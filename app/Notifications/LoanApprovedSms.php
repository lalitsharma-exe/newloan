<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LoanApprovedSms extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected LoanApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): string
    {
        $appNum = $this->application->application_number;
        $amount = number_format($this->application->approved_amount, 2);
        
        return "Congratulations! Your loan application {$appNum} for M{$amount} has been APPROVED. Funds will be disbursed shortly.";
    }
}
