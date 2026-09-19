<?php

namespace App\Http\Controllers;

use App\Actions\ProcessBookReturnAction;
use App\Http\Requests\ProcessBookReturnRequest;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;

class AdminReturnBookController extends Controller
{
    public function __construct(private readonly ProcessBookReturnAction $processReturn)
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function store(ProcessBookReturnRequest $request, Loan $loan): RedirectResponse
    {
        $processedLoan = $this->processReturn->execute($loan, $request->validated());
        $message = $processedLoan->total_denda > 0
            ? 'Pengembalian diproses. Total denda: Rp'.number_format($processedLoan->total_denda, 0, ',', '.').'.'
            : 'Pengembalian berhasil diproses tanpa denda.';

        return back()->with('success', $message);
    }
}