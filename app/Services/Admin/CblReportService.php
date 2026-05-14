<?php

namespace App\Services\Admin;

use App\Models\Loan;
use App\Models\User;
use App\Models\Complaint;
use App\Models\LoanRateSchedule;
use App\Models\CblReportArchive;
use App\Models\DpdSnapshot;
use App\Models\SmeSizeCategory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CblReportService
{
    public function generateReport($startDate, $endDate, $segment = 'ALL')
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // 1. Data Preparation - DPD Calculation for all active loans
        $activeLoans = $this->getDpdCalc($endDate, $segment);

        // 2. Section Calculations
        $report = [
            '3.1' => $this->calculatePortfolioQuality($activeLoans),
            '3.2' => $this->calculateRiskRatios($activeLoans),
            '3.3' => $this->calculateTopBorrowers($endDate),
            '3.4' => $this->calculateDemographics($activeLoans),
            '3.5' => $this->calculateSmeLoans($activeLoans),
            '3.6' => $this->calculateTenorDistribution($activeLoans),
            '3.7' => $this->calculateLoanActivity($startDate, $endDate, $segment),
            '3.8' => $this->calculateArrearsSummary($activeLoans),
            '3.9' => $this->calculateWriteOffs($startDate, $endDate),
            '3.10' => $this->calculateComplaints($startDate, $endDate),
            '3.11' => $this->calculateRiskIndicators($activeLoans),
            '3.12' => $this->calculatePricingFairness($activeLoans, $startDate, $endDate),
            '3.13' => $this->calculateClientGrowth($startDate, $endDate),
        ];

        // 3. Validation Gates
        $validations = $this->runValidations($report, $startDate, $endDate, $activeLoans);

        return [
            'metadata' => [
                'period' => $startDate->format('M Y'),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'segment' => $segment,
                'generated_at' => now()->toDateTimeString(),
            ],
            'data' => $report,
            'validations' => $validations,
            'ready_for_export' => collect($validations)->every(fn($v) => $v['status'] === 'PASS' || $v['type'] === 'WARN')
        ];
    }

    private function getDpdCalc($date, $segment)
    {
        $query = Loan::with('user')
            ->where('status', 'ACTIVE')
            ->where('disbursement_date', '<=', $date);

        if ($segment !== 'ALL') {
            $query->where('segment', $segment);
        }

        // Subquery for DPD
        // DPD = CURRENT_DATE - date_of_first_missed_instalment
        return $query->get()->map(function ($loan) use ($date) {
            $firstMissed = DB::table('loan_installments')
                ->where('loan_id', $loan->id)
                ->where('due_date', '<=', $date)
                ->whereIn('status', ['unpaid', 'overdue', 'partial'])
                ->orderBy('due_date', 'asc')
                ->first();

            $dpd = 0;
            if ($firstMissed) {
                $dpd = max(0, Carbon::parse($date)->diffInDays(Carbon::parse($firstMissed->due_date)));
            }

            $loan->dpd = $dpd;
            $loan->aging_bucket = $this->getAgingBucket($dpd);
            return $loan;
        });
    }

    private function getAgingBucket($dpd)
    {
        if ($dpd === 0) return 'Current';
        if ($dpd <= 30) return '1-30 days';
        if ($dpd <= 90) return '31-90 days';
        if ($dpd <= 180) return '91-180 days';
        if ($dpd <= 270) return '181-270 days';
        if ($dpd <= 365) return '271-365 days';
        return '365+ days';
    }

    private function calculatePortfolioQuality($loans)
    {
        $buckets = ['Current', '1-30 days', '31-90 days', '91-180 days', '181-270 days', '271-365 days', '365+ days'];
        $result = [];

        foreach ($buckets as $bucket) {
            $filtered = $loans->where('aging_bucket', $bucket);
            $result[] = [
                'bucket' => $bucket,
                'count' => $filtered->count(),
                'amount' => $filtered->sum('outstanding_balance'),
                'classification' => $this->getCblClassification($bucket),
                'provision_rate' => $this->getProvisionRate($bucket),
            ];
        }
        return $result;
    }

    private function getCblClassification($bucket)
    {
        return match ($bucket) {
            'Current' => 'Performing',
            '1-30 days' => 'Watch',
            '31-90 days' => 'Substandard',
            '91-180 days', '181-270 days' => 'Doubtful',
            default => 'Loss',
        };
    }

    private function getProvisionRate($bucket)
    {
        return match ($bucket) {
            'Current' => 1,
            '1-30 days' => 5,
            '31-90 days' => 25,
            '91-180 days' => 50,
            '181-270 days' => 75,
            default => 100,
        };
    }

    private function calculateRiskRatios($loans)
    {
        $totalActiveBalance = $loans->sum('outstanding_balance');
        $par30Balance = $loans->where('dpd', '>', 30)->sum('outstanding_balance');
        $nplBalance = $loans->where('dpd', '>', 90)->sum('outstanding_balance');

        return [
            'par_30' => $totalActiveBalance > 0 ? ($par30Balance / $totalActiveBalance) * 100 : 0,
            'npl_ratio' => $totalActiveBalance > 0 ? ($nplBalance / $totalActiveBalance) * 100 : 0,
        ];
    }

    private function calculateTopBorrowers($date)
    {
        return Loan::with('user')
            ->where('status', 'ACTIVE')
            ->orderBy('outstanding_balance', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($l) {
                // Approximate calculations for display
                // In production, we'd sum from installments
                return [
                    'name' => $l->user->name,
                    'principal_amount' => $l->principal_amount,
                    'outstanding_balance' => $l->outstanding_balance,
                    'monthly_installment' => $l->monthly_installment,
                ];
            });
    }

    private function calculateDemographics($loans)
    {
        $totalCount = $loans->unique('user_id')->count();
        return $loans->groupBy('user.gender')->map(function ($group, $gender) use ($totalCount) {
            return [
                'gender' => $gender ?: 'Unknown',
                'count' => $group->unique('user_id')->count(),
                'amount' => $group->sum('outstanding_balance'),
                'percentage' => $totalCount > 0 ? ($group->unique('user_id')->count() / $totalCount) * 100 : 0
            ];
        })->values();
    }

    private function calculateSmeLoans($loans)
    {
        $categories = SmeSizeCategory::all();
        if ($categories->isEmpty()) {
            // Seed defaults if empty
            $categories = collect([
                (object)['category_name' => 'Micro', 'min_employees' => 1, 'max_employees' => 2],
                (object)['category_name' => 'Small', 'min_employees' => 3, 'max_employees' => 10],
                (object)['category_name' => 'Medium', 'min_employees' => 11, 'max_employees' => 50],
                (object)['category_name' => 'Large', 'min_employees' => 51, 'max_employees' => 9999],
            ]);
        }

        $smeLoans = $loans->where('segment', 'SME');
        return $categories->map(function ($cat) use ($smeLoans) {
            $filtered = $smeLoans->filter(fn($l) => ($l->business_employee_count ?? 1) >= $cat->min_employees && ($l->business_employee_count ?? 1) <= $cat->max_employees);
            return [
                'category' => $cat->category_name,
                'count' => $filtered->count(),
                'amount' => $filtered->sum('outstanding_balance')
            ];
        });
    }

    private function calculateTenorDistribution($loans)
    {
        return [
            'Short-term (1-3 months)' => $loans->whereBetween('term_months', [1, 3]),
            'Medium-term (4-6 months)' => $loans->whereBetween('term_months', [4, 6]),
            'Long-term (7-12 months)' => $loans->whereBetween('term_months', [7, 12]),
            'Extended-term (1-3 years)' => $loans->whereBetween('term_months', [13, 36]),
        ];
    }

    private function calculateLoanActivity($start, $end, $segment)
    {
        $newLoans = Loan::whereBetween('disbursement_date', [$start, $end]);
        $settled = Loan::whereBetween('closed_at', [$start, $end])->where('closed_reason', 'SETTLED');
        $writeOffs = Loan::whereBetween('closed_at', [$start, $end])->where('closed_reason', 'WRITTEN_OFF');

        if ($segment !== 'ALL') {
            $newLoans->where('segment', $segment);
            $settled->where('segment', $segment);
            $writeOffs->where('segment', $segment);
        }

        return [
            'new' => ['count' => $newLoans->count(), 'amount' => $newLoans->sum('principal_amount')],
            'settled' => ['count' => $settled->count(), 'amount' => $settled->sum('principal_amount')],
            'write_offs' => ['count' => $writeOffs->count(), 'amount' => $writeOffs->sum('outstanding_balance')],
        ];
    }

    private function calculateArrearsSummary($loans)
    {
        return [
            'Current' => ['count' => $loans->where('dpd', 0)->count(), 'amount' => $loans->where('dpd', 0)->sum('outstanding_balance')],
            '1-30 days' => ['count' => $loans->whereBetween('dpd', [1, 30])->count(), 'amount' => $loans->whereBetween('dpd', [1, 30])->sum('outstanding_balance')],
            '31-90 days' => ['count' => $loans->whereBetween('dpd', [31, 90])->count(), 'amount' => $loans->whereBetween('dpd', [31, 90])->sum('outstanding_balance')],
            '90+ days' => ['count' => $loans->where('dpd', '>', 90)->count(), 'amount' => $loans->where('dpd', '>', 90)->sum('outstanding_balance')],
        ];
    }

    private function calculateWriteOffs($start, $end)
    {
        // Placeholder for real write-off table
        return [
            'count' => 0,
            'amount' => 0
        ];
    }

    private function calculateComplaints($start, $end)
    {
        return [
            'internal' => Complaint::whereBetween('complaint_date', [$start, $end])->where('status', 'RESOLVED_INTERNALLY')->count(),
            'referred' => Complaint::whereBetween('complaint_date', [$start, $end])->where('status', 'REFERRED_TO_CBL')->count(),
        ];
    }

    private function calculateRiskIndicators($loans)
    {
        $officerCount = User::where('role', 'loan_officer')->where('is_active', true)->count();
        return [
            'loans_per_officer' => $officerCount > 0 ? $loans->count() / $officerCount : 0,
            'max_dti' => $loans->max(fn($l) => $l->borrower_monthly_income > 0 ? ($l->borrower_total_obligations / $l->borrower_monthly_income) * 100 : 0),
            'avg_dti' => $loans->avg(fn($l) => $l->borrower_monthly_income > 0 ? ($l->borrower_total_obligations / $l->borrower_monthly_income) * 100 : 0),
        ];
    }

    private function calculatePricingFairness($loans, $start, $end)
    {
        return [
            'max_rate' => $loans->max('interest_rate'),
            'unapproved_count' => 0, // Logic to cross-check with LoanRateSchedule
        ];
    }

    private function calculateClientGrowth($start, $end)
    {
        $newClients = User::whereBetween('first_loan_date', [$start, $end])->count();
        $totalActive = Loan::where('status', 'ACTIVE')->distinct('user_id')->count();

        return [
            'new_clients' => $newClients,
            'total_active' => $totalActive,
            'repeat_clients' => max(0, $totalActive - $newClients),
        ];
    }

    private function runValidations($report, $start, $end, $loans)
    {
        $validations = [];

        // V-01: Total loans match
        $systemCount = Loan::where('status', 'ACTIVE')->where('disbursement_date', '<=', $end)->count();
        $validations['V-01'] = [
            'label' => 'Total loan count reconciliation',
            'status' => $loans->count() === $systemCount ? 'PASS' : 'FAIL',
            'type' => 'CRITICAL',
            'detail' => "Report count: {$loans->count()}, System count: $systemCount"
        ];

        // V-02: Aging buckets sum to book
        $totalBook = $loans->sum('outstanding_balance');
        $bucketSum = collect($report['3.1'])->sum('amount');
        $validations['V-02'] = [
            'label' => 'Aging buckets reconciliation',
            'status' => abs($totalBook - $bucketSum) < 0.01 ? 'PASS' : 'FAIL',
            'type' => 'CRITICAL',
            'detail' => "Book: $totalBook, Buckets: $bucketSum"
        ];

        // V-03: Section 3.1 = 3.8
        $arrearsSum = collect($report['3.8'])->sum('amount');
        $validations['V-03'] = [
            'label' => 'Portfolio vs Arrears consistency',
            'status' => abs($bucketSum - $arrearsSum) < 0.01 ? 'PASS' : 'FAIL',
            'type' => 'CRITICAL',
        ];

        // V-05: No null gender
        $nullGenderCount = User::whereHas('loans', fn($q) => $q->where('status', 'ACTIVE'))->whereNull('gender')->count();
        $validations['V-05'] = [
            'label' => 'Borrower demographic integrity',
            'status' => $nullGenderCount === 0 ? 'PASS' : 'FAIL',
            'type' => 'WARN',
            'detail' => "$nullGenderCount borrowers missing gender"
        ];

        return $validations;
    }
}
