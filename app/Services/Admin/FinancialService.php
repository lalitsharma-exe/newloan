<?php

namespace App\Services\Admin;

use App\Models\{TreasuryAccount, TreasuryTransaction, OperatingExpense, RepaymentForecast, Loan, LoanInstallment, Payment};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Get real-time liquidity stats for the dashboard.
     */
    public function getLiquidityStats()
    {
        $cashAvailable = TreasuryAccount::where('is_active', true)->sum('balance');
        
        $expectedInflows = RepaymentForecast::whereBetween('forecast_date', [now(), now()->addDays(30)])
            ->sum(DB::raw('expected_amount * probability'));

        $pendingExpenses = OperatingExpense::where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->sum('amount');
            
        // For outflows, we also consider scheduled disbursements if any (pending approved applications)
        $scheduledDisbursements = Loan::where('status', 'approved')
            ->sum('principal_amount');

        $expectedOutflows = $pendingExpenses + $scheduledDisbursements;
        
        $netLiquidity = $cashAvailable + $expectedInflows - $expectedOutflows;
        
        // Calculate Burn Rate (avg daily expense over last 30 days)
        $last30DaysExpense = TreasuryTransaction::where('type', 'expense')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('amount');
        
        $dailyBurnRate = $last30DaysExpense / 30;
        
        $runwayDays = $dailyBurnRate > 0 ? (int) ($cashAvailable / $dailyBurnRate) : null;

        return [
            'cash_available' => $cashAvailable,
            'expected_inflows' => $expectedInflows,
            'expected_outflows' => $expectedOutflows,
            'net_liquidity' => $netLiquidity,
            'runway_days' => $runwayDays,
            'daily_burn_rate' => $dailyBurnRate,
            'liquidity_status' => $this->getLiquidityStatus($netLiquidity)
        ];
    }

    private function getLiquidityStatus($netLiquidity)
    {
        if ($netLiquidity > 50000) return 'healthy'; // Configurable thresholds
        if ($netLiquidity > 0) return 'warning';
        return 'critical';
    }

    /**
     * Record a cash movement in the treasury ledger.
     */
    public function recordTransaction($accountId, $type, $amount, $direction, $data = [])
    {
        return DB::transaction(function () use ($accountId, $type, $amount, $direction, $data) {
            $account = TreasuryAccount::findOrFail($accountId);
            
            // Create transaction
            $transaction = TreasuryTransaction::create([
                'treasury_account_id' => $accountId,
                'type' => $type,
                'amount' => $amount,
                'direction' => $direction,
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'payment_id' => $data['payment_id'] ?? null,
                'loan_id' => $data['loan_id'] ?? null,
                'expense_id' => $data['expense_id'] ?? null,
                'recorded_by' => auth()->id()
            ]);

            // Update account balance
            if ($direction === 'in') {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }

            return $transaction;
        });
    }

    /**
     * Refresh probability-weighted repayment forecasts.
     * Logic: Borrowers with high PAR or low risk score have lower probability.
     */
    public function refreshForecasts()
    {
        RepaymentForecast::truncate();

        $activeInstallments = LoanInstallment::whereIn('status', ['pending', 'overdue', 'partial'])
            ->whereBetween('due_date', [now(), now()->addDays(90)]) // Forecast 90 days out
            ->with('loan.user')
            ->get();

        foreach ($activeInstallments as $inst) {
            $probability = 0.95; // Default

            // Simple logic for PAR/Overdue
            if ($inst->status === 'overdue') {
                $days = now()->diffInDays($inst->due_date);
                $probability = max(0.1, 0.8 - ($days * 0.05));
            }

            // Could also integrate risk score from User model here
            
            RepaymentForecast::create([
                'loan_id' => $inst->loan_id,
                'user_id' => $inst->loan->user_id,
                'expected_amount' => $inst->outstanding_amount,
                'probability' => $probability,
                'forecast_date' => $inst->due_date
            ]);
        }
    }
}
