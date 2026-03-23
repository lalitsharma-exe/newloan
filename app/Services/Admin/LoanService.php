<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanInstallment, Payment, User};

class LoanService
{
    public function getPaginated(array $filters, int $perPage = 15)
    {
        $q = Loan::with(['user', 'loanProduct']);

        if (!empty($filters['status']))    $q->where('status', $filters['status']);
        if (!empty($filters['product']))   $q->where('loan_product_id', $filters['product']);
        if (!empty($filters['overdue']))   $q->where('status', 'overdue');
        if (!empty($filters['date_from'])) $q->whereDate('disbursement_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('disbursement_date', '<=', $filters['date_to']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($q) use ($s) {
                $q->where('loan_number', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%"));
            });
        }

        return $q->latest()->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total_active'    => Loan::where('status', 'active')->count(),
            'total_overdue'   => Loan::where('status', 'overdue')->count(),
            'total_portfolio' => Loan::where('status', 'active')->sum('outstanding_balance'),
            'paid_off'        => Loan::where('status', 'paid_off')->count(),
        ];
    }

    public function adjustSchedule(Loan $loan, array $data, User $admin): void
    {
        $remaining = $loan->installments()->whereIn('status',['pending','partial'])->orderBy('installment_number')->get();
        $newTerm   = count($remaining);
        if ($newTerm === 0) return;

        $product       = $loan->loanProduct;
        $newRate       = isset($data['interest_rate']) ? (float)$data['interest_rate'] / 100 : $loan->interest_rate / 100;
        $adminPerMonth = (float) ($product?->admin_fee_fixed ?? 50);

        // Flat: recalculate on remaining outstanding principal only
        $outstandingPrincipal = $remaining->sum('principal_amount');
        $initiationRate       = ($product?->initiation_fee_rate ?? 40) / 100;

        $interestPerMonth    = round($outstandingPrincipal * $newRate, 2);
        $principalPerMonth   = round($outstandingPrincipal / $newTerm, 2);
        $initiationPerMonth  = round(($outstandingPrincipal * $initiationRate) / $newTerm, 2);

        foreach ($remaining as $idx => $inst) {
            $isLast = ($idx === $newTerm - 1);
            $prin   = $isLast ? round($outstandingPrincipal - $principalPerMonth * ($newTerm - 1), 2) : $principalPerMonth;
            $init   = $isLast ? round(($outstandingPrincipal * $initiationRate) - $initiationPerMonth * ($newTerm - 1), 2) : $initiationPerMonth;
            $total  = round($prin + $interestPerMonth + $adminPerMonth + $init, 2);

            $inst->update([
                'principal_amount'      => $prin,
                'interest_amount'       => $interestPerMonth,
                'initiation_fee_amount' => $init,
                'admin_fee_amount'      => $adminPerMonth,
                'total_amount'          => $total,
                'outstanding_amount'    => $total,
            ]);
        }

        // Update loan's monthly_installment to new amount
        if ($remaining->count() > 0) {
            $loan->update(['monthly_installment' => $remaining->first()->fresh()->total_amount]);
        }
    }

    public function recordManualPayment(Loan $loan, array $data, User $admin): Payment
    {
        $payment = Payment::create([
            'payment_reference' => 'PAY-MAN-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'loan_id'           => $loan->id,
            'user_id'           => $loan->user_id,
            'amount'            => $data['amount'],
            'method'            => $data['method'],
            'status'            => 'verified',
            'notes'             => $data['notes'] ?? 'Manual payment recorded by admin',
            'is_manual'         => true,
            'verified_by'       => $admin->id,
            'verified_at'       => now(),
        ]);

        // ── Payment allocation order: Penalty → Admin Fee → Interest → Principal ──
        // Per client spec: overdue instalments first, then by allocation priority within each
        $remaining = (float) $data['amount'];
        $installments = $loan->installments()
            ->whereIn('status', ['pending','overdue','partial'])
            ->orderBy('due_date')   // oldest first
            ->get();

        foreach ($installments as $inst) {
            if ($remaining <= 0) break;

            // Amounts already paid per component (tracked proportionally from paid_amount)
            $totalComp   = (float) $inst->total_amount;
            $alreadyPaid = (float) $inst->paid_amount;
            $stillOwed   = max(0, round($totalComp - $alreadyPaid, 2));
            if ($stillOwed <= 0) continue;

            // Component breakdown of what's still owed, in allocation order:
            // 1. Penalty Fee   2. Admin Fee   3. Interest   4. Principal
            $components = [
                'late_fee'              => (float) $inst->late_fee,
                'admin_fee_amount'      => (float) ($inst->admin_fee_amount ?? 0),
                'interest_amount'       => (float) $inst->interest_amount,
                'principal_amount'      => (float) $inst->principal_amount,
                'initiation_fee_amount' => (float) ($inst->initiation_fee_amount ?? 0),
            ];

            // Calculate how much of each component has been paid proportionally
            // (simple approach: payment fills components in order until exhausted)
            $paidSoFar = $alreadyPaid;
            $componentPaid = [];
            foreach ($components as $comp => $compTotal) {
                if ($paidSoFar <= 0) { $componentPaid[$comp] = 0; continue; }
                $compPaid = min($paidSoFar, $compTotal);
                $componentPaid[$comp] = $compPaid;
                $paidSoFar -= $compPaid;
            }

            // Now apply remaining payment to components in order
            $appliedToInst = 0;
            foreach ($components as $comp => $compTotal) {
                if ($remaining <= 0) break;
                $compOwed = max(0, $compTotal - ($componentPaid[$comp] ?? 0));
                if ($compOwed <= 0) continue;
                $pay = min($remaining, $compOwed);
                $remaining      -= $pay;
                $appliedToInst  += $pay;
            }

            $newPaid        = round($alreadyPaid + $appliedToInst, 2);
            $newOutstanding = round(max(0, $totalComp - $newPaid), 2);

            $inst->update([
                'paid_amount'        => $newPaid,
                'outstanding_amount' => $newOutstanding,
                'paid_at'            => $newOutstanding <= 0 ? now() : $inst->paid_at,
                'status'             => $newOutstanding <= 0 ? 'paid' : 'partial',
            ]);

            if (!$payment->installment_id) {
                $payment->update(['installment_id' => $inst->id]);
            }
        }

        // Update loan outstanding balance
        $actualApplied = (float) $data['amount'] - $remaining;
        $loan->decrement('outstanding_balance', $actualApplied);

        // Check fully paid
        $loan->refresh();
        if ($loan->outstanding_balance <= 0 || $loan->installments()->whereNotIn('status',['paid','waived'])->count() === 0) {
            $loan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
        }

        return $payment;
    }

    public function closeLoan(Loan $loan, string $reason, User $admin): void
    {
        $loan->update([
            'status'       => 'closed',
            'closed_at'    => now(),
            'closed_reason'=> $reason,
            'closed_by'    => $admin->id,
        ]);
    }

    // ── Bulk repayment: record up to 30 payments at once ──────────────────────
    public function recordBulkPayments(array $rows, string $method, User $admin): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $i => $row) {
            $loanNumber = trim($row['loan_number'] ?? '');
            $amount     = (float) ($row['amount'] ?? 0);
            if (!$loanNumber || $amount <= 0) { $results['failed']++; continue; }

            $loan = Loan::where('loan_number', $loanNumber)
                        ->whereIn('status', ['active','overdue'])
                        ->with('installments','loanProduct')
                        ->first();

            if (!$loan) {
                $results['failed']++;
                $results['errors'][] = "Row ".($i+1).": Loan {$loanNumber} not found or not active.";
                continue;
            }

            try {
                $this->recordManualPayment($loan, [
                    'amount' => $amount,
                    'method' => $method,
                    'notes'  => $row['notes'] ?? 'Bulk repayment entry',
                ], $admin);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Row ".($i+1)." ({$loanNumber}): ".$e->getMessage();
            }
        }

        return $results;
    }

    // ── Import loans from CSV ─────────────────────────────────────────────────
    public function importLoansFromCsv(array $rows, User $admin): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $i => $row) {
            try {
                $userEmail  = trim($row['email'] ?? '');
                $principal  = (float) ($row['principal_amount'] ?? $row['amount'] ?? 0);
                $term       = (int) ($row['term_months'] ?? $row['term'] ?? 1);
                $rate       = (float) ($row['interest_rate'] ?? 15);
                $disbDate   = $row['disbursement_date'] ?? now()->format('Y-m-d');

                if (!$userEmail || $principal <= 0) {
                    $results['failed']++;
                    $results['errors'][] = "Row ".($i+1).": email and amount are required.";
                    continue;
                }

                $user = \App\Models\User::where('email', $userEmail)->first();
                if (!$user) {
                    $results['failed']++;
                    $results['errors'][] = "Row ".($i+1).": No borrower found with email {$userEmail}.";
                    continue;
                }

                // Find or use default product
                $productName = $row['product'] ?? '';
                $product     = $productName
                    ? \App\Models\LoanProduct::where('name', 'like', "%{$productName}%")->first()
                    : \App\Models\LoanProduct::active()->first();

                $initiationRate  = ($product?->initiation_fee_rate ?? 40) / 100;
                $adminPerMonth   = (float) ($product?->admin_fee_fixed ?? 50);
                $totalInterest   = round($principal * ($rate / 100) * $term, 2);
                $totalInitiation = round($principal * $initiationRate, 2);
                $totalAdmin      = $adminPerMonth * $term;
                $totalRepay      = $principal + $totalInterest + $totalInitiation + $totalAdmin;
                $monthly         = round($totalRepay / $term, 2);
                $nextId          = (Loan::max('id') ?? 0) + $results['success'] + 1;

                $loan = Loan::create([
                    'loan_number'         => 'LN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT),
                    'user_id'             => $user->id,
                    'loan_product_id'     => $product?->id,
                    'principal_amount'    => $principal,
                    'interest_rate'       => $rate,
                    'term_months'         => $term,
                    'total_amount'        => $totalRepay,
                    'outstanding_balance' => $totalRepay,
                    'monthly_installment' => $monthly,
                    'processing_fee'      => $totalInitiation,
                    'status'              => 'active',
                    'disbursement_date'   => $disbDate,
                    'first_payment_date'  => \Carbon\Carbon::parse($disbDate)->addMonth()->startOfMonth()->toDateString(),
                    'maturity_date'       => \Carbon\Carbon::parse($disbDate)->addMonths($term)->toDateString(),
                    'collection_method'   => $row['collection_method'] ?? 'payroll',
                    'payout_method'       => $row['payout_method'] ?? 'bank_transfer',
                ]);

                // Generate installments
                $principalPerMonth  = round($principal / $term, 2);
                $interestPerMonth   = round($principal * ($rate / 100), 2);
                $initiationPerMonth = round($totalInitiation / $term, 2);
                $payDate = \Carbon\Carbon::parse($disbDate)->addMonth()->startOfMonth();

                for ($m = 1; $m <= $term; $m++) {
                    $isLast = ($m === $term);
                    $prin   = $isLast ? round($principal - $principalPerMonth * ($term - 1), 2) : $principalPerMonth;
                    $init   = $isLast ? round($totalInitiation - $initiationPerMonth * ($term - 1), 2) : $initiationPerMonth;
                    $total  = round($prin + $interestPerMonth + $adminPerMonth + $init, 2);

                    \App\Models\LoanInstallment::create([
                        'loan_id'               => $loan->id,
                        'installment_number'    => $m,
                        'due_date'              => $payDate->copy()->toDateString(),
                        'principal_amount'      => $prin,
                        'interest_amount'       => $interestPerMonth,
                        'initiation_fee_amount' => $init,
                        'admin_fee_amount'      => $adminPerMonth,
                        'total_amount'          => $total,
                        'paid_amount'           => 0,
                        'outstanding_amount'    => $total,
                        'status'                => 'pending',
                    ]);
                    $payDate->addMonth();
                }

                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Row ".($i+1).": ".$e->getMessage();
            }
        }

        return $results;
    }

    // ── Collection sheet: loans with payments due today / this week ───────────
    public function getCollectionSheet(string $date, ?int $officerId = null): array
    {
        $targetDate = \Carbon\Carbon::parse($date);

        $q = \App\Models\LoanInstallment::with(['loan.user', 'loan.loanProduct', 'loan.application.assignedOfficer'])
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->whereDate('due_date', '<=', $targetDate)
            ->orderBy('due_date');

        if ($officerId) {
            $q->whereHas('loan.application', fn($a) => $a->where('assigned_officer_id', $officerId));
        }

        $installments = $q->get();

        return [
            'date'          => $date,
            'installments'  => $installments,
            'total_due'     => $installments->sum('outstanding_amount'),
            'total_count'   => $installments->count(),
            'overdue_count' => $installments->where('status', 'overdue')->count(),
            'by_officer'    => $installments->groupBy(fn($i) =>
                $i->loan->application?->assignedOfficer?->name ?? 'Unassigned'
            ),
        ];
    }

    // ── Repayment chart data: monthly collections last 12 months ─────────────
    public function getRepaymentChartData(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $m       = now()->subMonths($i);
            $label   = $m->format('M Y');
            $start   = $m->startOfMonth()->format('Y-m-d');
            $end     = $m->copy()->endOfMonth()->format('Y-m-d');

            $collected = Payment::where('status', 'verified')
                ->whereDate('verified_at', '>=', $start)
                ->whereDate('verified_at', '<=', $end)
                ->sum('amount');

            $disbursed = Loan::whereDate('disbursement_date', '>=', $start)
                ->whereDate('disbursement_date', '<=', $end)
                ->sum('principal_amount');

            $months[] = [
                'label'     => $label,
                'collected' => round((float) $collected, 2),
                'disbursed' => round((float) $disbursed, 2),
            ];
        }

        // By method (all time last 12 months)
        $byMethod = Payment::where('status', 'verified')
            ->whereDate('verified_at', '>=', now()->subMonths(12)->format('Y-m-d'))
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->map(fn($v) => round((float) $v, 2))
            ->toArray();

        return ['months' => $months, 'by_method' => $byMethod];
    }

    public function generateAgreementPdf(Loan $loan): string
    {
        return storage_path("app/agreements/loan_{$loan->loan_number}.pdf");
    }


    // ── Pre-disbursement checks ────────────────────────────────────────────────
    public function preDisbursementChecks(Loan $loan): array
    {
        $app          = $loan->application;
        $docs         = $app?->documents ?? collect();
        $affordability= $app?->affordability;

        // KYC: national_id document verified
        $kycDoc = $docs->where('type', 'national_id')->where('status', 'verified')->first();
        // Payslip verified
        $payslipDoc = $docs->where('type', 'payslip')->where('status', 'verified')->first();

        // Affordability: assessment exists and disposable income > 0
        $affordabilityPassed = $affordability && ((float)$affordability->disposable_income) > 0;

        // Approved: application status is approved or disbursed
        $isApproved = $app && in_array($app->status, ['approved', 'disbursed']);

        // Repayment method: collection_method is set
        $repaymentActive = !empty($loan->collection_method);

        return [
            [
                'label'    => 'KYC Verified',
                'pass'     => (bool) $kycDoc,
                'required' => false,   // warning only — admin can still disburse
                'detail'   => $kycDoc ? 'National ID document verified' : 'National ID document missing or not verified (warning)',
            ],
            [
                'label'    => 'Affordability Passed',
                'pass'     => $affordabilityPassed,
                'required' => false,   // warning only — admin can still disburse
                'detail'   => $affordabilityPassed
                    ? 'Disposable income: M'.number_format((float)$affordability->disposable_income, 2)
                    : 'Affordability assessment not completed (warning)',
            ],
            [
                'label'    => 'Loan Approved',
                'pass'     => $isApproved,
                'required' => true,
                'detail'   => $isApproved ? 'Application approved' : 'Application has not been approved yet',
            ],
            [
                'label'    => 'Repayment Method Active',
                'pass'     => $repaymentActive,
                'required' => false,
                'detail'   => $repaymentActive
                    ? 'Collection method: '.ucfirst(str_replace('_',' ',$loan->collection_method))
                    : 'No collection method set (warning only)',
            ],
            [
                'label'    => 'Supporting Documents',
                'pass'     => (bool) $payslipDoc,
                'required' => false,
                'detail'   => $payslipDoc ? 'Payslip verified' : 'Payslip missing or not verified (warning only)',
            ],
        ];
    }

    // ── Generate installments for an already-approved loan ───────────────────
    public function generateInstallments(Loan $loan): void
    {
        $principal  = (float) $loan->principal_amount;
        $term       = (int)   $loan->term_months;
        $product    = $loan->loanProduct;

        $monthlyRate     = (float) $loan->interest_rate / 100;
        $initiationRate  = ($product?->initiation_fee_rate ?? 40) / 100;
        $adminPerMonth   = (float) ($product?->admin_fee_fixed ?? 50);

        $totalInterest   = round($principal * $monthlyRate * $term, 2);
        $totalInitiation = round($principal * $initiationRate, 2);

        $principalPerMonth  = round($principal / $term, 2);
        $interestPerMonth   = round($principal * $monthlyRate, 2);
        $initiationPerMonth = round($totalInitiation / $term, 2);

        $payday = $loan->salary_payday ?? 25;
        $payDate = \Carbon\Carbon::parse($loan->disbursement_date)->addMonth()->setDay($payday);

        for ($i = 1; $i <= $term; $i++) {
            $isLast = ($i === $term);
            $prin   = $isLast ? round($principal - $principalPerMonth * ($term - 1), 2) : $principalPerMonth;
            $init   = $isLast ? round($totalInitiation - $initiationPerMonth * ($term - 1), 2) : $initiationPerMonth;
            $total  = round($prin + $interestPerMonth + $adminPerMonth + $init, 2);

            \App\Models\LoanInstallment::create([
                'loan_id'               => $loan->id,
                'installment_number'    => $i,
                'due_date'              => $payDate->copy()->toDateString(),
                'principal_amount'      => $prin,
                'interest_amount'       => $interestPerMonth,
                'initiation_fee_amount' => $init,
                'admin_fee_amount'      => $adminPerMonth,
                'total_amount'          => $total,
                'paid_amount'           => 0,
                'outstanding_amount'    => $total,
                'status'                => 'pending',
            ]);
            $payDate->addMonth();
        }
    }
}