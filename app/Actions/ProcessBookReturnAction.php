<?php

namespace App\Actions;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ProcessBookReturnAction
{
    private const LATE_FINE_PER_DAY = 2000;

    public function execute(Loan $loan, array $data): Loan
    {
        return DB::transaction(function () use ($loan, $data): Loan {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $book = Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id);
            $returnedAt = Date::today();
            $lateDays = max(0, $lockedLoan->tenggat_kembali->diffInDays($returnedAt, false));
            $lateFine = $lateDays * self::LATE_FINE_PER_DAY;
            $extraFine = (int) ($data['denda_tambahan'] ?? 0);
            $totalFine = $lateFine + $extraFine;
            $condition = $data['keterangan_kondisi'];
            $canReturnToStock = in_array($condition, ['baik', 'rusak_ringan'], true);

            $lockedLoan->update([
                'tanggal_kembali' => $returnedAt,
                'keterangan_kondisi' => $condition,
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'denda_terlambat' => $lateFine,
                'denda_tambahan' => $extraFine,
                'total_denda' => $totalFine,
                'status' => 'returned',
            ]);

            if ($canReturnToStock) {
                $book->increment('stok_tersedia');
                $book->update(['status' => 'tersedia']);
            } else {
                $book->update(['status' => 'tidak_tersedia']);
            }

            if ($totalFine > 0) {
                Fine::updateOrCreate(
                    ['loan_id' => $lockedLoan->id],
                    ['jumlah_denda' => $totalFine, 'status_bayar' => 'unpaid']
                );
            }

            return $lockedLoan;
        });
    }
}