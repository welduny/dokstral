<?php

namespace Tests\Feature;

use App\Actions\BorrowBookAction;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminBorrowRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_a_loan_through_the_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $book = $this->createBook();

        $this->actingAs($admin)
            ->post(route('loans.store', $book), ['accept_rules' => '1'])
            ->assertForbidden();

        $this->assertDatabaseCount('loans', 0);
        $this->assertSame(2, $book->fresh()->stok_tersedia);
        $this->assertSame('tersedia', $book->fresh()->status);
    }

    public function test_borrow_action_rejects_admin_accounts_directly(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $book = $this->createBook();

        try {
            app(BorrowBookAction::class)->execute($admin, $book->id);
            $this->fail('Akun admin seharusnya tidak dapat meminjam buku.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('book', $exception->errors());
        }

        $this->assertDatabaseCount('loans', 0);
        $this->assertSame(2, $book->fresh()->stok_tersedia);
    }

    public function test_catalog_hides_the_borrow_control_from_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $this->createBook();

        $this->actingAs($admin)
            ->get(route('catalog'))
            ->assertOk()
            ->assertSee('Kelola buku')
            ->assertDontSee('Konfirmasi pinjam');

        $this->actingAs($member)
            ->get(route('catalog'))
            ->assertOk()
            ->assertSee('Konfirmasi pinjam')
            ->assertDontSee('Kelola buku');
    }

    private function createBook(): Book
    {
        return Book::create([
            'judul' => 'Buku untuk uji pinjam',
            'penulis' => 'Test author',
            'jumlah_total' => 2,
            'stok_tersedia' => 2,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);
    }
}
