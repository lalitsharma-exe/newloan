<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanApplication, LoanInstallment, Payment, User};

class DashboardService
{
    public function getStats(): array
    {
        return [
            'loans_approved_today'    => Loan::whereDate('created_at', today())->count(),
            'payments_received_today' => Payment::whereDate('created_at', today())
                                            ->where('status', 'verified')->sum('amount'),
            'applications_pending'    => LoanApplication::whereIn('status', [
                                            'submitted', 'under_review', 'info_requested', 'on_hold'
                                        ])->count(),
            'overdue_loans'           => Loan::where('status', 'overdue')->count(),
            'expected_collections'    => LoanInstallment::whereDate('due_date', '>=', now())
                                            ->whereDate('due_date', '<=', now()->endOfMonth())
                                            ->whereIn('status', ['pending', 'partial', 'overdue'])
                                            ->sum('outstanding_amount'),
            'total_portfolio'         => Loan::where('status', 'active')->sum('outstanding_balance'),
            'total_disbursed_month'   => Loan::whereMonth('disbursement_date', now()->month)
                                            ->whereYear('disbursement_date', now()->year)
                                            ->sum('principal_amount'),
            'total_borrowers'         => User::where('role', 'borrower')->count(),
        ];
    }

    public function getRecentApplications(int $limit = 10)
    {
        return LoanApplication::with(['user', 'loanProduct'])
            ->where('status', '!=', 'draft')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getOverdueLoans(int $limit = 5)
    {
        return Loan::with('user')
            ->where('status', 'overdue')
            ->orderByDesc('outstanding_balance')
            ->limit($limit)
            ->get();
    }

    public function getRecentPayments(int $limit = 5)
    {
        return Payment::with(['loan.user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getMonthlyChartData(): array
    {
        return collect(range(5, 0))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'month'        => $d->format('M Y'),
                'disbursed'    => Loan::whereMonth('disbursement_date', $d->month)
                                      ->whereYear('disbursement_date', $d->year)
                                      ->sum('principal_amount'),
                'collected'    => Payment::whereMonth('created_at', $d->month)
                                         ->whereYear('created_at', $d->year)
                                         ->where('status', 'verified')
                                         ->sum('amount'),
                'applications' => LoanApplication::whereMonth('created_at', $d->month)
                                                  ->whereYear('created_at', $d->year)
                                                  ->where('status', '!=', 'draft')
                                                  ->count(),
            ];
        })->toArray();
    }
}