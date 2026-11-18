<?php

namespace Tests\Feature;

use App\Actions\BorrowBookAction;
use App\Actions\ConfirmLoanHandoffAction;
use App\Actions\ProcessBookReturnAction;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookCirculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_return_cannot_be_processed_twice(): void
    {
        [$user, $book, $loan] = $this->createLoan('borrowed', 0);
        $action = app(ProcessBookReturnAction::class);
        $data = ['keterangan_kondisi' => 'baik'];

        $action->execute($loan, $data);

        $this->assertSame(1, $book->fresh()->stok_tersedia);
        $this->expectException(ValidationException::class);
        $action->execute($loan->fresh(), $data);
    }

    public function test_late_fine_uses_the_two_thousand_rupiah_daily_rate(): void
    {
        [, $book, $loan] = $this->createLoan('borrowed', 0, now()->subDays(2)->toDateString());

        app(ProcessBookReturnAction::class)->execute($loan, []);

        $this->assertSame(4000, $loan->fresh()->denda_terlambat);
        $this->assertSame(4000, Fine::where('loan_id', $loan->id)->value('jumlah_denda'));
        $this->assertSame(1, $book->fresh()->stok_tersedia);
    }

    public function test_borrowing_the_last_copy_marks_the_book_unavailable(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $book = Book::create([
            'judul' => 'Test book',
            'penulis' => 'Test author',
            'jumlah_total' => 1,
            'stok_tersedia' => 1,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);

        app(BorrowBookAction::class)->execute($user, $book->id);

        $this->assertSame(0, $book->fresh()->stok_tersedia);
        $this->assertSame('tidak_tersedia', $book->fresh()->status);
        $this->assertSame('pending', $user->loans()->first()->status);
        $this->assertMatchesRegularExpression('/^\d{5}$/', $user->loans()->first()->confirmation_token);
    }

    public function test_catalog_has_one_borrow_button_and_confirmation_shows_a_qr(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $book = Book::create([
            'judul' => 'Test book',
            'penulis' => 'Test author',
            'jumlah_total' => 1,
            'stok_tersedia' => 1,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);

        $this->actingAs($user);
        $catalog = $this->get('/')->assertOk();
        $this->assertSame(1, substr_count($catalog->getContent(), 'class="loan-confirm"'));

        $this->post(route('loans.store', $book), ['accept_rules' => '1'])
            ->assertRedirect(route('loans.mine'));

        $loan = Loan::query()->firstOrFail();
        $this->assertSame('pending', $loan->status);
        $this->get(route('loans.mine'))
            ->assertOk()
            ->assertSee('loan-confirmation-qr', false)
            ->assertSee($loan->confirmation_token, false);
    }

    public function test_admin_qr_confirmation_starts_the_seven_day_loan_once(): void
    {
        [, , $loan] = $this->createLoan('pending', 0);
        $loan->update(['confirmation_token' => str_repeat('a', 64)]);
        $expectedDueDate = now()->addDays(7)->toDateString();

        $confirmedLoan = app(ConfirmLoanHandoffAction::class)->execute(str_repeat('a', 64));

        $this->assertSame('borrowed', $confirmedLoan->status);
        $this->assertNotNull($confirmedLoan->confirmed_at);
        $this->assertSame(100000, $confirmedLoan->harga_buku);
        $this->assertSame('C-07', $confirmedLoan->book->lokasi_rak);
        $this->assertNull($confirmedLoan->confirmation_token);
        $this->assertSame($expectedDueDate, $confirmedLoan->tenggat_kembali->toDateString());

        $this->expectException(ValidationException::class);
        app(ConfirmLoanHandoffAction::class)->execute(str_repeat('a', 64));
    }

    public function test_damage_fine_uses_the_book_price_at_handoff_even_if_the_catalog_price_changes(): void
    {
        [, $book, $loan] = $this->createLoan('pending', 0);
        $token = '04217';
        $loan->update(['confirmation_token' => $token]);

        app(ConfirmLoanHandoffAction::class)->execute($token);
        $book->update(['harga' => 200000]);

        app(ProcessBookReturnAction::class)->execute($loan->fresh(), ['keterangan_kondisi' => 'rusak_ringan']);

        $this->assertSame(10000, $loan->fresh()->denda_tambahan);
        $this->assertSame(10000, Fine::where('loan_id', $loan->id)->value('jumlah_denda'));
    }

    public function test_admin_scan_endpoint_confirms_the_handoff(): void
    {
        [, , $loan] = $this->createLoan('pending', 0);
        $token = '04217';
        $loan->update(['confirmation_token' => $token]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($loan->user)
            ->get(route('loans.mine'))
            ->assertOk()
            ->assertSee('loan-confirmation-qr', false)
            ->assertSee($token, false)
            ->assertSee('Token QR untuk input manual');

        $this->actingAs($admin)
            ->get(route('admin.loans.scan'))
            ->assertOk()
            ->assertSee('handoff-reader', false)
            ->assertSee('<form id="scan-manual"', false)
            ->assertSee('id="scan-token"', false)
            ->assertSee('Konfirmasi kode', false);

        $this->postJson(route('admin.loans.confirm-handoff'), ['token' => $token])
            ->assertOk()
            ->assertJsonPath('loan.book', 'Test book')
            ->assertJsonPath('loan.shelf_location', 'C-07');

        $this->assertSame('borrowed', $loan->fresh()->status);

        $this->actingAs($admin)
            ->get(route('admin.loans.index'))
            ->assertOk()
            ->assertSee('admin-handoff-notice', false)
            ->assertSee('Peminjaman dikonfirmasi')
            ->assertSee('Lokasi rak: C-07');

        $this->actingAs($loan->user)
            ->getJson(route('loans.status'))
            ->assertOk()
            ->assertJsonPath('loans.0.status', 'borrowed')
            ->assertJsonPath('loans.0.shelf_location', 'C-07');

        $this->actingAs($loan->user)
            ->get(route('loans.mine'))
            ->assertOk()
            ->assertDontSee('loan-confirmation-qr', false)
            ->assertDontSee($token, false)
            ->assertSee('loan-success-progress', false)
            ->assertSee('PEMINJAMAN TERCATAT')
            ->assertSee('Lokasi pengambilan:')
            ->assertSee('Rak C-07');
    }

    public function test_member_status_poll_returns_only_their_loans_without_qr_tokens(): void
    {
        [$member, , $memberLoan] = $this->createLoan('pending', 0);
        [, , $otherLoan] = $this->createLoan('pending', 0);
        $memberLoan->update(['confirmation_token' => '04217']);
        $otherLoan->update(['confirmation_token' => '09531']);

        $this->actingAs($member)
            ->getJson(route('loans.status'))
            ->assertOk()
            ->assertJsonCount(1, 'loans')
            ->assertJsonPath('loans.0.id', $memberLoan->id)
            ->assertJsonPath('loans.0.shelf_location', null)
            ->assertJsonMissing(['confirmation_token' => '04217'])
            ->assertJsonMissing(['confirmation_token' => '09531']);
    }

    public function test_admin_scan_endpoint_rejects_non_five_digit_tokens(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->postJson(route('admin.loans.confirm-handoff'), ['token' => '1234'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');

        $this->postJson(route('admin.loans.confirm-handoff'), ['token' => '12a45'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');

        $this->postJson(route('admin.loans.confirm-handoff'), ['token' => str_repeat('1', 64)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');
    }

    public function test_pending_reservation_cannot_be_overdue_and_rejection_releases_stock(): void
    {
        [, $book, $loan] = $this->createLoan('pending', 0);
        $loan->update(['confirmation_token' => '04217']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.loans.index'))
            ->assertOk()
            ->assertDontSee('Proses kembali');

        $this->patch(route('admin.loans.status', $loan), ['status' => 'overdue'])
            ->assertStatus(422);

        $this->assertSame('pending', $loan->fresh()->status);
        $this->assertSame(0, $book->fresh()->stok_tersedia);

        $this->patch(route('admin.loans.status', $loan), ['status' => 'rejected'])
            ->assertRedirect();

        $this->assertSame('rejected', $loan->fresh()->status);
        $this->assertNull($loan->fresh()->confirmation_token);
        $this->assertSame(1, $book->fresh()->stok_tersedia);
        $this->assertSame('tersedia', $book->fresh()->status);

        $this->postJson(route('admin.loans.confirm-handoff'), ['token' => '04217'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');
    }

    public function test_a_damaged_book_is_not_returned_to_available_stock(): void
    {
        [, $book, $loan] = $this->createLoan('borrowed', 0);

        app(ProcessBookReturnAction::class)->execute($loan, ['keterangan_kondisi' => 'rusak_berat']);

        $this->assertSame(0, $book->fresh()->stok_tersedia);
        $this->assertSame('tidak_tersedia', $book->fresh()->status);
        $this->assertSame(50000, $loan->fresh()->denda_tambahan);
        $this->assertSame(50000, Fine::where('loan_id', $loan->id)->value('jumlah_denda'));
    }

    public function test_a_lost_book_is_fined_at_its_full_reference_price(): void
    {
        [, $book, $loan] = $this->createLoan('borrowed', 0);

        app(ProcessBookReturnAction::class)->execute($loan, ['keterangan_kondisi' => 'hilang']);

        $this->assertSame(100000, $loan->fresh()->denda_tambahan);
        $this->assertSame(100000, Fine::where('loan_id', $loan->id)->value('jumlah_denda'));
        $this->assertSame(0, $book->fresh()->stok_tersedia);
    }

    public function test_damage_fine_uses_price_percentage_and_late_fine_is_two_thousand_per_day(): void
    {
        [, , $loan] = $this->createLoan('borrowed', 0, now()->subDays(2)->toDateString());

        app(ProcessBookReturnAction::class)->execute($loan, ['keterangan_kondisi' => 'rusak_ringan']);

        $this->assertSame(4000, $loan->fresh()->denda_terlambat);
        $this->assertSame(10000, $loan->fresh()->denda_tambahan);
        $this->assertSame(14000, $loan->fresh()->total_denda);
        $this->assertSame(14000, Fine::where('loan_id', $loan->id)->value('jumlah_denda'));
    }

    public function test_a_damage_fine_cannot_be_calculated_until_the_book_has_a_price(): void
    {
        [, $book, $loan] = $this->createLoan('borrowed', 0);
        $book->update(['harga' => null]);

        try {
            app(ProcessBookReturnAction::class)->execute($loan, ['keterangan_kondisi' => 'rusak_ringan']);
            $this->fail('A damage fine should require a book price.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('harga', $exception->errors());
        }

        $this->assertSame('borrowed', $loan->fresh()->status);
        $this->assertDatabaseMissing('fines', ['loan_id' => $loan->id]);
    }

    private function createLoan(string $status, int $availableStock, ?string $dueDate = null): array
    {
        $user = User::factory()->create(['status' => 'active']);
        $book = Book::create([
            'judul' => 'Test book',
            'penulis' => 'Test author',
            'harga' => 100000,
            'lokasi_rak' => 'C-07',
            'jumlah_total' => 1,
            'stok_tersedia' => $availableStock,
            'is_circulating' => true,
            'status' => $availableStock > 0 ? 'tersedia' : 'tidak_tersedia',
        ]);
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => now()->subDays(9)->toDateString(),
            'tenggat_kembali' => $dueDate ?? now()->addDays(1)->toDateString(),
            'status' => $status,
        ]);

        return [$user, $book, $loan];
    }
}