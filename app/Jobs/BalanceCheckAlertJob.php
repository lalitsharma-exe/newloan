<?php

namespace App\Jobs;

use App\Models\FsEquityMovement;
use App\Models\FsPpeRegister;
use App\Models\Investment;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\TreasuryAccount;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BalanceCheckAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Calculate Total Assets
        $cash = (double) TreasuryAccount::where('is_active', true)->sum('balance');
        $ppe = (double) FsPpeRegister::where('status', 'active')->sum('net_book_value');
        $loans = (double) Loan::whereIn('status', ['active', 'overdue', 'arrears'])->sum('outstanding_balance');
        $assets = $cash + $ppe + $loans;

        // 2. Calculate Total Liabilities
        $investments = (double) Investment::where('status', 'active')->sum('principal_cents') / 100;
        $liabilities = $investments;

        // 3. Calculate Total Equity
        $equity = (double) FsEquityMovement::sum('amount');

        // 4. Validate Balance sheet equation
        $mismatch = abs($assets - ($liabilities + $equity));

        if ($mismatch > 1.00) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::send(
                    $admin->id,
                    'balance_sheet_mismatch',
                    '⚠️ Balance Sheet Mismatch Alert',
                    'Daily check failed: Assets (' . number_format($assets, 2) . ') do not reconcile with Liabilities + Equity (' . number_format($liabilities + $equity, 2) . '). Mismatch is LSL ' . number_format($mismatch, 2),
                    '/admin/financial/reports',
                    'exclamation-triangle'
                );
            }
        }
    }
}
