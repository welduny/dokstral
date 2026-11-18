<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolRegistrationVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_school_identity_and_creates_a_pending_account(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Nomor Induk Siswa (NIS)')
            ->assertSee('menunggu verifikasi', false);

        $this->post(route('register'), [
            'name' => 'Pembaca Sekolah',
            'nis' => '001234',
            'kelas' => 'XI IPA 1',
            'email' => 'reader@example.test',
            'password' => 'reader-password',
            'password_confirmation' => 'reader-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'name' => 'Pembaca Sekolah',
            'nis' => '001234',
            'kelas' => 'XI IPA 1',
            'role' => 'siswa',
            'status' => 'pending',
        ]);

        $pendingUser = User::where('email', 'reader@example.test')->firstOrFail();
        $this->actingAs($pendingUser)
            ->get(route('home'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun kamu masih menunggu verifikasi admin sebelum bisa digunakan.');
        $this->assertGuest();

        $this->from(route('login'))->post(route('login'), [
            'email' => 'reader@example.test',
            'password' => 'reader-password',
        ])->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_nis_must_be_numeric_and_unique(): void
    {
        User::factory()->create(['nis' => '009988']);

        $registration = [
            'name' => 'Pendaftar',
            'kelas' => 'XII IPS 2',
            'email' => 'new-reader@example.test',
            'password' => 'reader-password',
            'password_confirmation' => 'reader-password',
        ];

        $this->from(route('register'))
            ->post(route('register'), [...$registration, 'nis' => '12A'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('nis');

        $this->from(route('register'))
            ->post(route('register'), [...$registration, 'nis' => '009988'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('nis');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_can_approve_pending_user_and_user_can_then_sign_in(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create([
            'name' => 'Calon Anggota',
            'nis' => '123456',
            'kelas' => 'X IPA 1',
            'role' => 'siswa',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Calon Anggota')
            ->assertSee('123456')
            ->assertSee('Setujui');

        $this->post(route('admin.users.approve', $user))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('active', $user->fresh()->status);

        auth()->logout();
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_reject_pending_user_and_member_cannot_approve_or_reject_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $pendingUser = User::factory()->create(['role' => 'siswa', 'status' => 'pending']);

        $this->actingAs($member)
            ->post(route('admin.users.approve', $pendingUser))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($admin)
            ->post(route('admin.users.reject', $pendingUser))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('inactive', $pendingUser->fresh()->status);
    }
}
