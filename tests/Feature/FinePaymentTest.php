<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_the_unpaid_fine_and_cannot_borrow_until_admin_records_payment(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $oldBook = $this->createBook('Returned book', 0);
        $newBook = $this->createBook('New book', 1);
        $returnedLoan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $oldBook->id,
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tenggat_kembali' => now()->subDays(3)->toDateString(),
            'tanggal_kembali' => now()->toDateString(),
            'status' => 'returned',
        ]);
        $fine = Fine::create([
            'loan_id' => $returnedLoan->id,
            'jumlah_denda' => 7000,
            'status_bayar' => 'unpaid',
        ]);

        $this->actingAs($member)
            ->get(route('loans.mine'))
            ->assertOk()
            ->assertSee('Denda belum lunas: Rp7.000');

        $this->from('/')->post(route('loans.store', $newBook), ['accept_rules' => '1'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('book');
        $this->assertDatabaseMissing('loans', [
            'user_id' => $member->id,
            'book_id' => $newBook->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.fines.paid', $fine))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('paid', $fine->fresh()->status_bayar);

        $this->actingAs($member)
            ->from('/')
            ->post(route('loans.store', $newBook), ['accept_rules' => '1'])
            ->assertRedirect(route('loans.mine'));
        $this->assertDatabaseHas('loans', [
            'user_id' => $member->id,
            'book_id' => $newBook->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_admin_cannot_mark_a_fine_paid(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook('Returned book', 0);
        $loan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tenggat_kembali' => now()->subDays(3)->toDateString(),
            'tanggal_kembali' => now()->toDateString(),
            'status' => 'returned',
        ]);
        $fine = Fine::create([
            'loan_id' => $loan->id,
            'jumlah_denda' => 7000,
            'status_bayar' => 'unpaid',
        ]);

        $this->actingAs($member)
            ->patch(route('admin.fines.paid', $fine))
            ->assertRedirect(route('admin.login'));

        $this->assertSame('unpaid', $fine->fresh()->status_bayar);
    }

    private function createBook(string $title, int $stock): Book
    {
        return Book::create([
            'judul' => $title,
            'penulis' => 'Test author',
            'harga' => 100000,
            'jumlah_total' => 1,
            'stok_tersedia' => $stock,
            'is_circulating' => true,
            'status' => $stock > 0 ? 'tersedia' : 'tidak_tersedia',
        ]);
    }
}
