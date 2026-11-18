<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanDueReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLoanDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_an_active_loan_releases_its_copy_and_removes_related_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook(0, 'tidak_tersedia');
        $loan = $this->createLoan($member, $book, 'borrowed');
        Fine::create(['loan_id' => $loan->id, 'jumlah_denda' => 3000, 'status_bayar' => 'unpaid']);
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => LoanDueReminder::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $member->id,
            'data' => json_encode(['loan_id' => $loan->id, 'book_title' => $book->judul, 'message' => 'Jatuh tempo besok', 'due_date' => now()->addDay()->toDateString()]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.loans.index'))
            ->delete(route('admin.loans.destroy', $loan))
            ->assertRedirect(route('admin.loans.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseCount('fines', 0);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
        $this->assertSame('tersedia', $book->fresh()->status);
    }

    public function test_deleting_a_returned_loan_keeps_the_available_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook(2, 'tersedia');
        $loan = $this->createLoan($member, $book, 'returned');

        $this->actingAs($admin)
            ->from(route('admin.loans.index'))
            ->delete(route('admin.loans.destroy', $loan))
            ->assertRedirect(route('admin.loans.index'));

        $this->assertDatabaseCount('loans', 0);
        $this->assertSame(2, $book->fresh()->stok_tersedia);
    }

    public function test_members_cannot_delete_loan_records(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook(1, 'tersedia');
        $loan = $this->createLoan($member, $book, 'borrowed');

        $this->actingAs($member)
            ->delete(route('admin.loans.destroy', $loan))
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('loans', 1);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
    }

    public function test_delete_confirmation_escapes_quotes_in_titles_and_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active', 'name' => "O'Brien"]);
        $book = Book::create([
            'judul' => "Buku O'Brien",
            'penulis' => 'Test author',
            'jumlah_total' => 2,
            'stok_tersedia' => 2,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);
        $this->createLoan($member, $book, 'borrowed');

        $content = $this->actingAs($admin)
            ->get(route('admin.loans.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('\u0027', $content);
        $this->assertStringNotContainsString("O'Brien", $content);
    }

    private function createBook(int $available, string $status): Book
    {
        return Book::create([
            'judul' => 'Buku uji hapus',
            'penulis' => 'Test author',
            'jumlah_total' => 2,
            'stok_tersedia' => $available,
            'is_circulating' => true,
            'status' => $status,
        ]);
    }

    private function createLoan(User $member, Book $book, string $status): Loan
    {
        return Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(2)->toDateString(),
            'tenggat_kembali' => now()->addDays(5)->toDateString(),
            'tanggal_kembali' => $status === 'returned' ? now()->subDay()->toDateString() : null,
            'status' => $status,
        ]);
    }
}
