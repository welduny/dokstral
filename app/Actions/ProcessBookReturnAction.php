<?php

namespace App\Actions;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessBookReturnAction
{
    public function __construct(private readonly BookFinePolicy $finePolicy)
    {
    }

    public function execute(Loan $loan, array $data): Loan
    {
        return DB::transaction(function () use ($loan, $data): Loan {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            if (in_array($lockedLoan->status, ['pending', 'returned', 'rejected'], true)) {
                throw ValidationException::withMessages([
                    'loan' => 'Peminjaman ini belum dikonfirmasi, sudah selesai, atau ditolak.',
                ]);
            }

            $book = Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id);
            $returnedAt = Date::today();
            $lateDays = max(0, $lockedLoan->tenggat_kembali->diffInDays($returnedAt, false));
            $lateFine = $lateDays * BookFinePolicy::LATE_FINE_PER_DAY;
            $condition = $data['keterangan_kondisi'] ?? 'baik';
            $extraFine = $this->finePolicy->damageFine($condition, $lockedLoan->harga_buku ?? $book->harga);
            $totalFine = $lateFine + $extraFine;
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
            }
            $book->syncAvailabilityStatus();

            if ($totalFine > 0) {
                $existingFine = Fine::query()->where('loan_id', $lockedLoan->id)->first();
                Fine::updateOrCreate(
                    ['loan_id' => $lockedLoan->id],
                    [
                        'jumlah_denda' => $totalFine,
                        'status_bayar' => $existingFine?->status_bayar === 'paid' ? 'paid' : 'unpaid',
                    ]
                );
            }

            return $lockedLoan;
        });
    }
}