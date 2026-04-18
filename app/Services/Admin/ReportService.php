<?php
namespace App\Services\Admin;
use App\Models\{Loan, LoanApplication, LoanInstallment, Payment, LoanProduct, User};
use Illuminate\Support\Facades\DB;

class ReportService {

    // ── 1. PORTFOLIO ──────────────────────────────────────────────────────────
    public function getPortfolioReport(array $f): array {
        $active   = Loan::where('loans.status','active');
        $allLoans = Loan::with(['user','loanProduct']);
        if (!empty($f['product'])) { $active->where('loan_product_id',$f['product']); $allLoans->where('loan_product_id',$f['product']); }

        if (!empty($f['category'])) {
            $active->join('employments', 'loans.application_id', '=', 'employments.application_id')
                ->where('employments.employer_category', $f['category']);
            $allLoans->join('employments', 'loans.application_id', '=', 'employments.application_id')
                ->where('employments.employer_category', $f['category']);
        }

        $activeLoans    = $active->get();
        $totalPortfolio = $activeLoans->sum('outstanding_balance');
        $totalPrincipal = $activeLoans->sum('principal_amount');
        $avgLoanSize    = $activeLoans->avg('principal_amount') ?? 0;
        $borrowerCount  = $activeLoans->pluck('user_id')->unique()->count();

        // Portfolio growth: compare this month vs last month
        $thisMonth   = Loan::where('status','active')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('principal_amount');
        $lastMonth   = Loan::where('status','active')->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->sum('principal_amount');
        $growth      = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        $byProduct   = $activeLoans->groupBy(fn($l) => $l->loanProduct->name ?? 'Unknown')->map->sum('outstanding_balance');
        $byStatus    = Loan::selectRaw('status, count(*) as count, sum(outstanding_balance) as total')->groupBy('status')->get();

        return compact('activeLoans','totalPortfolio','totalPrincipal','avgLoanSize','borrowerCount','growth','byProduct','byStatus','thisMonth','lastMonth');
    }

