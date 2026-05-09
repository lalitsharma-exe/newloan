<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LoanDeclinedSms extends Notification implements ShouldQueue
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
        $reason = $this->application->decline_reason ?? 'did not meet criteria';
        
        return "Dear customer, your loan application {$appNum} was declined: {$reason}. For more info, contact support.";
    }
}
