<?php

namespace Tests\Feature;

use App\Actions\BuildMonthlyReportAction;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMonthlyReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-04 12:00:00', config('app.timezone')));
    }

    public function test_only_admins_can_view_or_download_monthly_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $this->get(route('admin.reports.monthly'))
            ->assertRedirect(route('login'));

        $this->actingAs($member)
            ->get(route('admin.reports.monthly'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.reports.monthly.pdf', ['month' => '2026-10']))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($member)
            ->get(route('admin.reports.monthly.pdf', ['month' => '2026-10']))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($admin)
            ->get(route('admin.reports.monthly'))
            ->assertOk()
            ->assertSee('Laporan bulanan')
            ->assertSee('Laporan bulanan', false)
            ->assertSee('Unduh PDF')
            ->assertSee(route('admin.reports.monthly'), false);
    }

    public function test_month_input_rejects_invalid_and_future_periods(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin);

        $this->get(route('admin.reports.monthly', ['month' => '2026-13']))
            ->assertSessionHasErrors('month');

        $this->get(route('admin.reports.monthly', ['month' => '2026-12']))
            ->assertSessionHasErrors('month');

        $this->get(route('admin.reports.monthly', ['month' => ['2026-10']]))
            ->assertSessionHasErrors('month');
    }

    public function test_empty_month_shows_zero_totals_and_downloads_a_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin);

        $this->get(route('admin.reports.monthly', ['month' => '2026-09']))
            ->assertOk()
            ->assertSee('Belum ada aktivitas pada bulan ini.')
            ->assertSee('Belum ada peminjaman yang diserahkan atau dikembalikan pada bulan ini.')
            ->assertSee('Peminjaman dimulai')
            ->assertSee('Anggota baru');

        $response = $this->get(route('admin.reports.monthly.pdf', ['month' => '2026-09']));
        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename=laporan-bulanan-2026-09.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_report_counts_handoffs_returns_fines_and_members_in_the_selected_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $borrower = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $newMember = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'created_at' => '2026-10-14 08:00:00',
        ]);
        $pendingMember = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $startedBook = $this->createBook('Confirmed loan');
        $returnedBook = $this->createBook('Returned loan');
        $pendingBook = $this->createBook('Pending loan');
        $rejectedBook = $this->createBook('Rejected loan');
        $legacyBook = $this->createBook('Legacy loan');
        $unconfirmedBook = $this->createBook('Unconfirmed loan');

        $this->createLoan($borrower, $startedBook, 'borrowed', '2026-10-10', '2026-10-10 09:00:00');
        $returned = $this->createLoan($borrower, $returnedBook, 'returned', '2026-09-20', '2026-09-20 09:00:00', '2026-10-12');
        $this->createLoan($pendingMember, $pendingBook, 'pending', '2026-10-13', null);
        $this->createLoan($pendingMember, $rejectedBook, 'rejected', '2026-10-15', null);
        $legacy = $this->createLoan($pendingMember, $legacyBook, 'returned', '2026-10-18', null, '2026-10-19');
        $legacy->forceFill(['created_at' => '2026-10-18 10:00:00'])->save();
        $legacyBorrowed = $this->createLoan($pendingMember, $this->createBook('Legacy confirmed loan'), 'overdue', '2026-10-11', null);
        $legacyBorrowed->forceFill(['created_at' => '2026-10-17 10:00:00'])->save();
        $unconfirmed = $this->createLoan($pendingMember, $unconfirmedBook, 'borrowed', '2026-10-21', null);
        $unconfirmed->forceFill(['created_at' => '2026-10-20 10:00:00'])->save();

        $unpaidFine = Fine::create(['loan_id' => $returned->id, 'jumlah_denda' => 5000, 'status_bayar' => 'unpaid']);
        $unpaidFine->forceFill(['created_at' => '2026-10-12 12:00:00'])->save();
        $paidInMonthLoan = $this->createLoan($borrower, $this->createBook('Paid fine'), 'returned', '2026-09-15', '2026-09-15 09:00:00', '2026-10-17');
        $paidInMonthFine = Fine::create([
            'loan_id' => $paidInMonthLoan->id,
            'jumlah_denda' => 7000,
            'status_bayar' => 'paid',
            'paid_at' => '2026-10-20 11:00:00',
        ]);
        $paidInMonthFine->forceFill(['created_at' => '2026-10-17 12:00:00'])->save();
        $paidAfterMonthLoan = $this->createLoan($borrower, $this->createBook('Paid later'), 'returned', '2026-09-15', '2026-09-15 09:00:00', '2026-10-18');
        $paidAfterMonthFine = Fine::create([
            'loan_id' => $paidAfterMonthLoan->id,
            'jumlah_denda' => 3000,
            'status_bayar' => 'paid',
            'paid_at' => '2026-11-02 11:00:00',
        ]);
        $paidAfterMonthFine->forceFill(['created_at' => '2026-10-18 12:00:00'])->save();
        $legacyPaidLoan = $this->createLoan($borrower, $this->createBook('Legacy paid'), 'returned', '2026-09-15', '2026-09-15 09:00:00', '2026-10-19');
        $legacyPaidFine = Fine::create(['loan_id' => $legacyPaidLoan->id, 'jumlah_denda' => 2000, 'status_bayar' => 'paid']);
        $legacyPaidFine->forceFill(['created_at' => '2026-10-20 11:00:00'])->save();

        $this->actingAs($admin)
            ->get(route('admin.reports.monthly', ['month' => '2026-10']))
            ->assertOk()
            ->assertSee('Peminjaman dimulai')
            ->assertSee('2 peminjaman lama dihitung', false)
            ->assertSee('Belum lunas pada akhir bulan')
            ->assertSee('Denda dibayar')
            ->assertSee('Data belum tersedia');

        $report = app(BuildMonthlyReportAction::class)->execute(
            CarbonImmutable::parse('2026-10-01', config('app.timezone'))
        );

        $this->assertSame(3, $report['circulation']['started_count']);
        $this->assertSame(2, $report['circulation']['started_legacy_count']);
        $this->assertSame(5, $report['circulation']['returned_count']);
        $this->assertCount(7, $report['loan_details']);
        $this->assertSame(2, $report['members']['borrowers_count']);
        $this->assertNull($report['members']['active_members_count']);
        $this->assertSame(1, $report['members']['new_members_count']);
        $this->assertSame(4, $report['fines']['issued_count']);
        $this->assertSame(17000, $report['fines']['issued_amount']);
        $this->assertSame(1, $report['fines']['paid_count']);
        $this->assertSame(7000, $report['fines']['paid_amount']);
        $this->assertSame(2, $report['fines']['outstanding_count']);
        $this->assertSame(8000, $report['fines']['outstanding_amount']);
        $this->assertSame(1, $report['fines']['paid_without_timestamp_count']);
        $this->assertSame(2000, $report['fines']['paid_without_timestamp_amount']);
    }

    public function test_report_lists_returned_loan_details_for_the_selected_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $borrower = User::factory()->create([
            'name' => 'Nadia Sari',
            'nama' => 'Nadia Sari',
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $book = $this->createBook('Laut Bercerita');
        $loan = $this->createLoan($borrower, $book, 'returned', '2026-10-15', '2026-10-15 09:00:00', '2026-10-22');
        $loan->update([
            'keterangan_kondisi' => 'rusak_ringan',
            'catatan_admin' => 'Sampul sedikit terlipat.',
            'denda_terlambat' => 2000,
            'denda_tambahan' => 5000,
            'total_denda' => 7000,
        ]);
        $activeLoan = $this->createLoan(
            $borrower,
            $this->createBook('Buku masih dipinjam'),
            'borrowed',
            '2026-10-25',
            '2026-10-25 09:00:00',
        );
        Fine::create([
            'loan_id' => $loan->id,
            'jumlah_denda' => 7000,
            'status_bayar' => 'unpaid',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.monthly', ['month' => '2026-10']))
            ->assertOk()
            ->assertSee('Rincian peminjaman')
            ->assertSee('Nadia Sari')
            ->assertSee('Laut Bercerita')
            ->assertSee('15 Okt 2026')
            ->assertSee('22 Okt 2026')
            ->assertSee('7 hari')
            ->assertSee('Rusak ringan')
            ->assertSee('Rp7.000')
            ->assertSee('Sampul sedikit terlipat.')
            ->assertSee('Buku masih dipinjam')
            ->assertSee('Belum kembali')
            ->assertSee('Sedang dipinjam')
            ->assertSee('Belum dihitung');

        $pdfResponse = $this->get(route('admin.reports.monthly.pdf', ['month' => '2026-10']));
        $pdfResponse->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        $details = app(BuildMonthlyReportAction::class)
            ->execute(CarbonImmutable::parse('2026-10-01', config('app.timezone')))['loan_details'];

        $this->assertCount(2, $details);
        $returnedDetails = $details->firstWhere('book', 'Laut Bercerita');
        $activeDetails = $details->firstWhere('book', 'Buku masih dipinjam');
        $this->assertSame('Belum lunas', $returnedDetails['fine_status']);
        $this->assertSame('Sedang dipinjam', $activeDetails['status_label']);
        $this->assertNull($activeDetails['fine_amount']);
        $this->assertSame(2000, $returnedDetails['late_fine']);
        $this->assertSame(5000, $returnedDetails['extra_fine']);
        $this->assertSame(2000, $details->first()['late_fine']);
        $this->assertSame(5000, $details->first()['extra_fine']);
    }

    public function test_month_boundaries_include_the_last_moment_and_exclude_the_next_month(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $lastDayLoan = $this->createLoan($member, $this->createBook('Last day'), 'returned', '2026-10-31', '2026-10-31 23:59:59', '2026-10-31');
        $nextMonthLoan = $this->createLoan($member, $this->createBook('Next month'), 'returned', '2026-11-01', '2026-11-01 00:00:00', '2026-11-01');
        Fine::create([
            'loan_id' => $lastDayLoan->id,
            'jumlah_denda' => 4000,
            'status_bayar' => 'paid',
            'paid_at' => '2026-10-31 23:59:59',
        ]);
        Fine::create([
            'loan_id' => $nextMonthLoan->id,
            'jumlah_denda' => 9000,
            'status_bayar' => 'paid',
            'paid_at' => '2026-11-01 00:00:00',
        ]);

        $report = app(BuildMonthlyReportAction::class)->execute(
            CarbonImmutable::parse('2026-10-01', config('app.timezone'))
        );

        $this->assertSame(1, $report['circulation']['started_count']);
        $this->assertSame(1, $report['circulation']['returned_count']);
        $this->assertCount(1, $report['loan_details']);
        $this->assertSame('Last day', $report['loan_details']->first()['book']);
        $this->assertSame(1, $report['fines']['paid_count']);
        $this->assertSame(4000, $report['fines']['paid_amount']);
    }

    public function test_active_member_count_is_only_shown_for_the_current_month(): void
    {
        User::factory()->count(2)->create(['role' => 'siswa', 'status' => 'active']);
        User::factory()->create(['role' => 'siswa', 'status' => 'inactive']);
        User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $action = app(BuildMonthlyReportAction::class);

        $currentMonth = $action->execute(CarbonImmutable::parse('2026-11-01', config('app.timezone')));
        $previousMonth = $action->execute(CarbonImmutable::parse('2026-10-01', config('app.timezone')));

        $this->assertSame(2, $currentMonth['members']['active_members_count']);
        $this->assertNull($previousMonth['members']['active_members_count']);
    }

    public function test_marking_a_fine_paid_records_the_payment_timestamp(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $borrower = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $loan = $this->createLoan($borrower, $this->createBook('Fine payment'), 'returned', '2026-10-01', '2026-10-01 09:00:00', '2026-10-02');
        $fine = Fine::create(['loan_id' => $loan->id, 'jumlah_denda' => 1000, 'status_bayar' => 'unpaid']);

        $this->actingAs($admin)
            ->patch(route('admin.fines.paid', $fine))
            ->assertRedirect();

        $this->assertSame('paid', $fine->fresh()->status_bayar);
        $this->assertSame('2026-11-04 12:00:00', $fine->fresh()->paid_at->format('Y-m-d H:i:s'));
    }

    private function createBook(string $title): Book
    {
        return Book::create([
            'judul' => $title,
            'penulis' => 'Test author',
            'jumlah_total' => 2,
            'stok_tersedia' => 2,
            'is_circulating' => true,
            'status' => 'tersedia',
        ]);
    }

    private function createLoan(
        User $user,
        Book $book,
        string $status,
        string $borrowedDate,
        ?string $confirmedAt,
        ?string $returnedDate = null,
    ): Loan {
        return Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => $borrowedDate,
            'tenggat_kembali' => '2026-10-30',
            'tanggal_kembali' => $returnedDate,
            'confirmed_at' => $confirmedAt,
            'status' => $status,
        ]);
    }
}
