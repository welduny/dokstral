<?php

namespace App\Actions;

use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class BuildMonthlyReportAction
{
    private const HANDOFF_TRACKING_STARTED_AT = '2026-10-19 00:00:00';

    public function execute(CarbonImmutable $monthStart): array
    {
        $monthStart = $monthStart->startOfMonth()->startOfDay();
        $nextMonthStart = $monthStart->addMonth();
        $now = CarbonImmutable::now(config('app.timezone'));
        $isCurrentMonth = $monthStart->format('Y-m') === $now->format('Y-m');
        $coverageEnd = $isCurrentMonth ? $now->startOfDay() : $monthStart->endOfMonth();
        $coverageEndExclusive = $coverageEnd->addDay()->startOfDay();
        $timestampLimit = $isCurrentMonth ? $now->addMicrosecond() : $nextMonthStart;
        $startDate = $monthStart->toDateString();
        $coverageEndDate = $coverageEnd->toDateString();
        $coverageNextDate = $coverageEndExclusive->toDateString();

        $startedLoans = $this->startedLoansQuery($monthStart, $timestampLimit, $startDate, $coverageNextDate);
        $legacyStartedLoans = $this->legacyStartedLoansQuery($startDate, $coverageNextDate);
        $startedCount = (clone $startedLoans)->count();
        $legacyStartedCount = $legacyStartedLoans->count();
        $borrowerIds = (clone $startedLoans)->distinct()->pluck('user_id');

        $issuedFines = Fine::query()
            ->join('loans', 'loans.id', '=', 'fines.loan_id')
            ->where('loans.status', 'returned')
            ->whereNotNull('loans.tanggal_kembali')
            ->where('loans.tanggal_kembali', '>=', $startDate)
            ->where('loans.tanggal_kembali', '<', $coverageNextDate);

        $paidFines = Fine::query()
            ->where('status_bayar', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $monthStart)
            ->where('paid_at', '<', $timestampLimit);

        $outstandingFines = Fine::query()
            ->where('created_at', '<', $timestampLimit)
            ->whereHas('loan', function (Builder $loan) use ($coverageNextDate): void {
                $loan->where('status', 'returned')
                    ->whereNotNull('tanggal_kembali')
                    ->where('tanggal_kembali', '<', $coverageNextDate);
            })
            ->where(function (Builder $query) use ($timestampLimit): void {
                $query->where('status_bayar', 'unpaid')
                    ->orWhere(function (Builder $paid) use ($timestampLimit): void {
                        $paid->where('status_bayar', 'paid')
                            ->whereNotNull('paid_at')
                            ->where('paid_at', '>=', $timestampLimit);
                    });
            });

        $unassignedPaidFines = Fine::query()
            ->where('status_bayar', 'paid')
            ->whereNull('paid_at')
            ->whereHas('loan', function (Builder $loan) use ($coverageNextDate): void {
                $loan->whereNotNull('tanggal_kembali')
                    ->where('tanggal_kembali', '<', $coverageNextDate);
            });

        $returnedMissingDateCount = Loan::query()
            ->where('status', 'returned')
            ->whereNull('tanggal_kembali')
            ->count();

        $returnedLoanDetails = Loan::query()
            ->with([
                'user:id,name,nama',
                'book:id,judul',
                'fine:id,loan_id,jumlah_denda,status_bayar',
            ])
            ->whereIn('status', ['borrowed', 'overdue', 'returned'])
            ->where(function (Builder $activity) use ($startedLoans, $startDate, $coverageNextDate): void {
                $activity->whereIn('loans.id', (clone $startedLoans)->select('loans.id'))
                    ->orWhere(function (Builder $returned) use ($startDate, $coverageNextDate): void {
                        $returned->where('status', 'returned')
                            ->whereNotNull('tanggal_kembali')
                            ->where('tanggal_kembali', '>=', $startDate)
                            ->where('tanggal_kembali', '<', $coverageNextDate);
                    });
            })
            ->orderByDesc('tanggal_kembali')
            ->orderByDesc('tanggal_pinjam')
            ->orderByDesc('id')
            ->get()
            ->map(function (Loan $loan) use ($coverageEnd): array {
                $returnedOn = $loan->tanggal_kembali;
                $fineAmount = $loan->fine?->jumlah_denda ?? ($loan->total_denda > 0 ? $loan->total_denda : ($returnedOn ? 0 : null));
                $conditionLabels = [
                    'baik' => 'Baik',
                    'rusak_ringan' => 'Rusak ringan',
                    'rusak_berat' => 'Rusak berat',
                    'hilang' => 'Hilang',
                ];

                return [
                    'borrower' => $loan->user?->nama ?: ($loan->user?->name ?: 'Peminjam tidak tersedia'),
                    'book' => $loan->book?->judul ?: 'Buku tidak tersedia',
                    'borrowed_on' => $loan->tanggal_pinjam,
                    'returned_on' => $returnedOn,
                    'duration_days' => (int) $loan->tanggal_pinjam->diffInDays($returnedOn ?? $coverageEnd),
                    'status_label' => $returnedOn
                        ? 'Dikembalikan'
                        : ($loan->status === 'overdue' ? 'Terlambat' : ($loan->status === 'returned' ? 'Tanggal kembali belum tercatat' : 'Sedang dipinjam')),
                    'condition' => $returnedOn
                        ? ($conditionLabels[$loan->keterangan_kondisi] ?? 'Belum dicatat')
                        : 'Belum dikembalikan',
                    'late_fine' => (int) $loan->denda_terlambat,
                    'extra_fine' => (int) $loan->denda_tambahan,
                    'fine_amount' => $fineAmount,
                    'fine_status' => match ($loan->fine?->status_bayar) {
                        'paid' => 'Lunas',
                        'unpaid' => 'Belum lunas',
                        default => $fineAmount === null ? 'Belum dihitung' : ($fineAmount > 0 ? 'Status belum tercatat' : 'Tidak ada denda'),
                    },
                    'notes' => trim((string) $loan->catatan_admin) ?: ($returnedOn ? 'Tidak ada catatan.' : 'Belum ada catatan pengembalian.'),
                ];
            });

        return [
            'loan_details' => $returnedLoanDetails,
            'period' => [
                'month_value' => $monthStart->format('Y-m'),
                'start_date' => $startDate,
                'coverage_end' => $coverageEndDate,
                'coverage_end_exclusive' => $coverageNextDate,
                'is_current_month' => $isCurrentMonth,
                'as_of_date' => $coverageEndDate,
            ],
            'circulation' => [
                'started_count' => $startedCount,
                'started_legacy_count' => $legacyStartedCount,
                'returned_count' => Loan::query()
                    ->where('status', 'returned')
                    ->whereNotNull('tanggal_kembali')
                    ->where('tanggal_kembali', '>=', $startDate)
                    ->where('tanggal_kembali', '<', $coverageNextDate)
                    ->count(),
                'returned_missing_date_count' => $returnedMissingDateCount,
            ],
            'fines' => [
                'issued_count' => $issuedFines->count('fines.id'),
                'issued_amount' => (int) $issuedFines->sum('fines.jumlah_denda'),
                'paid_count' => $paidFines->count(),
                'paid_amount' => (int) $paidFines->sum('jumlah_denda'),
                'outstanding_count' => $outstandingFines->count(),
                'outstanding_amount' => (int) $outstandingFines->sum('jumlah_denda'),
                'paid_without_timestamp_count' => $unassignedPaidFines->count(),
                'paid_without_timestamp_amount' => (int) $unassignedPaidFines->sum('jumlah_denda'),
                'outstanding_history_unavailable' => ! $isCurrentMonth && $unassignedPaidFines->exists(),
            ],
            'members' => [
                'borrowers_count' => User::query()
                    ->where('role', 'siswa')
                    ->whereIn('id', $borrowerIds)
                    ->count(),
                'active_members_count' => $isCurrentMonth
                    ? User::query()->where('role', 'siswa')->where('status', 'active')->count()
                    : null,
                'new_members_count' => User::query()
                    ->where('role', 'siswa')
                    ->where('created_at', '>=', $monthStart)
                    ->where('created_at', '<', $timestampLimit)
                    ->count(),
            ],
        ];
    }

    private function startedLoansQuery(
        CarbonImmutable $monthStart,
        CarbonImmutable $coverageEndExclusive,
        string $startDate,
        string $coverageNextDate,
    ): Builder {
        return Loan::query()->where(function (Builder $query) use ($monthStart, $coverageEndExclusive, $startDate, $coverageNextDate): void {
            $query->where(function (Builder $confirmed) use ($monthStart, $coverageEndExclusive): void {
                $confirmed->whereNotNull('confirmed_at')
                    ->where('confirmed_at', '>=', $monthStart)
                    ->where('confirmed_at', '<', $coverageEndExclusive);
            })->orWhere(function (Builder $legacy) use ($startDate, $coverageNextDate): void {
                $legacy->whereNull('confirmed_at')
                    ->where('created_at', '<', self::HANDOFF_TRACKING_STARTED_AT)
                    ->whereIn('status', ['borrowed', 'overdue', 'returned'])
                    ->where('tanggal_pinjam', '>=', $startDate)
                    ->where('tanggal_pinjam', '<', $coverageNextDate);
            });
        });
    }

    private function legacyStartedLoansQuery(string $startDate, string $coverageNextDate): Builder
    {
        return Loan::query()
            ->whereNull('confirmed_at')
            ->where('created_at', '<', self::HANDOFF_TRACKING_STARTED_AT)
            ->whereIn('status', ['borrowed', 'overdue', 'returned'])
            ->where('tanggal_pinjam', '>=', $startDate)
            ->where('tanggal_pinjam', '<', $coverageNextDate);
    }
}
