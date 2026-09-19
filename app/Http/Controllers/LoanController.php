<?php

namespace App\Http\Controllers;

use App\Actions\BorrowBookAction;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanStatusRequest;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function __construct(private readonly BorrowBookAction $borrowBook)
    {
        $this->middleware('auth');
    }

    public function store(StoreLoanRequest $request, Book $book): RedirectResponse
    {
        $this->borrowBook->execute($request->user(), $book->id);

        return back()->with('success', 'Buku berhasil dipinjam selama 7 hari.');
    }

    public function return(Loan $loan): RedirectResponse
    {
        abort_unless(request()->user()->id === $loan->user_id || request()->user()->role === 'admin', 403);

        DB::transaction(function () use ($loan): void {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            if (in_array($lockedLoan->status, ['returned', 'rejected'], true)) {
                return;
            }

            $today = Date::today();
            $lateDays = max(0, $lockedLoan->tenggat_kembali->diffInDays($today, false));
            $lockedLoan->update(['tanggal_kembali' => $today, 'status' => 'returned']);
            Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id)->increment('stok_tersedia');

            if ($lateDays > 0) {
                Fine::updateOrCreate(
                    ['loan_id' => $lockedLoan->id],
                    ['jumlah_denda' => $lateDays * 1000, 'status_bayar' => 'unpaid']
                );
            }
        });

        return back()->with('success', 'Buku berhasil dikembalikan.');
    }

    public function mine(): \Illuminate\Contracts\View\View
    {
        $this->markOverdue(request()->user()->id);

        return view('loans.index', [
            'loans' => Loan::with('book')->where('user_id', request()->user()->id)->latest()->get(),
            'isAdmin' => false,
        ]);
    }

    public function adminIndex(): \Illuminate\Contracts\View\View
    {
        $this->markOverdue();

        return view('loans.index', [
            'loans' => Loan::with(['book', 'user'])->latest()->get(),
            'isAdmin' => true,
        ]);
    }

    public function updateStatus(UpdateLoanStatusRequest $request, Loan $loan): RedirectResponse
    {
        DB::transaction(function () use ($request, $loan): void {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $newStatus = $request->string('status')->toString();

            if ($lockedLoan->status !== 'returned' && $newStatus === 'returned') {
                Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id)->increment('stok_tersedia');
                $lockedLoan->update(['tanggal_kembali' => now()->toDateString()]);
            } elseif ($lockedLoan->status === 'returned' && $newStatus !== 'returned') {
                $book = Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id);
                if ($book->stok_tersedia < 1) {
                    abort(422, 'Stok tidak cukup untuk mengaktifkan kembali peminjaman ini.');
                }
                $book->decrement('stok_tersedia');
                $lockedLoan->update(['tanggal_kembali' => null]);
            }

            $lockedLoan->update(['status' => $newStatus]);
        });

        return back()->with('success', 'Status peminjaman berhasil diperbarui.');
    }

    private function markOverdue(?int $userId = null): void
    {
        Loan::query()->whereIn('status', ['pending', 'borrowed'])
            ->whereDate('tenggat_kembali', '<', now()->toDateString())
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->update(['status' => 'overdue']);
    }
}