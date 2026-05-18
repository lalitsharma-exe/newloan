<?php

namespace App\Services\Admin;

use App\Models\Investment;
use App\Models\InvestmentAccrual;
use App\Models\Investor;
use App\Models\TreasuryAccount;
use App\Models\TreasuryTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class InvestorService
{
    /**
     * Calculate investment parameters (Rate, Months, Accruals, Totals).
     */
    public function calculateInvestment($principal, $investmentDate, $investorType): array
    {
        $carbonDate = Carbon::parse($investmentDate);
        $investmentMonth = $carbonDate->month; // 1-12
        $investmentYear = $carbonDate->year;

        // Interest accrual starts at the end of the calendar month AFTER the investment month
        $interestStartDate = $carbonDate->copy()->addMonth()->endOfMonth();
        $maturityDate = Carbon::create($investmentYear, 12, 31);

        // Number of month-ends from start to Dec 31 inclusive
        $totalMonths = 12 - $investmentMonth;

        $rate = $investorType === 'MD' ? 0.05 : 0.015;

        if ($totalMonths <= 0) {
            return [
                'rate' => $rate,
                'total_months' => 0,
                'monthly_interest_cents' => 0,
                'total_interest_cents' => 0,
                'total_repayable_cents' => (int) round($principal * 100),
                'maturity_date' => $maturityDate->toDateString(),
                'interest_start_date' => null,
                'flag' => 'DEFER'
            ];
        }

        $principalCents = (int) round($principal * 100);
        $monthlyInterestCents = (int) round($principalCents * $rate);
        $totalInterestCents = $monthlyInterestCents * $totalMonths;
        $totalRepayableCents = $principalCents + $totalInterestCents;

        return [
            'rate' => $rate,
            'total_months' => $totalMonths,
            'monthly_interest_cents' => $monthlyInterestCents,
            'total_interest_cents' => $totalInterestCents,
            'total_repayable_cents' => $totalRepayableCents,
            'maturity_date' => $maturityDate->toDateString(),
            'interest_start_date' => $interestStartDate->toDateString(),
            'flag' => 'ACTIVE'
        ];
    }

    /**
     * Create a new investment and schedule its monthly accruals.
     */
    public function createInvestment(string $investorId, float $principal, string $investmentDate, ?int $accountId = null, ?int $adminId = null): Investment
    {
        return DB::transaction(function () use ($investorId, $principal, $investmentDate, $accountId, $adminId) {
            $investor = Investor::findOrFail($investorId);

            if ($investor->status !== 'active') {
                throw new Exception("Cannot create investment for an inactive investor.");
            }

            $calc = $this->calculateInvestment($principal, $investmentDate, $investor->investor_type);

            $carbonDate = Carbon::parse($investmentDate);
            $year = $carbonDate->year;
            $typeStr = $investor->investor_type === 'MD' ? 'MD' : 'PUB';

            // Generate unique contract reference: INV-{YEAR}-{TYPE}-{6-digit sequence}
            $count = Investment::whereYear('investment_date', $year)->count() + 1;
            $sequence = str_pad($count, 6, '0', STR_PAD_LEFT);
            $contractRef = "INV-{$year}-{$typeStr}-{$sequence}";

            $principalCents = (int) round($principal * 100);

            // Create Investment
            $investment = Investment::create([
                'investor_id' => $investor->id,
                'contract_ref' => $contractRef,
                'principal_cents' => $principalCents,
                'investment_date' => $carbonDate->toDateString(),
                'interest_rate' => $calc['rate'],
                'interest_start_date' => $calc['interest_start_date'] ?: $calc['maturity_date'],
                'maturity_date' => $calc['maturity_date'],
                'total_months' => $calc['total_months'],
                'monthly_interest_cents' => $calc['monthly_interest_cents'],
                'total_interest_cents' => $calc['total_interest_cents'],
                'total_repayable_cents' => $calc['total_repayable_cents'],
                'status' => 'active',
                'treasury_account_id' => $accountId,
            ]);

            // Create Accruals
            if ($calc['total_months'] > 0 && $calc['interest_start_date']) {
                $start = Carbon::parse($calc['interest_start_date']);
                for ($i = 1; $i <= $calc['total_months']; $i++) {
                    $accrualDate = $start->copy()->addMonths($i - 1)->endOfMonth();
                    InvestmentAccrual::create([
                        'investment_id' => $investment->id,
                        'accrual_date' => $accrualDate->toDateString(),
                        'interest_cents' => $calc['monthly_interest_cents'],
                        'status' => 'pending',
                    ]);
                }
            }

            // Sync Treasury Account Balance and ledger transactions
            if ($accountId) {
                $account = TreasuryAccount::findOrFail($accountId);
                $account->increment('balance', $principal);

                TreasuryTransaction::create([
                    'treasury_account_id' => $accountId,
                    'type' => 'INVESTMENT',
                    'direction' => 'in',
                    'amount' => $principal,
                    'reference' => $contractRef,
                    'description' => "Capital Injection: {$investor->full_name} ({$contractRef})",
                    'recorded_by' => $adminId,
                ]);
            }

            return $investment;
        });
    }

    /**
     * Preview early termination payout breakdown.
     */
    public function calculateEarlyTerminationPayout(Investment $investment, string $terminationDate): array
    {
        $carbonTermDate = Carbon::parse($terminationDate);

        // Standard 30-day notice check vs 31 Dec
        $maturityDate = Carbon::parse($investment->maturity_date);
        if ($carbonTermDate->diffInDays($maturityDate) <= 30) {
            throw new Exception("Notice period extends past the annual maturity date. Standard maturity process applies.");
        }

        // Count fully posted accrual months
        $postedAccruals = $investment->accruals()->where('status', 'posted')->get();
        $pendingAccruals = $investment->accruals()->where('status', 'pending')->get();

        $earnedCents = $postedAccruals->count() * $investment->monthly_interest_cents;
        $forfeitedCents = $pendingAccruals->count() * $investment->monthly_interest_cents;

        // Termination fee = 10% of forfeited interest
        $feeCents = (int) round($forfeitedCents * 0.10);

        $netPayoutCents = $investment->principal_cents + $earnedCents - $feeCents;

        return [
            'principal_cents' => $investment->principal_cents,
            'earned_interest_cents' => $earnedCents,
            'forfeited_interest_cents' => $forfeitedCents,
            'termination_fee_cents' => $feeCents,
            'net_payout_cents' => $netPayoutCents,
            'posted_months_count' => $postedAccruals->count(),
            'forfeited_months_count' => $pendingAccruals->count(),
        ];
    }

    /**
     * Submit an early termination request (initiating 30-day notice).
     */
    public function requestTermination(Investment $investment, string $requestDate): Investment
    {
        $carbonRequestDate = Carbon::parse($requestDate);
        $noticeExpiry = $carbonRequestDate->copy()->addDays(30);

        // Standard 30-day notice check vs 31 Dec
        $maturityDate = Carbon::parse($investment->maturity_date);
        if ($noticeExpiry->greaterThanOrEqualTo($maturityDate)) {
            throw new Exception("Early termination notice window overlaps with annual maturity date of 31 Dec.");
        }

        $investment->update([
            'status' => 'termination_pending',
            'termination_requested_at' => $carbonRequestDate->toDateTimeString(),
            'termination_notice_expiry' => $noticeExpiry->toDateString(),
        ]);

        return $investment;
    }

    /**
     * Approve early termination (liquidity check and payout).
     */
    public function approveTermination(Investment $investment, int $adminId): Investment
    {
        return DB::transaction(function () use ($investment, $adminId) {
            if ($investment->status !== 'termination_pending') {
                throw new Exception("Investment is not in termination_pending state.");
            }

            $today = now();
            $calc = $this->calculateEarlyTerminationPayout($investment, $today->toDateString());

            // Liquidity check
            $accountId = $investment->treasury_account_id;
            if ($accountId) {
                $account = TreasuryAccount::findOrFail($accountId);
                $netPayoutLsl = $calc['net_payout_cents'] / 100;
                
                if ($account->balance < $netPayoutLsl) {
                    throw new Exception("Insufficient liquidity in account '{$account->name}' to process LSL " . number_format($netPayoutLsl, 2) . " repayment.");
                }

                // Deduct final payout from balance
                $account->decrement('balance', $netPayoutLsl);

                TreasuryTransaction::create([
                    'treasury_account_id' => $accountId,
                    'type' => 'INVESTMENT_REPAYMENT',
                    'direction' => 'out',
                    'amount' => $netPayoutLsl,
                    'reference' => $investment->contract_ref,
                    'description' => "Early Termination Payout: {$investment->contract_ref}",
                    'recorded_by' => $adminId,
                ]);
            }

            // Update pending accruals to forfeited
            $investment->accruals()->where('status', 'pending')->update([
                'status' => 'forfeited'
            ]);

            // Finalize investment status
            $investment->update([
                'status' => 'terminated',
                'termination_approved_by' => $adminId,
                'termination_fee_cents' => $calc['termination_fee_cents'],
                'net_payout_cents' => $calc['net_payout_cents'],
                'terminated_at' => $today->toDateTimeString(),
            ]);

            return $investment;
        });
    }

    /**
     * Decline early termination (revert to active status).
     */
    public function declineTermination(Investment $investment): Investment
    {
        if ($investment->status !== 'termination_pending') {
            throw new Exception("Investment is not in termination_pending state.");
        }

        $investment->update([
            'status' => 'active',
            'termination_requested_at' => null,
            'termination_notice_expiry' => null,
        ]);

        return $investment;
    }

    /**
     * Process maturity repayment on 31 December.
     */
    public function processMaturityRepayment(Investment $investment, int $adminId): Investment
    {
        return DB::transaction(function () use ($investment, $adminId) {
            if ($investment->status !== 'active' && $investment->status !== 'matured') {
                throw new Exception("Only active or matured investments can be repaid at maturity.");
            }

            $totalRepayableLsl = $investment->total_repayable_cents / 100;

            // Liquidity Check
            $accountId = $investment->treasury_account_id;
            if ($accountId) {
                $account = TreasuryAccount::findOrFail($accountId);
                if ($account->balance < $totalRepayableLsl) {
                    throw new Exception("Insufficient liquidity in account '{$account->name}' to process maturity repayment of LSL " . number_format($totalRepayableLsl, 2));
                }

                $account->decrement('balance', $totalRepayableLsl);

                TreasuryTransaction::create([
                    'treasury_account_id' => $accountId,
                    'type' => 'INVESTMENT_REPAYMENT',
                    'direction' => 'out',
                    'amount' => $totalRepayableLsl,
                    'reference' => $investment->contract_ref,
                    'description' => "Maturity Repayment: {$investment->contract_ref}",
                    'recorded_by' => $adminId,
                ]);
            }

            $investment->update([
                'status' => 'repaid',
                'repaid_at' => now(),
            ]);

            return $investment;
        });
    }
}
