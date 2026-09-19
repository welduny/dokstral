<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class MaintainLoans extends Command
{
    protected $signature = 'loans:maintain';
    protected $description = 'Expire pending loans and mark late loans as overdue';

    public function handle(): int
    {
        $pendingCutoff = Date::now()->subHours(24);
        Loan::where('status', 'pending')
            ->where('created_at', '<', $pendingCutoff)
            ->update(['status' => 'rejected']);

        Loan::whereIn('status', ['pending', 'borrowed'])
            ->whereDate('tenggat_kembali', '<', Date::today())
            ->update(['status' => 'overdue']);

        $this->info('Loan maintenance completed.');
        return self::SUCCESS;
    }
}