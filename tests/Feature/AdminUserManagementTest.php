<?php

namespace Tests\Feature;

use App\Models\AdminPasswordResetCode;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_open_user_management_and_admin_accounts_are_excluded(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $otherAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($member)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($member->email)
            ->assertDontSee($otherAdmin->email);
    }

    public function test_admin_can_update_member_identity_and_status_without_changing_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $this->issueCode($admin, $member);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $member), [
                'name' => 'Nama Baru',
                'email' => 'NEW-MEMBER@example.test',
                'status' => 'inactive',
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.users.edit', $member));

        $this->assertDatabaseMissing('admin_password_reset_codes', ['user_id' => $member->id]);
        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Nama Baru',
            'nama' => 'Nama Baru',
            'email' => 'new-member@example.test',
            'status' => 'inactive',
            'role' => 'siswa',
        ]);
    }

    public function test_admin_issues_a_hashed_single_use_code_for_only_the_selected_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $code = $this->issueCode($admin, $member);
        $resetCode = AdminPasswordResetCode::where('user_id', $member->id)->firstOrFail();

        $this->assertMatchesRegularExpression('/^\d{8}$/', $code);
        $this->assertTrue(Hash::check($code, $resetCode->code_hash));
        $this->assertNotSame($code, $resetCode->code_hash);
        $this->assertSame($admin->id, $resetCode->issued_by);
        $this->assertTrue($resetCode->expires_at->between(now()->addMinutes(14), now()->addMinutes(15)));

        $this->get(route('admin.users.edit', $member))
            ->assertOk()
            ->assertSee($code)
            ->assertSee('berikan hanya kepada pengguna ini');

        $this->get(route('admin.users.edit', $member))
            ->assertOk()
            ->assertDontSee($code);

        $this->assertDatabaseCount('admin_password_reset_codes', 1);
    }

    public function test_member_can_reset_password_with_their_valid_code_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'remember_token' => 'old-remember-token',
        ]);
        $code = $this->issueCode($admin, $member);

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => strtoupper($member->email),
                'code' => $code,
                'password' => 'replacement-password',
                'password_confirmation' => 'replacement-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Password berhasil diubah. Silakan masuk dengan password baru.');

        $member->refresh();
        $this->assertTrue(Hash::check('replacement-password', $member->password));
        $this->assertNotSame('old-remember-token', $member->remember_token);
        $this->assertNotNull($member->adminPasswordResetCode->used_at);

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => $member->email,
                'code' => $code,
                'password' => 'another-password',
                'password_confirmation' => 'another-password',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('replacement-password', $member->fresh()->password));
    }

    public function test_reissuing_a_reset_code_invalidates_the_previous_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $oldCode = $this->issueCode($admin, $member);
        $newCode = $this->issueCode($admin, $member);

        $this->post(route('password.email'), [
            'email' => $member->email,
            'code' => $oldCode,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('code');

        $this->post(route('password.email'), [
            'email' => $member->email,
            'code' => $newCode,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertRedirect(route('login'));
    }

    public function test_code_cannot_reset_another_accounts_password_or_be_used_when_expired(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $otherMember = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $code = $this->issueCode($admin, $member);

        $this->post(route('password.email'), [
            'email' => $otherMember->email,
            'code' => $code,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseHas('users', [
            'id' => $otherMember->id,
            'password' => $otherMember->password,
        ]);

        $member->adminPasswordResetCode()->update(['expires_at' => now()->subSecond()]);

        $this->post(route('password.email'), [
            'email' => $member->email,
            'code' => $code,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Hash::check('replacement-password', $member->fresh()->password));
    }

    public function test_inactive_accounts_cannot_receive_or_use_reset_codes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'inactive']);

        $this->actingAs($admin)
            ->post(route('admin.users.password-reset-code', $member))
            ->assertRedirect()
            ->assertSessionHas('error');

        AdminPasswordResetCode::create([
            'user_id' => $member->id,
            'issued_by' => $admin->id,
            'code_hash' => Hash::make('12345678'),
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->post(route('password.email'), [
            'email' => $member->email,
            'code' => '12345678',
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('code');

        Auth::logout();
        $this->from('/login')
            ->post('/login', [
                'email' => $member->email,
                'password' => 'password',
            ])->assertRedirect('/login');

        $this->actingAs($member)
            ->get(route('home'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_reset_code_guesses_are_limited_per_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $this->issueCode($admin, $member);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('password.request'))
                ->post(route('password.email'), [
                    'email' => $member->email,
                    'code' => '00000000',
                    'password' => 'replacement-password',
                    'password_confirmation' => 'replacement-password',
                ])
                ->assertRedirect(route('password.request'))
                ->assertSessionHasErrors('code');
        }

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => $member->email,
                'code' => '00000000',
                'password' => 'replacement-password',
                'password_confirmation' => 'replacement-password',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('code');

        $this->assertFalse(Hash::check('replacement-password', $member->fresh()->password));
    }

    public function test_admin_cannot_delete_account_with_loan_history_but_can_delete_empty_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $memberWithHistory = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $memberWithoutHistory = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = Book::create([
            'judul' => 'Buku uji akun',
            'penulis' => 'Penulis',
            'jumlah_total' => 1,
            'stok_tersedia' => 1,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);
        Loan::create([
            'user_id' => $memberWithHistory->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => today(),
            'tenggat_kembali' => today()->addDays(7),
            'status' => 'returned',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $memberWithHistory))
            ->delete(route('admin.users.destroy', $memberWithHistory))
            ->assertRedirect(route('admin.users.edit', $memberWithHistory))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $memberWithHistory->id]);
        $this->assertDatabaseCount('loans', 1);

        $this->delete(route('admin.users.destroy', $memberWithoutHistory))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $memberWithoutHistory->id]);
    }

    private function issueCode(User $admin, User $member): string
    {
        $this->actingAs($admin)
            ->post(route('admin.users.password-reset-code', $member))
            ->assertRedirect()
            ->assertSessionHas('issued_reset_code');

        return (string) session('issued_reset_code.code');
    }
}
