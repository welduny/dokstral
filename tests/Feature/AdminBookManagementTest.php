<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_book_description_stock_and_keep_an_existing_external_cover(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $book = $this->createBook();
        $book->update(['cover_url' => 'https://covers.example.test/existing.jpg']);

        $this->actingAs($admin)
            ->patch(route('admin.books.update', $book), [
                'judul' => $book->judul,
                'penulis' => $book->penulis,
                'isbn' => $book->isbn,
                'kategori' => $book->kategori,
                'harga' => 85000,
                'lokasi_rak' => 'B-04',
                'deskripsi' => 'An updated detail.',
                'jumlah_total' => 6,
                'is_circulating' => '1',
            ])
            ->assertRedirect(route('admin.books.create'));

        $this->assertSame('An updated detail.', $book->fresh()->deskripsi);
        $this->assertSame('https://covers.example.test/existing.jpg', $book->fresh()->cover_url);
        $this->assertSame($book->cover_url, $book->cover_image_url);
        $this->assertSame(6, $book->fresh()->jumlah_total);
        $this->assertSame(6, $book->fresh()->stok_tersedia);
        $this->assertSame(85000, $book->fresh()->harga);
        $this->assertSame('B-04', $book->fresh()->lokasi_rak);
    }

    public function test_book_price_is_required_when_adding_a_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->from(route('admin.books.create'))
            ->post(route('admin.books.store'), [
                'judul' => 'Buku tanpa harga',
                'penulis' => 'Penulis Uji',
                'lokasi_rak' => 'A-01',
                'jumlah_total' => 1,
            ])
            ->assertRedirect(route('admin.books.create'))
            ->assertSessionHasErrors('harga');

        $this->assertDatabaseMissing('books', ['judul' => 'Buku tanpa harga']);
    }

    public function test_book_price_is_available_on_create_and_edit_forms(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $book = $this->createBook();

        $this->actingAs($admin)
            ->get(route('admin.books.create'))
            ->assertOk()
            ->assertSee('Harga buku (Rp)')
            ->assertSee('name="harga"', false)
            ->assertSee('name="lokasi_rak"', false);

        $this->get(route('admin.books.edit', $book))
            ->assertOk()
            ->assertSee('Harga buku (Rp)')
            ->assertSee('value="85000"', false)
            ->assertSee('name="lokasi_rak"', false)
            ->assertSee('value="A-01"', false)
            ->assertSee('<select id="kategori" name="kategori">', false)
            ->assertSee('<option value="Fiksi" selected>Fiksi</option>', false);
    }

    public function test_book_category_uses_fixed_options_and_rejects_custom_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.books.create'))
            ->assertOk()
            ->assertSee('<select id="kategori" name="kategori">', false)
            ->assertSee('<option value="Teknologi"', false)
            ->assertDontSee('<input id="kategori" name="kategori"', false);

        $this->from(route('admin.books.create'))
            ->post(route('admin.books.store'), [
                'judul' => 'Custom category book',
                'penulis' => 'Test author',
                'kategori' => 'Kategori bebas',
                'harga' => 85000,
                'lokasi_rak' => 'A-01',
                'jumlah_total' => 1,
            ])
            ->assertSessionHasErrors('kategori');

        $book = $this->createBook();
        $this->from(route('admin.books.edit', $book))
            ->patch(route('admin.books.update', $book), [
                'judul' => $book->judul,
                'penulis' => $book->penulis,
                'kategori' => 'Kategori bebas',
                'harga' => $book->harga,
                'lokasi_rak' => $book->lokasi_rak,
                'jumlah_total' => $book->jumlah_total,
            ])
            ->assertSessionHasErrors('kategori');
    }

    public function test_admin_can_create_a_book_with_a_local_cover_upload(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [
                'judul' => 'Buku Bercover',
                'penulis' => 'Penulis Uji',
                'isbn' => '9781111111112',
                'kategori' => 'Fiksi',
                'harga' => 85000,
                'lokasi_rak' => 'C-02',
                'jumlah_total' => 3,
                'is_circulating' => '1',
                'cover_file' => $this->fakePng('cover.png'),
            ])
            ->assertRedirect(route('admin.books.create'));

        $book = Book::query()->where('isbn', '9781111111112')->firstOrFail();
        $this->assertSame(85000, $book->harga);
        $this->assertSame('C-02', $book->lokasi_rak);
        $this->assertNotNull($book->cover_path);
        $this->assertNull($book->cover_url);
        $this->assertSame(3, $book->stok_tersedia);
        $this->assertStringContainsString('/storage/covers/', $book->cover_image_url);
        Storage::disk('public')->assertExists($book->cover_path);
    }

    public function test_uploaded_cover_replaces_a_legacy_url_and_invalid_file_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $book = $this->createBook();
        $book->update(['cover_url' => 'https://covers.example.test/legacy.jpg']);

        $this->actingAs($admin)
            ->patch(route('admin.books.update', $book), [
                'judul' => $book->judul,
                'penulis' => $book->penulis,
                'isbn' => $book->isbn,
                'kategori' => $book->kategori,
                'harga' => 85000,
                'lokasi_rak' => $book->lokasi_rak,
                'deskripsi' => $book->deskripsi,
                'jumlah_total' => $book->jumlah_total,
                'is_circulating' => '1',
                'cover_file' => $this->fakePng('new-cover.png'),
            ])
            ->assertRedirect(route('admin.books.create'));

        $book->refresh();
        $this->assertNotNull($book->cover_path);
        $this->assertNull($book->cover_url);
        $oldCoverPath = $book->cover_path;
        Storage::disk('public')->assertExists($oldCoverPath);

        $this->patch(route('admin.books.update', $book), [
            'judul' => $book->judul,
            'penulis' => $book->penulis,
            'isbn' => $book->isbn,
            'kategori' => $book->kategori,
            'harga' => 85000,
            'lokasi_rak' => $book->lokasi_rak,
            'jumlah_total' => $book->jumlah_total,
            'is_circulating' => '1',
            'cover_file' => $this->fakePng('replacement-cover.png'),
        ])->assertRedirect(route('admin.books.create'));

        $book->refresh();
        $this->assertNotSame($oldCoverPath, $book->cover_path);
        Storage::disk('public')->assertMissing($oldCoverPath);
        Storage::disk('public')->assertExists($book->cover_path);

        $this->from(route('admin.books.edit', $book))
            ->patch(route('admin.books.update', $book), [
                'judul' => $book->judul,
                'penulis' => $book->penulis,
                'isbn' => $book->isbn,
                'kategori' => $book->kategori,
                'harga' => 85000,
                'lokasi_rak' => $book->lokasi_rak,
                'jumlah_total' => $book->jumlah_total,
                'is_circulating' => '1',
                'cover_file' => UploadedFile::fake()->create('cover.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('cover_file');

        $this->assertSame($book->cover_path, $book->fresh()->cover_path);
    }

    public function test_admin_stock_increase_and_decrease_adjust_available_stock_without_losing_active_reservations(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook();
        $book->update(['stok_tersedia' => 1]);
        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tenggat_kembali' => now()->addDays(7)->toDateString(),
            'status' => 'borrowed',
        ]);

        $this->actingAs($admin)->patch(route('admin.books.update', $book), [
            'judul' => $book->judul,
            'penulis' => $book->penulis,
            'isbn' => $book->isbn,
            'kategori' => $book->kategori,
            'harga' => 85000,
            'lokasi_rak' => $book->lokasi_rak,
            'jumlah_total' => 4,
            'is_circulating' => '1',
        ])->assertRedirect(route('admin.books.create'));

        $this->assertSame(4, $book->fresh()->jumlah_total);
        $this->assertSame(3, $book->fresh()->stok_tersedia);

        $this->patch(route('admin.books.update', $book), [
            'judul' => $book->judul,
            'penulis' => $book->penulis,
            'isbn' => $book->isbn,
            'kategori' => $book->kategori,
            'harga' => 85000,
            'lokasi_rak' => $book->lokasi_rak,
            'jumlah_total' => 2,
            'is_circulating' => '1',
        ])->assertRedirect(route('admin.books.create'));

        $this->assertSame(2, $book->fresh()->jumlah_total);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
    }

    public function test_create_form_can_disable_circulation_when_checkbox_is_unchecked(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [
                'judul' => 'Buku Referensi',
                'penulis' => 'Penulis Uji',
                'isbn' => '9781111111113',
                'harga' => 85000,
                'lokasi_rak' => 'D-03',
                'jumlah_total' => 2,
            ])
            ->assertRedirect(route('admin.books.create'));

        $book = Book::query()->where('isbn', '9781111111113')->firstOrFail();
        $this->assertFalse($book->is_circulating);
        $this->assertSame('tidak_tersedia', $book->status);
    }

    public function test_total_stock_cannot_be_reduced_below_active_loans(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $book = $this->createBook();
        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tenggat_kembali' => now()->addDays(7)->toDateString(),
            'status' => 'borrowed',
        ]);
        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDay()->toDateString(),
            'tenggat_kembali' => now()->addDays(6)->toDateString(),
            'status' => 'borrowed',
        ]);
        $book->update(['stok_tersedia' => 0]);

        $this->actingAs($admin)
            ->from(route('admin.books.edit', $book))
            ->patch(route('admin.books.update', $book), [
                'judul' => $book->judul,
                'penulis' => $book->penulis,
                'isbn' => $book->isbn,
                'kategori' => $book->kategori,
                'harga' => 85000,
                'lokasi_rak' => $book->lokasi_rak,
                'deskripsi' => $book->deskripsi,
                'cover_url' => $book->cover_url,
                'jumlah_total' => 1,
                'is_circulating' => '1',
            ])
            ->assertSessionHasErrors('jumlah_total');

        $this->assertSame(2, $book->fresh()->jumlah_total);
        $this->assertSame(0, $book->fresh()->stok_tersedia);
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR4nGP4DwQACfsD/fteaysAAAAASUVORK5CYII='),
        );
    }

    private function createBook(): Book
    {
        return Book::create([
            'judul' => 'Editable book',
            'penulis' => 'Test author',
            'isbn' => '9781111111111',
            'kategori' => 'Fiksi',
            'harga' => 85000,
            'lokasi_rak' => 'A-01',
            'deskripsi' => 'Original description.',
            'cover_url' => null,
            'jumlah_total' => 2,
            'stok_tersedia' => 2,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);
    }
}
