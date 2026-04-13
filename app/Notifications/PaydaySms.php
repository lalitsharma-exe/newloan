<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\LoanInstallment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaydaySms extends Notification implements ShouldQueue
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

        return "Hi {$name}, \n" .
               "It’s payday. Please remember to pay your MyLoan amount of M{$amount} today. Thanks for staying on track.";
    }
}
