<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class LoanDueReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Loan $loan,
        private readonly int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'book_title' => $this->loan->book->judul,
            'due_date' => $this->loan->tenggat_kembali->format('Y-m-d'),
            'days_remaining' => $this->daysRemaining,
            'message' => $this->daysRemaining === 0
                ? 'Buku jatuh tempo hari ini.'
                : 'Buku jatuh tempo dalam '.$this->daysRemaining.' hari.',
        ];
    }
}