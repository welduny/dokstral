<?php

namespace App\Actions;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowBookAction
{
    private const MAX_ACTIVE_LOANS = 3;

    private const LOAN_DAYS = 7;

    private const ACTIVE_STATUSES = ['pending', 'borrowed', 'overdue'];

    public function execute(User $user, int $bookId): Loan
    {
        return DB::transaction(function () use ($user, $bookId): Loan {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->role !== 'siswa') {
                throw ValidationException::withMessages([
                    'book' => 'Akun admin tidak dapat meminjam buku. Gunakan akun anggota untuk peminjaman.',
                ]);
            }

            if ($lockedUser->hasUnpaidFine()) {
                throw ValidationException::withMessages(['book' => 'Selesaikan denda yang belum dibayar terlebih dahulu.']);
            }

            if ($lockedUser->loans()->whereIn('status', self::ACTIVE_STATUSES)->whereDate('tenggat_kembali', '<', Date::today())->exists()) {
                throw ValidationException::withMessages(['book' => 'Kamu masih memiliki buku yang terlambat dikembalikan.']);
            }

            if ($lockedUser->loans()->whereIn('status', self::ACTIVE_STATUSES)->count() >= self::MAX_ACTIVE_LOANS) {
                throw ValidationException::withMessages(['book' => 'Batas peminjaman aktif adalah 3 buku.']);
            }

            $book = Book::query()->lockForUpdate()->findOrFail($bookId);

            if (! $book->is_circulating) {
                throw ValidationException::withMessages(['book' => 'Buku ini tidak tersedia untuk dipinjam.']);
            }

            if ($book->stok_tersedia < 1) {
                throw ValidationException::withMessages(['book' => 'Stok buku sedang habis.']);
            }

            if ($lockedUser->loans()->where('book_id', $book->id)->whereIn('status', self::ACTIVE_STATUSES)->exists()) {
                throw ValidationException::withMessages(['book' => 'Kamu sedang meminjam buku ini.']);
            }

            $today = Date::today();
            $book->decrement('stok_tersedia');
            $book->syncAvailabilityStatus();

            $confirmationToken = $this->generateConfirmationToken();

            return Loan::create([
                'user_id' => $lockedUser->id,
                'book_id' => $book->id,
                'tanggal_pinjam' => $today,
                'tenggat_kembali' => $today->copy()->addDays(self::LOAN_DAYS),
                'confirmation_token' => $confirmationToken,
                'status' => 'pending',
            ]);
        });
    }

    private function generateConfirmationToken(): string
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $token = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

            if (! Loan::query()->where('confirmation_token', $token)->exists()) {
                return $token;
            }
        }

        throw ValidationException::withMessages([
            'book' => 'Token konfirmasi sedang penuh. Coba lagi beberapa saat.',
        ]);
    }
}
