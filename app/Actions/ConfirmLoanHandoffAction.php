<?php

namespace App\Actions;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmLoanHandoffAction
{
    private const LOAN_DAYS = 7;

    public function execute(string $token): Loan
    {
        return DB::transaction(function () use ($token): Loan {
            $loan = Loan::query()
                ->where('confirmation_token', $token)
                ->lockForUpdate()
                ->first();

            if (!$loan) {
                throw ValidationException::withMessages([
                    'token' => 'Kode tidak ditemukan. Pastikan kode QR atau kode manual sesuai dengan peminjaman yang aktif.',
                ]);
            }

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages([
                    'token' => 'Kode ini sudah dipakai atau peminjaman sudah dikonfirmasi sebelumnya.',
                ]);
            }

            $book = Book::query()->lockForUpdate()->findOrFail($loan->book_id);
            $today = Date::today();
            $loan->update([
                'status' => 'borrowed',
                'tanggal_pinjam' => $today,
                'tenggat_kembali' => $today->copy()->addDays(self::LOAN_DAYS),
                'confirmed_at' => now(),
                'harga_buku' => $book->harga,
                'confirmation_token' => null,
            ]);

            return $loan->load(['book', 'user']);
        });
    }
}