<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\LoanInstallment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentReminderSms extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected LoanInstallment $installment)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $name = $notifiable->name;
        $amount = number_format($this->installment->outstanding_amount, 2);
        
        // Ensure due_date is treated as a Carbon instance
        $dueDateRaw = $this->installment->due_date;
        $dueDateStr = ($dueDateRaw instanceof \Carbon\Carbon) ? $dueDateRaw->format('d M Y') : date('d M Y', strtotime($dueDateRaw));

        return "Hi {$name}, \n" .
               "Reminder: your MyLoan payment of M{$amount} is due {$dueDateStr}. Please ensure enough funds to avoid charges.";
    }
}
