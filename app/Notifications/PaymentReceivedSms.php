<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentReceivedSms extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via($notifiable): array
    {
        return ['database']; // Add 'sms' if you have an SMS channel configured
    }

    public function toArray($notifiable): array
    {
        $loan = $this->payment->loan;
        $statusText = $loan->outstanding_balance <= 0 ? "FULLY PAID" : "PARTIAL PAYMENT";
        
        return [
            'message' => "Receipt: {$this->payment->payment_reference}. Payment of M" . number_format($this->payment->amount, 2) . " received for loan {$loan->loan_number}. {$statusText}. Balance: M" . number_format($loan->outstanding_balance, 2) . ". Thank you.",
            'payment_id' => $this->payment->id,
            'type' => 'payment_received'
        ];
    }
}
