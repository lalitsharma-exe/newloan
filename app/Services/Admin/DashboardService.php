<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanApplication, LoanInstallment, Payment, Referral, User};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // =========================================================================
    //  CORE STATS (used by AJAX refresh & initial load)
    // =========================================================================
    public function getStats(): array
    {
        $now = now();
        $totalPortfolio = Loan::whereIn('status', ['active', 'overdue'])->sum('outstanding_balance');
        $totalPrincipal = Loan::sum('principal_amount');

        // PAR buckets
        $par1Amount  = $this->parAmount(1);
        $par7Amount  = $this->parAmount(7);
        $par30Amount = $this->parAmount(30);
        $par1Pct     = $totalPortfolio > 0 ? round($par1Amount / $totalPortfolio * 100, 1) : 0;
        $par7Pct     = $totalPortfolio > 0 ? round($par7Amount / $totalPortfolio * 100, 1) : 0;
        $par30Pct    = $totalPortfolio > 0 ? round($par30Amount / $totalPortfolio * 100, 1) : 0;

        // Default & write-offs
        $defaultAmount = Loan::whereIn('status', ['defaulted', 'written_off'])->sum('outstanding_balance');
        $defaultRate   = $totalPrincipal > 0 ? round($defaultAmount / $totalPrincipal * 100, 1) : 0;
        $writtenOffAmt = Loan::where('status', 'written_off')->sum('outstanding_balance');

        // Collections (current month)
        $monthExpected  = LoanInstallment::whereMonth('due_date', $now->month)
            ->whereYear('due_date', $now->year)
            ->sum('total_amount');
        $monthCollected = Payment::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where('status', 'verified')
            ->sum('amount');
        $collectionPct = $monthExpected > 0 ? round($monthCollected / $monthExpected * 100, 1) : 0;

        // Overdue aging buckets
        $overdueTotal = LoanInstallment::where('status', 'overdue')->sum('outstanding_amount');
        $overdue1_7   = LoanInstallment::where('status', 'overdue')
            ->whereDate('due_date', '>=', $now->copy()->subDays(7))
            ->sum('outstanding_amount');
        $overdue8_30  = LoanInstallment::where('status', 'overdue')
            ->whereDate('due_date', '<', $now->copy()->subDays(7))
            ->whereDate('due_date', '>=', $now->copy()->subDays(30))
            ->sum('outstanding_amount');
        $overdue31_60 = LoanInstallment::where('status', 'overdue')
            ->whereDate('due_date', '<', $now->copy()->subDays(30))
            ->whereDate('due_date', '>=', $now->copy()->subDays(60))
            ->sum('outstanding_amount');
        $overdue60p   = LoanInstallment::where('status', 'overdue')
            ->whereDate('due_date', '<', $now->copy()->subDays(60))
            ->sum('outstanding_amount');

        // Liquidity
        $totalCashIn   = Payment::where('status', 'verified')->sum('amount');
        $totalDisbursed = Loan::sum('principal_amount');
        $cashAvailable = $totalCashIn - $totalDisbursed; // simplified approximation

        // Applications funnel
        $appsSubmitted = LoanApplication::where('status', '!=', 'draft')->count();
        $appsApproved  = LoanApplication::whereIn('status', ['approved', 'disbursed'])->count();
        $appsDeclined  = LoanApplication::where('status', 'declined')->count();
        $appsPending   = LoanApplication::whereIn('status', ['submitted', 'under_review', 'info_requested', 'on_hold'])->count();
        $approvalRate  = $appsSubmitted > 0 ? round($appsApproved / $appsSubmitted * 100, 1) : 0;

        // Loan portfolio
        $activeLoans   = Loan::where('status', 'active')->count();
        $overdueLoans  = Loan::where('status', 'overdue')->count();

        // Revenue (estimated)
        $totalInterestRevenue = Loan::sum(DB::raw('total_amount - principal_amount'));
        $totalFeeRevenue      = Loan::sum('processing_fee');
        $totalRevenue         = $totalInterestRevenue + $totalFeeRevenue;

        // Disbursements this month
        $disbursedMonth = Loan::whereMonth('disbursement_date', $now->month)
            ->whereYear('disbursement_date', $now->year)
            ->sum('principal_amount');

        // Total borrowers
        $totalBorrowers   = User::where('role', 'borrower')->count();
        $activeBorrowers  = Loan::whereIn('status', ['active', 'overdue'])->distinct('user_id')->count('user_id');

        // Referrals
        $totalReferrals    = Referral::count();
        $qualifiedReferrals = Referral::whereIn('status', ['qualified', 'paid'])->count();
        $paidReferrals     = Referral::where('status', 'paid')->count();
        $pendingPayouts    = Referral::where('status', 'qualified')->sum('amount');
        $totalPaidOut      = Referral::where('status', 'paid')->sum('amount');

        // Unit economics
        $avgLoanSize = Loan::avg('principal_amount') ?? 0;
        $profitPerLoan = $totalPrincipal > 0 && Loan::count() > 0
            ? round(($totalRevenue - $writtenOffAmt) / Loan::count(), 2)
            : 0;

        return [
            // Growth & Operations
            'apps_submitted'    => $appsSubmitted,
            'apps_approved'     => $appsApproved,
            'apps_declined'     => $appsDeclined,
            'apps_pending'      => $appsPending,
            'approval_rate'     => $approvalRate,
            'active_loans'      => $activeLoans,
            'overdue_loans'     => $overdueLoans,
            'total_borrowers'   => $totalBorrowers,
            'active_borrowers'  => $activeBorrowers,
            'disbursed_month'   => $disbursedMonth,

            // Portfolio & Risk
            'total_portfolio'   => $totalPortfolio,
            'total_principal'   => $totalPrincipal,
            'par1_pct'          => $par1Pct,
            'par7_pct'          => $par7Pct,
            'par30_pct'         => $par30Pct,
            'par1_amount'       => $par1Amount,
            'par7_amount'       => $par7Amount,
            'par30_amount'      => $par30Amount,
            'default_rate'      => $defaultRate,
            'default_amount'    => $defaultAmount,
            'written_off_amount' => $writtenOffAmt,

            // Collections
            'month_expected'    => $monthExpected,
            'month_collected'   => $monthCollected,
            'collection_pct'    => $collectionPct,
            'overdue_total'     => $overdueTotal,
            'overdue_1_7'       => $overdue1_7,
            'overdue_8_30'      => $overdue8_30,
            'overdue_31_60'     => $overdue31_60,
            'overdue_60p'       => $overdue60p,

            // Profitability
            'total_revenue'     => $totalRevenue,
            'total_interest_revenue' => $totalInterestRevenue,
            'total_fee_revenue' => $totalFeeRevenue,
            'profit_per_loan'   => $profitPerLoan,

            // Liquidity
            'cash_available'    => max(0, $cashAvailable),
            'total_disbursed'   => $totalDisbursed,
            'total_collected'   => $totalCashIn,

            // Referrals
            'total_referrals'   => $totalReferrals,
            'qualified_referrals' => $qualifiedReferrals,
            'paid_referrals'    => $paidReferrals,
            'pending_payouts'   => $pendingPayouts,
            'total_paid_out'    => $totalPaidOut,

            // Unit Economics
            'avg_loan_size'     => round($avgLoanSize, 2),

            // Loans today
            'loans_approved_today'   => Loan::whereDate('created_at', today())->count(),
            'loans_disbursed_today'  => Loan::whereDate('disbursement_date', today())->count(),
            'disbursed_today_amount' => Loan::whereDate('disbursement_date', today())->sum('principal_amount'),
            'payments_received_today' => Payment::whereDate('created_at', today())->where('status', 'verified')->sum('amount'),
        ];
    }

    // =========================================================================
    //  PAR HELPER
    // =========================================================================
    private function parAmount(int $days): float
    {
        return (float) Loan::whereIn('status', ['active', 'overdue'])
            ->whereHas('installments', fn($q) => $q
                ->where('status', 'overdue')
                ->whereDate('due_date', '<=', now()->subDays($days))
            )
            ->sum('outstanding_balance');
    }

    // =========================================================================
    //  RECENT APPLICATIONS
    // =========================================================================
    public function getRecentApplications(int $limit = 10)
    {
        return \App\Models\LoanApplication::with(['user', 'loanProduct'])
            ->where('status', '!=', 'draft')
            ->latest()
            ->limit($limit)
            ->get();
    }

    // =========================================================================
    //  OVERDUE LOANS
    // =========================================================================
    public function getOverdueLoans(int $limit = 5)
    {
        return Loan::with('user')
            ->where('status', 'overdue')
            ->orderByDesc('outstanding_balance')
            ->limit($limit)
            ->get();
    }

    // =========================================================================
    //  RECENT PAYMENTS
    // =========================================================================
    public function getRecentPayments(int $limit = 5)
    {
        return Payment::with(['loan.user'])->latest()->limit($limit)->get();
    }

    // =========================================================================
    //  PERIOD-AWARE CHART DATA  (supports 'month', 'quarter', 'year' periods)
    // =========================================================================
    public function getMonthlyChartData(string $period = 'month'): array
    {
        $points = match ($period) {
            'quarter' => collect(range(2, 0))->map(fn($i) => now()->subMonths($i)),
            'year'    => collect(range(11, 0))->map(fn($i) => now()->subMonths($i)),
            default   => collect(range(5, 0))->map(fn($i) => now()->subMonths($i)),
        };

        return $points->map(function (Carbon $d) {
            return [
                'month'        => $d->format('M Y'),
                'disbursed'    => (float) Loan::whereMonth('disbursement_date', $d->month)->whereYear('disbursement_date', $d->year)->sum('principal_amount'),
                'collected'    => (float) Payment::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->where('status', 'verified')->sum('amount'),
                'applications' => (int) \App\Models\LoanApplication::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->where('status', '!=', 'draft')->count(),
                'approved'     => (int) \App\Models\LoanApplication::whereMonth('decided_at', $d->month)->whereYear('decided_at', $d->year)->whereIn('status', ['approved', 'disbursed'])->count(),
                'expected'     => (float) LoanInstallment::whereMonth('due_date', $d->month)->whereYear('due_date', $d->year)->sum('total_amount'),
            ];
        })->toArray();
    }

    // =========================================================================
    //  SEGMENT BREAKDOWN (employer_type → government / ngo / self_employed / other)
    // =========================================================================
    public function getSegmentBreakdown(): array
    {
        return DB::table('loans')
            ->join('loan_applications', 'loans.application_id', '=', 'loan_applications.id')
            ->join('employments', 'loan_applications.id', '=', 'employments.application_id')
            ->select('employments.employer_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(loans.outstanding_balance) as portfolio'))
            ->whereIn('loans.status', ['active', 'overdue'])
            ->groupBy('employments.employer_type')
            ->get()
            ->toArray();
    }

    // =========================================================================
    //  REFERRAL LEADERBOARD
    // =========================================================================
    public function getTopReferrers(int $limit = 5): array
    {
        return DB::table('referrals')
            ->join('users', 'referrals.referrer_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as total_referrals'),
                DB::raw('SUM(CASE WHEN referrals.status IN (\'qualified\',\'paid\') THEN 1 ELSE 0 END) as qualified'),
                DB::raw('SUM(referrals.amount) as total_earned')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('qualified')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // =========================================================================
    //  LOAN STATUS BREAKDOWN
    // =========================================================================
    public function getLoanStatusBreakdown(): array
    {
        return [
            'Active'     => Loan::where('status', 'active')->count(),
            'Overdue'    => Loan::where('status', 'overdue')->count(),
            'Closed'     => Loan::where('status', 'closed')->count(),
            'Written Off' => Loan::where('status', 'written_off')->count(),
        ];
    }

    // =========================================================================
    //  PERIOD-AWARE: Compare current vs previous period stats
    // =========================================================================
    public function getPeriodStats(string $period = 'month'): array
    {
        [$curStart, $curEnd, $prevStart, $prevEnd] = $this->getPeriodRange($period);

        $cur  = $this->statsForRange($curStart, $curEnd);
        $prev = $this->statsForRange($prevStart, $prevEnd);

        return [
            'period'   => $period,
            'current'  => $cur,
            'previous' => $prev,
            'changes'  => $this->computeChanges($cur, $prev),
        ];
    }

    private function getPeriodRange(string $period): array
    {
        $now = now();
        return match ($period) {
            'quarter' => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter(),
                $now->copy()->subQuarter()->startOfQuarter(),
                $now->copy()->subQuarter()->endOfQuarter(),
            ],
            'year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ],
            default => [ // month
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
        };
    }

    private function statsForRange(Carbon $start, Carbon $end): array
    {
        return [
            'disbursed'    => (float) Loan::whereBetween('disbursement_date', [$start, $end])->sum('principal_amount'),
            'collected'    => (float) Payment::whereBetween('created_at', [$start, $end])->where('status', 'verified')->sum('amount'),
            'applications' => (int) \App\Models\LoanApplication::whereBetween('created_at', [$start, $end])->where('status', '!=', 'draft')->count(),
            'approved'     => (int) \App\Models\LoanApplication::whereBetween('decided_at', [$start, $end])->whereIn('status', ['approved', 'disbursed'])->count(),
            'referrals'    => (int) Referral::whereBetween('created_at', [$start, $end])->count(),
        ];
    }

    private function computeChanges(array $cur, array $prev): array
    {
        $changes = [];
        foreach ($cur as $k => $v) {
            $p = $prev[$k] ?? 0;
            $changes[$k] = $p > 0 ? round(($v - $p) / $p * 100, 1) : ($v > 0 ? 100 : 0);
        }
        return $changes;
    }
}