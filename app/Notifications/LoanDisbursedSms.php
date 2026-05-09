<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LoanDisbursedSms extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Loan $loan)
    {
    }

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): string
    {
        $loanNum = $this->loan->loan_number;
        $amount  = number_format($this->loan->principal_amount, 2);
        $method  = str_replace('_', ' ', $this->loan->payout_method);
        
        return "Your loan {$loanNum} of M{$amount} has been DISBURSED via {$method}. Please check your account. Thank you for choosing MyLoan.";
    }
}
