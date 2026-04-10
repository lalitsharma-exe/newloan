<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LoanFullyPaidSms extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Loan $loan)
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

        return "Hi {$name}, your MyLoan loan is now fully paid 🎉 \n\n" .
               "Great news — you qualify for another loan! \n" .
               "Apply anytime here: https://myloan.co.ls \n\n" .
               "Thank you for choosing MyLoan";
    }
}
