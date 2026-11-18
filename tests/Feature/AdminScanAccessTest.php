<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScanAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_is_redirected_to_admin_login_instead_of_a_dead_end_403(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $this->actingAs($member)
            ->get(route('admin.loans.scan'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'Halaman ini hanya untuk admin. Silakan masuk dengan akun administrator.');

        $this->get(route('admin.login'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Keluar dari akun siswa sebelum masuk sebagai admin.');
    }

    public function test_active_admin_can_open_the_scan_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.loans.scan'))
            ->assertOk()
            ->assertSee('handoff-reader', false)
            ->assertSee('id="scan-manual"', false)
            ->assertSee('id="scan-token"', false)
            ->assertSee('Konfirmasi kode')
            ->assertSee('Kamera akan langsung membaca QR', false)
            ->assertSee('data-success-url="'.route('admin.loans.index', [], false).'"', false);
    }

    public function test_member_cannot_switch_to_admin_without_logging_out_first(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => 'admin-secret',
        ]);

        $this->actingAs($member)
            ->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => 'admin-secret',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'Keluar dari akun saat ini sebelum masuk sebagai admin.');

        $this->assertAuthenticatedAs($member);
        $this->post(route('logout'));

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'admin-secret',
        ])->assertRedirect(route('admin.loans.index'));
        $this->get(route('admin.loans.scan'))->assertOk();
    }

    public function test_admin_cannot_authenticate_with_the_seeded_default_password(): void
    {
        User::factory()->create([
            'email' => 'admin@lehbook.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@lehbook.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_account_cannot_log_in_through_the_member_login_form(): void
    {
        User::factory()->create([
            'email' => 'admin@lehbook.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'secure-admin-password',
        ]);

        $this->post(route('login'), [
            'email' => 'admin@lehbook.com',
            'password' => 'secure-admin-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_return_form_uses_the_current_origin_and_processes_the_return(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = Book::create([
            'judul' => 'Return test book',
            'penulis' => 'Test author',
            'jumlah_total' => 1,
            'stok_tersedia' => 0,
            'is_circulating' => true,
            'status' => 'tidak_tersedia',
        ]);
        $loan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(2)->toDateString(),
            'tenggat_kembali' => now()->addDays(5)->toDateString(),
            'status' => 'borrowed',
        ]);

        $this->actingAs($admin)
            ->withServerVariables([
                'HTTP_HOST' => '127.0.0.1:8000',
                'SERVER_NAME' => '127.0.0.1',
                'SERVER_PORT' => 8000,
            ])
            ->get('/admin/loans')
            ->assertOk()
            ->assertSee('action="/admin/loans/'.$loan->id.'/return"', false)
            ->assertSee('Denda terlambat Rp2.000 per hari')
            ->assertSee('Atur harga buku sebelum memproses kondisi rusak atau hilang.')
            ->assertSee('href="'.route('admin.books.edit', $book).'"', false)
            ->assertSee('Atur harga buku')
            ->assertDontSee('action="http://localhost/admin/loans/'.$loan->id.'/return"', false);

        $this->post('/admin/loans/'.$loan->id.'/return', [
            'keterangan_kondisi' => 'baik',
            'denda_tambahan' => 0,
        ])->assertRedirect();

        $this->assertSame('returned', $loan->fresh()->status);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
    }
}
