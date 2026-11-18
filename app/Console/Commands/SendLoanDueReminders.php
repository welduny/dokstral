<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Notifications\LoanDueReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class SendLoanDueReminders extends Command
{
    protected $signature = 'loans:send-due-reminders';
    protected $description = 'Notify members three days and one day before, and on, a loan due date';

    public function handle(): int
    {
        $today = Date::today();
        $sent = 0;

        foreach ([3, 1, 0] as $daysRemaining) {
            $dueDate = $today->copy()->addDays($daysRemaining)->toDateString();
            $loans = Loan::query()
                ->with(['user', 'book'])
                ->where('status', 'borrowed')
                ->whereDate('tenggat_kembali', $dueDate)
                ->get();

            foreach ($loans as $loan) {
                $alreadySent = $loan->user->notifications()
                    ->where('type', LoanDueReminder::class)
                    ->whereJsonContains('data->loan_id', $loan->id)
                    ->whereJsonContains('data->days_remaining', $daysRemaining)
                    ->exists();

                if (!$alreadySent) {
                    $loan->user->notify(new LoanDueReminder($loan, $daysRemaining));
                    $sent++;
                }
            }
        }

        $this->info("Sent {$sent} due-date reminders.");

        return self::SUCCESS;
    }
}