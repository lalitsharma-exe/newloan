<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanApplication, LoanInstallment, Payment, User};

class DashboardService
{
    public function getStats(): array
    {
        $totalPortfolio = Loan::whereIn('status',['active','overdue'])->sum('outstanding_balance');

        $par30Amount = Loan::whereIn('status',['active','overdue'])
            ->whereHas('installments', fn($q) => $q->where('status','overdue')
                ->whereDate('due_date','<=', now()->subDays(30)))
            ->sum('outstanding_balance');
        $par30Pct = $totalPortfolio > 0 ? round(($par30Amount / $totalPortfolio) * 100, 1) : 0;

        $totalPrincipal = Loan::sum('principal_amount');
        $defaultAmount  = Loan::whereIn('status',['defaulted','written_off'])->sum('outstanding_balance');
        $defaultRate    = $totalPrincipal > 0 ? round(($defaultAmount / $totalPrincipal) * 100, 1) : 0;

        return [
            'loans_approved_today'    => Loan::whereDate('created_at', today())->count(),
            'loans_disbursed_today'   => Loan::whereDate('disbursement_date', today())->count(),
            'disbursed_today_amount'  => Loan::whereDate('disbursement_date', today())->sum('principal_amount'),
            'payments_received_today' => Payment::whereDate('created_at', today())->where('status','verified')->sum('amount'),
            'applications_pending'    => LoanApplication::whereIn('status',['submitted','under_review','info_requested','on_hold'])->count(),
            'overdue_loans'           => Loan::where('status','overdue')->count(),
            'expected_collections'    => LoanInstallment::whereDate('due_date','>=', now())
                                            ->whereDate('due_date','<=', now()->endOfMonth())
                                            ->whereIn('status',['pending','partial','overdue'])
                                            ->sum('outstanding_amount'),
            'total_portfolio'         => $totalPortfolio,
            'total_disbursed_month'   => Loan::whereMonth('disbursement_date', now()->month)
                                            ->whereYear('disbursement_date', now()->year)
                                            ->sum('principal_amount'),
            'total_borrowers'         => User::where('role','borrower')->count(),
            'par30_pct'               => $par30Pct,
            'par30_amount'            => $par30Amount,
            'default_rate'            => $defaultRate,
        ];
    }

    public function getRecentApplications(int $limit = 10)
    {
        return LoanApplication::with(['user','loanProduct'])
            ->where('status','!=','draft')
            ->latest()->limit($limit)->get();
    }

    public function getOverdueLoans(int $limit = 5)
    {
        return Loan::with('user')
            ->where('status','overdue')
            ->orderByDesc('outstanding_balance')
            ->limit($limit)->get();
    }

    public function getRecentPayments(int $limit = 5)
    {
        return Payment::with(['loan.user'])->latest()->limit($limit)->get();
    }

    public function getMonthlyChartData(): array
    {
        return collect(range(5, 0))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'month'        => $d->format('M Y'),
                'disbursed'    => Loan::whereMonth('disbursement_date', $d->month)->whereYear('disbursement_date', $d->year)->sum('principal_amount'),
                'collected'    => Payment::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->where('status','verified')->sum('amount'),
                'applications' => LoanApplication::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->where('status','!=','draft')->count(),
            ];
        })->toArray();
    }
}
    