    // ── 2. DISBURSEMENT ───────────────────────────────────────────────────────
    public function getDisbursementReport(array $f): array {
        $from = $f['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $f['date_to']   ?? now()->format('Y-m-d');

        $q = Loan::with(['user','loanProduct'])->whereNotNull('disbursement_date');
        $q->whereDate('disbursement_date','>=',$from)->whereDate('disbursement_date','<=',$to);
        if (!empty($f['product'])) $q->where('loan_product_id',$f['product']);

        if (!empty($f['category'])) {
            $q->join('employments', 'loans.application_id', '=', 'employments.application_id')
                ->where('employments.employer_category', $f['category']);
        }
        $loans = $q->latest('disbursement_date')->get();

        $today      = Loan::whereDate('disbursement_date', today())->sum('principal_amount');
        $thisWeek   = Loan::whereDate('disbursement_date','>=', now()->startOfWeek())->sum('principal_amount');
        $thisMonth  = Loan::whereDate('disbursement_date','>=', now()->startOfMonth())->sum('principal_amount');
        $byProduct  = $loans->groupBy(fn($l) => $l->loanProduct->name ?? 'Unknown')->map->sum('principal_amount');
        $byDay      = $loans->groupBy(fn($l) => $l->disbursement_date->format('d M Y'))->map->sum('principal_amount');

        return compact('loans','from','to','today','thisWeek','thisMonth','byProduct','byDay');
    }

    // ── 3. COLLECTION / REPAYMENT ─────────────────────────────────────────────
    public function getCollectionsReport(array $f): array {
        $from = $f['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $f['date_to']   ?? now()->format('Y-m-d');

        $q = Payment::with(['loan.user'])->where('status','verified');
        $q->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to);
        if (!empty($f['method'])) $q->where('method',$f['method']);
        $payments = $q->latest()->get();

        $today        = Payment::where('status','verified')->whereDate('created_at', today())->sum('amount');
        $thisWeek     = Payment::where('status','verified')->whereDate('created_at','>=', now()->startOfWeek())->sum('amount');
        $thisMonth    = Payment::where('status','verified')->whereDate('created_at','>=', now()->startOfMonth())->sum('amount');
        $failed       = Payment::whereIn('status',['failed','rejected'])->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->count();
        $totalDue     = LoanInstallment::whereDate('due_date','>=',$from)->whereDate('due_date','<=',$to)->sum('total_amount');
        $collectionRate = $totalDue > 0 ? round(($payments->sum('amount') / $totalDue) * 100, 1) : 0;
        $byMethod     = $payments->groupBy('method')->map->sum('amount');
        $byDay        = $payments->groupBy(fn($p) => $p->created_at->format('d M Y'))->map->sum('amount');

        return compact('payments','from','to','today','thisWeek','thisMonth','failed','collectionRate','byMethod','byDay');
    }

    public function getRepaymentReport(array $f): array { return $this->getCollectionsReport($f); }

    // ── 4. OUTSTANDING LOANS ──────────────────────────────────────────────────
    public function getOutstandingReport(array $f): array {
        $q = Loan::with(['user','loanProduct','installments' => fn($q) => $q->whereIn('status',['pending','overdue','partial'])->orderBy('due_date')->limit(1)])
            ->whereIn('loans.status',['active','overdue'])
            ->orderByDesc('outstanding_balance');
        if (!empty($f['product'])) $q->where('loan_product_id',$f['product']);

        if (!empty($f['category'])) {
            $q->join('employments', 'loans.application_id', '=', 'employments.application_id')
                ->where('employments.employer_category', $f['category']);
        }
        if (!empty($f['search'])) {
            $s = $f['search'];
            $q->whereHas('user', fn($u) => $u->where('name','like',"%$s%")->orWhere('phone','like',"%$s%"));
        }
        $loans       = $q->paginate(50);
        $totalOutstanding = Loan::whereIn('status',['active','overdue'])->sum('outstanding_balance');
        $totalLoans       = Loan::whereIn('status',['active','overdue'])->count();

        return compact('loans','totalOutstanding','totalLoans');
    }

    // ── 5. ARREARS ────────────────────────────────────────────────────────────
    public function getArrearsReport(array $f): array {
        $q = LoanInstallment::with(['loan.user','loan.loanProduct'])->where('status','overdue');
        if (!empty($f['min_days'])) $q->whereDate('due_date','<=', now()->subDays($f['min_days']));

        if (!empty($f['category'])) {
            $q->join('loans', 'loan_installments.loan_id', '=', 'loans.id')
                ->join('employments', 'loans.application_id', '=', 'employments.application_id')
                ->where('employments.employer_category', $f['category']);
        }
        $inst = $q->orderBy('due_date')->get();

        $buckets = [
            '1–30 days'  => $inst->filter(fn($i) => $i->due_date->diffInDays(now()) <= 30),
            '31–60 days' => $inst->filter(fn($i) => $i->due_date->diffInDays(now()) > 30 && $i->due_date->diffInDays(now()) <= 60),
            '61–90 days' => $inst->filter(fn($i) => $i->due_date->diffInDays(now()) > 60 && $i->due_date->diffInDays(now()) <= 90),
            '90+ days'   => $inst->filter(fn($i) => $i->due_date->diffInDays(now()) > 90),
        ];

        return ['installments' => $inst, 'total_overdue' => $inst->sum('outstanding_amount'), 'buckets' => $buckets];
    }

    // ── 6. PAR (PORTFOLIO AT RISK) ────────────────────────────────────────────
    public function getParReport(array $f): array {
        $totalPortfolio = Loan::whereIn('status',['active','overdue'])->sum('outstanding_balance');

        $par = [];
        foreach ([1, 30, 60, 90] as $days) {
            $amount = Loan::whereIn('status',['active','overdue'])
                ->whereHas('installments', fn($q) => $q->where('status','overdue')->whereDate('due_date','<=', now()->subDays($days)))
                ->sum('outstanding_balance');
            $pct = $totalPortfolio > 0 ? round(($amount / $totalPortfolio) * 100, 2) : 0;
            $par["PAR $days"] = ['amount' => $amount, 'pct' => $pct];
        }

        // Loans in each PAR bucket with details
        $par30Loans = Loan::with(['user','loanProduct'])
            ->whereIn('status',['active','overdue'])
            ->whereHas('installments', fn($q) => $q->where('status','overdue')->whereDate('due_date','<=', now()->subDays(30)))
            ->get();

        return compact('par','totalPortfolio','par30Loans');
    }

    // ── 7. DEFAULT ────────────────────────────────────────────────────────────
    public function getDefaultReport(array $f): array {
        $from = $f['date_from'] ?? now()->startOfYear()->format('Y-m-d');
        $to   = $f['date_to']   ?? now()->format('Y-m-d');

        $q = Loan::with(['user','loanProduct'])->whereIn('status',['defaulted','written_off']);
        $q->whereDate('updated_at','>=',$from)->whereDate('updated_at','<=',$to);
        $loans = $q->latest()->get();

        $totalDefaulted  = $loans->where('status','defaulted')->sum('outstanding_balance');
        $totalWrittenOff = $loans->where('status','written_off')->sum('outstanding_balance');
        $totalPortfolio  = Loan::sum('principal_amount');
        $defaultRate     = $totalPortfolio > 0 ? round((($totalDefaulted + $totalWrittenOff) / $totalPortfolio) * 100, 2) : 0;

        return compact('loans','totalDefaulted','totalWrittenOff','defaultRate','from','to');
    }

    // ── 8. OFFICER PERFORMANCE ────────────────────────────────────────────────
    public function getOfficerPerformanceReport(array $f): array {
        $q = User::where('role','loan_officer')->with(['assignedApplications.loan']);
        if (!empty($f['officer_id'])) $q->where('id',$f['officer_id']);
        $officers = $q->get()->map(function($o) {
            $apps      = $o->assignedApplications ?? collect();
            $loans     = $apps->pluck('loan')->filter();
            $portfolio = $loans->whereIn('status',['active','overdue'])->sum('outstanding_balance');
            $collected = Payment::where('status','verified')
                ->whereIn('loan_id', $loans->pluck('id'))->sum('amount');
            $totalDue  = LoanInstallment::whereIn('loan_id', $loans->pluck('id'))->sum('total_amount');
            $collRate  = $totalDue > 0 ? round(($collected / $totalDue) * 100, 1) : 0;
            $defaults  = $loans->whereIn('status',['defaulted','written_off'])->count();
            $defRate   = $loans->count() > 0 ? round(($defaults / $loans->count()) * 100, 1) : 0;

            return [
                'officer'    => $o,
                'total_apps' => $apps->count(),
                'approved'   => $apps->where('status','approved')->count(),
                'declined'   => $apps->where('status','declined')->count(),
                'portfolio'  => $portfolio,
                'coll_rate'  => $collRate,
                'def_rate'   => $defRate,
                'active_loans'=> $loans->where('status','active')->count(),
            ];
        });
        return ['officers' => $officers];
    }

    // ── 9. INCOME & PROFIT ────────────────────────────────────────────────────
    public function getIncomeStatementReport(array $f): array {
        $from = $f['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $f['date_to']   ?? now()->format('Y-m-d');

        // Interest income: sum of interest portions of verified payments
        $interestIncome = DB::table('payments')
            ->join('loan_installments','payments.installment_id','=','loan_installments.id')
            ->where('payments.status','verified')
            ->whereDate('payments.created_at','>=',$from)->whereDate('payments.created_at','<=',$to)
            ->sum('loan_installments.interest_amount') ?? 0;

        // Initiation fee income
        $initiationIncome = DB::table('payments')
            ->join('loan_installments','payments.installment_id','=','loan_installments.id')
            ->where('payments.status','verified')
            ->whereDate('payments.created_at','>=',$from)->whereDate('payments.created_at','<=',$to)
            ->sum('loan_installments.initiation_fee_amount') ?? 0;

        // Admin fee income
        $adminFeeIncome = DB::table('payments')
            ->join('loan_installments','payments.installment_id','=','loan_installments.id')
            ->where('payments.status','verified')
            ->whereDate('payments.created_at','>=',$from)->whereDate('payments.created_at','<=',$to)
            ->sum('loan_installments.admin_fee_amount') ?? 0;

        $penaltyIncome  = LoanInstallment::where('status','paid')->whereDate('updated_at','>=',$from)->whereDate('updated_at','<=',$to)->sum('late_fee');
        $totalIncome    = $interestIncome + $initiationIncome + $adminFeeIncome + $penaltyIncome;
        $disbursed      = Loan::whereDate('disbursement_date','>=',$from)->whereDate('disbursement_date','<=',$to)->sum('principal_amount');
        $writeOffs      = Loan::where('status','written_off')->whereDate('updated_at','>=',$from)->whereDate('updated_at','<=',$to)->sum('outstanding_balance');
        $netProfit      = $totalIncome - $writeOffs;

        // Monthly trend (last 6 months)
        $trend = collect(range(5,0))->map(function($i) {
            $d = now()->subMonths($i);
            $inc = Payment::where('status','verified')->whereMonth('created_at',$d->month)->whereYear('created_at',$d->year)->sum('amount');
            return ['month' => $d->format('M Y'), 'income' => $inc];
        });

        return compact('from','to','interestIncome','initiationIncome','adminFeeIncome','penaltyIncome','totalIncome','disbursed','writeOffs','netProfit','trend');
    }

    // ── 10. APPLICATION REPORT ────────────────────────────────────────────────
    public function getApplicationReport(array $f): array {
        $from = $f['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $f['date_to']   ?? now()->format('Y-m-d');

        $q = LoanApplication::with(['user','loanProduct','assignedOfficer'])->where('loan_applications.status','!=','draft');
        $q->whereDate('loan_applications.created_at','>=',$from)->whereDate('loan_applications.created_at','<=',$to);
        if (!empty($f['status'])) $q->where('loan_applications.status',$f['status']);

        if (!empty($f['category'])) {
            $q->join('employments', 'loan_applications.id', '=', 'employments.application_id')
                ->where('employments.employer_category', $f['category']);
        }
        $apps = $q->latest()->get();

        $submitted = $apps->whereIn('status',['submitted','under_review','info_requested','on_hold'])->count();
        $approved  = $apps->whereIn('status',['approved','disbursed'])->count();
        $declined  = $apps->where('status','declined')->count();
        $pending   = $apps->whereIn('status',['submitted','under_review','on_hold','info_requested'])->count();
        $approvalRate = $apps->count() > 0 ? round(($approved / $apps->count()) * 100, 1) : 0;

        $byStatus = $apps->groupBy('status')->map->count();
        $byProduct = $apps->groupBy(fn($a) => $a->loanProduct->name ?? 'Unknown')->map->count();
        $byDay    = $apps->groupBy(fn($a) => $a->created_at->format('d M Y'))->map->count();

        return compact('apps','from','to','submitted','approved','declined','pending','approvalRate','byStatus','byProduct','byDay');
    }

    // ── 11. PAYMENT FAILURE REPORT ────────────────────────────────────────────
    public function getPaymentFailureReport(array $f): array {
        $from = $f['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $f['date_to']   ?? now()->format('Y-m-d');

        $q = Payment::with(['loan.user'])->whereIn('status',['failed','rejected']);
        $q->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to);
        if (!empty($f['method'])) $q->where('method',$f['method']);
        $payments = $q->latest()->get();

        $totalFailed     = $payments->count();
        $totalAmount     = $payments->sum('amount');
        $byMethod        = $payments->groupBy('method')->map->count();
        $byReason        = $payments->groupBy('failure_reason')->map->count();
        $insufficientFunds = $payments->where('failure_reason','insufficient_funds')->count();
        $blockedCard     = $payments->where('failure_reason','card_blocked')->count();

        return compact('payments','from','to','totalFailed','totalAmount','byMethod','byReason','insufficientFunds','blockedCard');
    }

    // ── PRODUCT PERFORMANCE ───────────────────────────────────────────────────
    public function getProductPerformanceReport(array $f): array {
        $products = LoanProduct::with('loans')->get()->map(fn($p) => [
            'product'       => $p,
            'total_loans'   => $p->loans->count(),
            'total_disbursed'=> $p->loans->sum('principal_amount'),
            'outstanding'   => $p->loans->sum('outstanding_balance'),
            'overdue_count' => $p->loans->where('status','overdue')->count(),
        ]);
        return ['products' => $products];
    }

    // ── EXPORT CSV ────────────────────────────────────────────────────────────
    public function export(string $type, string $format, array $f) {
        $map = [
            'portfolio'       => 'getPortfolioReport',
            'disbursement'    => 'getDisbursementReport',
            'collections'     => 'getCollectionsReport',
            'outstanding'     => 'getOutstandingReport',
            'arrears'         => 'getArrearsReport',
            'par'             => 'getParReport',
            'default'         => 'getDefaultReport',
            'officer_performance' => 'getOfficerPerformanceReport',
            'income_statement'=> 'getIncomeStatementReport',
            'applications'    => 'getApplicationReport',
            'payment_failures'=> 'getPaymentFailureReport',
        ];
        $method = $map[$type] ?? 'getPortfolioReport';
        $data   = $this->$method($f);
        $filename = $type.'_'.now()->format('Y-m-d').'.csv';
        $headers  = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=\"$filename\""];
        return response()->stream(function() use($data) {
            $fh = fopen('php://output','w');
            foreach ($data as $v) {
                if (is_iterable($v)) {
                    foreach ($v as $row) {
                        if (is_object($row) && method_exists($row,'toArray')) fputcsv($fh,$row->toArray());
                        elseif (is_array($row)) fputcsv($fh,$row);
                    }
                    break;
                }
            }
            fclose($fh);
        }, 200, $headers);
    }

    public function getBorrowerDemographicsReport(array $f): array {
        $borrowers = User::where('role','borrower')->with('loans')->get();
        return [
            'total'          => $borrowers->count(),
            'active'         => $borrowers->where('is_active',true)->count(),
            'with_loans'     => $borrowers->filter(fn($u) => $u->loans->count() > 0)->count(),
            'by_gender'      => LoanApplication::whereNotNull('gender')->selectRaw('gender, count(*) as count')->groupBy('gender')->pluck('count','gender'),
            'new_this_month' => User::where('role','borrower')->whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->count(),
        ];
    }

    private function dates($q, array $f, string $col = 'created_at'): void {
        if (!empty($f['date_from'])) $q->whereDate($col,'>=',$f['date_from']);
        if (!empty($f['date_to']))   $q->whereDate($col,'<=',$f['date_to']);
    }
}
