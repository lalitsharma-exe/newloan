<?php

namespace App\Console\Commands;

use App\Models\LoanInstallment;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS reminders for upcoming loan installments (2 days before due date)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendTwoDayReminders();
        $this->sendPaydayMessages();
    }

    protected function sendTwoDayReminders()
    {
        $targetDate = now()->addDays(2)->toDateString();

        /** @var \App\Models\LoanInstallment[] $installments */
        $installments = LoanInstallment::where('status', '!=', 'paid')
            ->where('status', '!=', 'waived')
            ->whereDate('due_date', $targetDate)
            ->whereNull('last_reminder_sent_at') 
            ->with(['loan.user'])
            ->get();

        $count = 0;
        foreach ($installments as $inst) {
            $user = $inst->loan->user;
            if (!$user || !$user->phone) continue;

            try {
                $user->notify(new \App\Notifications\PaymentReminderSms($inst));
                $inst->update(['last_reminder_sent_at' => now()]);
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed to send reminder for {$user->name}: " . $e->getMessage());
            }
        }
        $this->info("✓ Sent {$count} reminders for {$targetDate}.");
    }

    protected function sendPaydayMessages()
    {
        $today = now()->toDateString();

        /** @var \App\Models\LoanInstallment[] $installments */
        $installments = LoanInstallment::where('status', '!=', 'paid')
            ->where('status', '!=', 'waived')
            ->whereDate('due_date', $today)
            ->whereNull('payday_notified_at') 
            ->with(['loan.user'])
            ->get();

        $count = 0;
        foreach ($installments as $inst) {
            $user = $inst->loan->user;
            if (!$user || !$user->phone) continue;

            try {
                $user->notify(new \App\Notifications\PaydaySms($inst));
                $inst->update(['payday_notified_at' => now()]);
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed to send payday message for {$user->name}: " . $e->getMessage());
            }
        }
        $this->info("✓ Sent {$count} payday messages for today.");
    }
}
