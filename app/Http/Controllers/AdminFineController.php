<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminFineController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function markPaid(Fine $fine): RedirectResponse
    {
        DB::transaction(function () use ($fine): void {
            $lockedFine = Fine::query()->lockForUpdate()->findOrFail($fine->id);

            if ($lockedFine->status_bayar !== 'unpaid') {
                abort(422, 'Denda ini sudah ditandai lunas.');
            }

            $lockedFine->update([
                'status_bayar' => 'paid',
                'paid_at' => now(),
            ]);
        });

        return back()->with('success', 'Pembayaran denda berhasil dicatat sebagai lunas.');
    }
}