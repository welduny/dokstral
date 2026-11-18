<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_pending_reservation_releases_its_stock_once(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $book = Book::create([
            'judul' => 'Reserved book',
            'penulis' => 'Test author',
            'jumlah_total' => 1,
            'stok_tersedia' => 0,
            'is_circulating' => true,
            'status' => 'tidak_tersedia',
        ]);
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(3)->toDateString(),
            'tenggat_kembali' => now()->addDays(4)->toDateString(),
            'confirmation_token' => '04217',
            'status' => 'pending',
        ]);
        DB::table('loans')->where('id', $loan->id)->update([
            'created_at' => now()->subHours(25),
            'updated_at' => now()->subHours(25),
        ]);

        Artisan::call('loans:maintain');

        $this->assertSame('rejected', $loan->fresh()->status);
        $this->assertNull($loan->fresh()->confirmation_token);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
        $this->assertSame('tersedia', $book->fresh()->status);

        Artisan::call('loans:maintain');

        $this->assertSame(1, $book->fresh()->stok_tersedia);
    }
}