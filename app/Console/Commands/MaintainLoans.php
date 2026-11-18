<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class MaintainLoans extends Command
{
    protected $signature = 'loans:maintain';
    protected $description = 'Expire pending loans and mark late loans as overdue';

    public function handle(): int
    {
        $pendingCutoff = Date::now()->subHours(24);
        Loan::query()
            ->where('status', 'pending')
            ->where('created_at', '<', $pendingCutoff)
            ->orderBy('id')
            ->chunkById(100, function ($loans): void {
                foreach ($loans as $loan) {
                    DB::transaction(function () use ($loan): void {
                        $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
                        if ($lockedLoan->status !== 'pending') {
                            return;
                        }

                        $book = $lockedLoan->book()->lockForUpdate()->firstOrFail();
                        $lockedLoan->update([
                            'status' => 'rejected',
                            'confirmation_token' => null,
                        ]);
                        $book->increment('stok_tersedia');
                        $book->syncAvailabilityStatus();
                    });
                }
            });

        Loan::where('status', 'borrowed')
            ->whereDate('tenggat_kembali', '<', Date::today())
            ->update(['status' => 'overdue']);

        $this->info('Loan maintenance completed.');
        return self::SUCCESS;
    }
}