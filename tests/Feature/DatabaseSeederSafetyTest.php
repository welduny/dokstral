<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_rerunning_seeder_does_not_reset_existing_user_credentials_or_book_stock(): void
    {
        $user = User::create([
            'name' => 'Library Reader',
            'email' => 'siswa@lehbook.com',
            'password' => 'a-custom-password',
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $book = Book::create([
            'judul' => 'Laravel untuk Pemula',
            'penulis' => 'Taylor Otwell',
            'isbn' => '978000000001',
            'kategori' => 'Teknologi',
            'jumlah_total' => 5,
            'stok_tersedia' => 2,
        ]);

        app(DatabaseSeeder::class)->run();

        $this->assertSame('Library Reader', $user->fresh()->name);
        $this->assertTrue(Hash::check('a-custom-password', $user->fresh()->password));
        $this->assertSame(2, $book->fresh()->stok_tersedia);
    }
}