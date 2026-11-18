<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanDueReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DiscoveryFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_and_book_detail_show_cover_and_description(): void
    {
        $this->createBook('Atomic Habits', 'James Clear', 'Pengembangan Diri', true, 'Build better habits.', 'https://covers.example.test/atomic.jpg');
        $this->createBook('A Different Story', 'Another Author', 'Fiksi', true, 'Another description.');

        $this->get('/?q=Atomic&category=Pengembangan%20Diri&availability=available')
            ->assertOk()
            ->assertSee('Atomic Habits')
            ->assertSee('covers.example.test/atomic.jpg')
            ->assertSee('Build better habits.')
            ->assertDontSee('A Different Story');
    }

    public function test_catalog_category_filter_matches_the_fixed_book_categories(): void
    {
        $this->createBook('Lowercase fiction', 'Author One', 'fiksi');
        $this->createBook('Technology title', 'Author Three', 'Teknologi');

        $this->get('/')
            ->assertOk()
            ->assertSee('<option value="Fiksi"', false)
            ->assertSee('>Fiksi</option>', false)
            ->assertSee('<option value="Teknologi"', false)
            ->assertSee('<option value="Fantasi"', false)
            ->assertSee('<option value="Filsafat"', false)
            ->assertSee('<option value="Keuangan"', false)
            ->assertSee('<option value="Pengembangan Diri"', false)
            ->assertDontSee('<option value="fiksi">fiksi</option>', false)
            ->assertDontSee('<option value="Agama"', false)
            ->assertDontSee('<option value="Lainnya"', false);

        $this->get('/?category=Fiksi')
            ->assertOk()
            ->assertSee('Lowercase fiction')
            ->assertDontSee('Technology title');
    }

    public function test_member_can_toggle_favorite_and_review_only_after_returning_book(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook('Reviewable title', 'Author', 'Fiksi');
        $this->actingAs($member);

        $this->post(route('books.favorite', $book))->assertRedirect();
        $this->assertDatabaseHas('book_favorites', ['user_id' => $member->id, 'book_id' => $book->id]);
        $this->post(route('books.favorite', $book))->assertRedirect();
        $this->assertDatabaseMissing('book_favorites', ['user_id' => $member->id, 'book_id' => $book->id]);

        $loan = $this->createLoan($member, $book, 'borrowed');
        $this->from('/')->post(route('books.reviews.store', $book), ['rating' => 5, 'review' => 'Bagus sekali'])
            ->assertForbidden();
        $loan->update(['status' => 'returned', 'tanggal_kembali' => now()->toDateString()]);

        $this->post(route('books.reviews.store', $book), ['rating' => 5, 'review' => 'Bagus sekali'])->assertRedirect();
        $this->assertDatabaseHas('book_reviews', ['user_id' => $member->id, 'book_id' => $book->id, 'rating' => 5]);
    }

    public function test_member_can_update_monthly_reading_target(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $this->actingAs($member)
            ->put(route('reading-challenge.update'), ['target' => 6])
            ->assertRedirect();

        $this->assertDatabaseHas('reading_challenges', [
            'user_id' => $member->id,
            'year' => now()->year,
            'month' => now()->month,
            'target' => 6,
        ]);
    }

    public function test_due_reminder_command_notifies_once_per_reminder_window(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook('Due soon', 'Author', 'Fiksi');
        $this->createLoan($member, $book, 'borrowed', now()->addDays(3)->toDateString());

        Artisan::call('loans:send-due-reminders');
        Artisan::call('loans:send-due-reminders');

        $this->assertSame(1, $member->notifications()->where('type', LoanDueReminder::class)->count());
    }

    private function createBook(string $title, string $author, string $category, bool $circulating = true, ?string $description = null, ?string $coverUrl = null): Book
    {
        return Book::create([
            'judul' => $title,
            'penulis' => $author,
            'kategori' => $category,
            'deskripsi' => $description,
            'cover_url' => $coverUrl,
            'jumlah_total' => 2,
            'stok_tersedia' => 2,
            'is_circulating' => $circulating,
            'status' => 'tersedia',
        ]);
    }

    private function createLoan(User $user, Book $book, string $status, ?string $dueDate = null): Loan
    {
        return Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(2)->toDateString(),
            'tenggat_kembali' => $dueDate ?? now()->addDays(5)->toDateString(),
            'tanggal_kembali' => $status === 'returned' ? now()->toDateString() : null,
            'status' => $status,
            'confirmed_at' => now()->subDays(2),
        ]);
    }
}
