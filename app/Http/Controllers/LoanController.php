<?php

namespace App\Http\Controllers;

use App\Actions\BorrowBookAction;
use App\Actions\ConfirmLoanHandoffAction;
use App\Actions\ProcessBookReturnAction;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanStatusRequest;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Notifications\LoanDueReminder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'borrowed', 'overdue'];

    public function __construct(
        private readonly BorrowBookAction $borrowBook,
        private readonly ConfirmLoanHandoffAction $confirmHandoff,
        private readonly ProcessBookReturnAction $processReturn,
    ) {
        $this->middleware('auth');
    }

    public function store(StoreLoanRequest $request, Book $book): RedirectResponse
    {
        $this->borrowBook->execute($request->user(), $book->id);

        return redirect()->route('loans.mine')->with('success', 'Reservasi berhasil. Tunjukkan QR konfirmasi kepada admin saat mengambil buku.');
    }

    public function return(Loan $loan): RedirectResponse
    {
        abort_unless(request()->user()->id === $loan->user_id || request()->user()->role === 'admin', 403);
        $this->processReturn->execute($loan, []);

        return back()->with('success', 'Buku berhasil dikembalikan.');
    }

    public function mine(): View
    {
        $this->markOverdue(request()->user()->id);

        return view('loans.index', [
            'loans' => Loan::with(['book', 'fine'])->where('user_id', request()->user()->id)->latest()->get(),
            'isAdmin' => false,
        ]);
    }

    public function mineStatus(): JsonResponse
    {
        return response()->json([
            'loans' => Loan::query()
                ->with('book:id,lokasi_rak')
                ->where('user_id', request()->user()->id)
                ->whereIn('status', ['pending', 'borrowed', 'overdue', 'returned', 'rejected'])
                ->latest()
                ->get(['id', 'book_id', 'status', 'tanggal_pinjam', 'tenggat_kembali', 'confirmed_at'])
                ->map(fn (Loan $loan): array => [
                    'id' => $loan->id,
                    'status' => $loan->status,
                    'borrowed_at' => $loan->status === 'pending' ? null : $loan->tanggal_pinjam?->format('d M Y'),
                    'due_date' => $loan->status === 'pending' ? null : $loan->tenggat_kembali?->format('d M Y'),
                    'shelf_location' => $loan->confirmed_at ? $loan->book?->lokasi_rak : null,
                ]),
        ]);
    }

    public function adminIndex(): View
    {
        $this->markOverdue();

        return view('loans.index', [
            'loans' => Loan::with(['book', 'user', 'fine'])->latest()->get(),
            'isAdmin' => true,
        ]);
    }

    public function scanPage(): View
    {
        return view('admin.loans.scan');
    }

    public function confirmHandoff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'digits:5'],
        ]);

        $loan = $this->confirmHandoff->execute($validated['token']);
        $notice = [
            'borrower' => $loan->user->name,
            'book' => $loan->book->judul,
            'due_date' => $loan->tenggat_kembali->format('d M Y'),
            'shelf_location' => $loan->book->lokasi_rak,
        ];
        $request->session()->flash('handoff_notice', $notice);

        return response()->json([
            'message' => 'Peminjaman berhasil dikonfirmasi.',
            'loan' => [
                'borrower' => $loan->user->name,
                'book' => $loan->book->judul,
                'due_date' => $loan->tenggat_kembali->format('d M Y'),
                'shelf_location' => $loan->book->lokasi_rak,
            ],
        ]);
    }

    public function updateStatus(UpdateLoanStatusRequest $request, Loan $loan): RedirectResponse
    {
        DB::transaction(function () use ($request, $loan): void {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $newStatus = $request->string('status')->toString();
            if ($newStatus === 'borrowed' && $lockedLoan->status !== 'borrowed') {
                abort(422, 'Konfirmasi peminjaman harus dilakukan melalui scan QR.');
            }
            if ($newStatus === 'pending' && $lockedLoan->status !== 'pending') {
                abort(422, 'Status reservasi tidak dapat diaktifkan kembali.');
            }

            if (in_array($lockedLoan->status, ['returned', 'rejected'], true)) {
                abort(422, 'Peminjaman yang sudah selesai atau ditolak tidak dapat diaktifkan kembali.');
            }

            if ($newStatus === 'overdue' && ! in_array($lockedLoan->status, ['borrowed', 'overdue'], true)) {
                abort(422, 'Reservasi harus dikonfirmasi melalui scan QR sebelum dapat terlambat.');
            }

            $wasActive = in_array($lockedLoan->status, self::ACTIVE_STATUSES, true);
            $willBeActive = in_array($newStatus, self::ACTIVE_STATUSES, true);

            if (! $wasActive && $willBeActive) {
                $book = Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id);
                if (! $book->is_circulating || $book->stok_tersedia < 1) {
                    abort(422, 'Stok tidak cukup untuk mengaktifkan kembali peminjaman ini.');
                }
                $book->decrement('stok_tersedia');
                $book->syncAvailabilityStatus();
            } elseif ($wasActive && ! $willBeActive) {
                $book = Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id);
                $book->increment('stok_tersedia');
                $book->syncAvailabilityStatus();
            }

            $lockedLoan->update([
                'status' => $newStatus,
                'confirmation_token' => $newStatus === 'rejected' ? null : $lockedLoan->confirmation_token,
            ]);
        });

        return back()->with('success', 'Status peminjaman berhasil diperbarui.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        $title = (string) $loan->book?->judul;

        DB::transaction(function () use ($loan): void {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if (in_array($lockedLoan->status, self::ACTIVE_STATUSES, true)) {
                $book = Book::query()->lockForUpdate()->findOrFail($lockedLoan->book_id);
                $book->update([
                    'stok_tersedia' => min($book->jumlah_total, $book->stok_tersedia + 1),
                ]);
                $book->syncAvailabilityStatus();
            }

            DB::table('notifications')
                ->where('type', LoanDueReminder::class)
                ->whereJsonContains('data->loan_id', $lockedLoan->id)
                ->delete();

            Fine::query()->where('loan_id', $lockedLoan->id)->delete();
            $lockedLoan->delete();
        });

        return back()->with('success', "Transaksi \"{$title}\" dihapus dan stok buku disesuaikan.");
    }

    private function markOverdue(?int $userId = null): void
    {
        Loan::query()->where('status', 'borrowed')
            ->whereDate('tenggat_kembali', '<', now()->toDateString())
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->update(['status' => 'overdue']);
    }
}
