<?php

namespace App\Console\Commands;

use App\Models\{Loan, LoanInstallment};
use Illuminate\Console\Command;

class ApplyLoanPenalties extends Command
{
    protected $signature   = 'loans:apply-penalties';
    protected $description = 'Apply M20 penalty per 10 days overdue to all overdue installments';

    public function handle(): void
    {
        $today   = now();
        $applied = 0;

        // Find all overdue installments
        $overdue = LoanInstallment::where('status', 'overdue')
            ->where('due_date', '<', $today)
            ->with('loan')
            ->get();

        foreach ($overdue as $inst) {
            $daysOverdue = (int) $today->diffInDays($inst->due_date);
            // M20 for every complete 10-day period overdue
            $penalty = (int) floor($daysOverdue / 10) * 20;

            if ($penalty > 0 && (float) $inst->late_fee !== (float) $penalty) {
                $diff = $penalty - (float) $inst->late_fee;
                $inst->update([
                    'late_fee'           => $penalty,
                    'total_amount'       => round((float) $inst->total_amount + $diff, 2),
                    'outstanding_amount' => round((float) $inst->outstanding_amount + $diff, 2),
                ]);
                // Update loan balance too
                if ($inst->loan && $diff > 0) {
                    $inst->loan->increment('outstanding_balance', $diff);
                }
                $applied++;
            }
        }

        // Also mark any pending installments that are now past due as overdue
        $nowOverdue = LoanInstallment::where('status', 'pending')
            ->where('due_date', '<', $today->toDateString())
            ->get();

        foreach ($nowOverdue as $inst) {
            $inst->update(['status' => 'overdue']);
            if ($inst->loan && !in_array($inst->loan->status, ['paid_off','closed','defaulted'])) {
                $inst->loan->update(['status' => 'overdue']);
            }
        }

        $this->info("✓ Penalties applied to {$applied} installments.");
        $this->info("✓ {$nowOverdue->count()} installments marked overdue.");
    }
}
