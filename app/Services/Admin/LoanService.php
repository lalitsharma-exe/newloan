<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanInstallment, Payment, User, Referral};

class LoanService
{
    public function validateReferral(Loan $loan): void
    {
        $borrower = $loan->user;
        $referral = Referral::where('referred_id', $borrower->id)->where('status', 'pending')->first();
        
        if (!$referral) return;

        // Check if it's the 1st loan
        $loanCount = Loan::where('user_id', $borrower->id)->count();
        
        if ($loanCount === 1) {
            $referral->update([
                'status'        => 'validated',
                'loan_id'       => $loan->id,
                'is_first_loan' => true,
            ]);
        } else {
            $referral->update([
                'status'          => 'rejected',
                'is_repeat_loan'  => true,
                'rejected_reason' => 'Repeat loan detected (First loan only rule)',
            ]);
        }
    }

    public function checkReferralQualification(Loan $loan): void
    {
        $referral = Referral::where('loan_id', $loan->id)
            ->where('status', 'validated')
            ->first();

        if (!$referral) return;

        // Check if the FIRST installment is paid
        $firstInst = $loan->installments()->orderBy('installment_number')->first();
        
        if ($firstInst && $firstInst->status === 'paid') {
            $referral->update([
                'status'       => 'qualified',
                'qualified_at' => now(),
                'amount'       => 50.00, // Fixed referral reward
            ]);
        }
    }

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
        // 1. Identify remaining unpaid installments (those we are allowed to change)
        $remaining = $loan->installments()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('installment_number')
            ->get();
            
        if ($remaining->isEmpty()) return;

        // 2. Frozen installments (already paid/waived)
        $frozen = $loan->installments()
            ->whereIn('status', ['paid', 'waived'])
            ->orderBy('installment_number')
            ->get();

        // 3. Determine Remaining Principal to redistribute
        // Total Principal to be distributed = (Original Loan Principal) - (Principal already covered in paid installments)
        $paidPrincipal = $frozen->sum('principal_amount');
        $outstandingPrincipal = max(0, $loan->principal_amount - $paidPrincipal);

        $product        = $loan->loanProduct;
        $newRate        = isset($data['interest_rate']) ? (float)$data['interest_rate'] / 100 : $loan->interest_rate / 100;
        $adminPerMonth  = (float) ($product?->admin_fee_fixed ?? 50);
        $initiationRate = ($product?->initiation_fee_rate ?? 40) / 100;
        
        $currentRemainingCount = $remaining->count();
        $requestedTerm         = isset($data['new_term']) ? (int)$data['new_term'] : $currentRemainingCount;

        // ── Step 1: Adjust installment count ──
        if ($requestedTerm > $currentRemainingCount) {
            // Addition: Add new installments after the last one
            $lastInst = $remaining->last();
            $lastNum  = $lastInst->installment_number;
            $lastDate = \Carbon\Carbon::parse($lastInst->due_date);
            
            for ($i = 1; $i <= ($requestedTerm - $currentRemainingCount); $i++) {
                $newInst = $lastInst->replicate();
                $newInst->installment_number = $lastNum + $i;
                $newInst->due_date = $lastDate->copy()->addMonths($i)->toDateString();
                $newInst->paid_amount = 0;
                $newInst->outstanding_amount = 0; // Will be set in Step 2
                $newInst->status = 'pending';
                $newInst->save();
            }
        } elseif ($requestedTerm < $currentRemainingCount && $requestedTerm > 0) {
            // Removal: Remove the latest un-paid installments
            $toRemoveCount = $currentRemainingCount - $requestedTerm;
            $idsToRemove = $remaining->take(-$toRemoveCount)->pluck('id');
            LoanInstallment::whereIn('id', $idsToRemove)->delete();
        }

        // Refresh remaining list after additions/removals
        $remaining = $loan->installments()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('installment_number')
            ->get();
            
        $newTermCount = $remaining->count();
        if ($newTermCount === 0) return;
        
        // ── Step 2: Recalculate components for the new remaining term ──
        // Principal is spread evenly. Interest/Admin are per month. Initiation is also spread.
        $interestPerMonth    = round($outstandingPrincipal * $newRate, 2);
        $principalPerMonth   = round($outstandingPrincipal / $newTermCount, 2);
        
        $totalInitiation     = round($loan->principal_amount * $initiationRate, 2);
        $paidInitiation      = $frozen->sum('initiation_fee_amount');
        $remainingInitiation = max(0, $totalInitiation - $paidInitiation);
        $initiationPerMonth  = round($remainingInitiation / $newTermCount, 2);

        foreach ($remaining as $idx => $inst) {
            $isLast = ($idx === $newTermCount - 1);
            
            // Handle precision by putting residue in last installment
            $prin   = $isLast ? round($outstandingPrincipal - $principalPerMonth * ($newTermCount - 1), 2) : $principalPerMonth;
            $init   = $isLast ? round($remainingInitiation - $initiationPerMonth * ($newTermCount - 1), 2) : $initiationPerMonth;
            
            $total  = round($prin + $interestPerMonth + $adminPerMonth + $init, 2);

            $inst->update([
                'principal_amount'      => $prin,
                'interest_amount'       => $interestPerMonth,
                'initiation_fee_amount' => $init,
                'admin_fee_amount'      => $adminPerMonth,
                'total_amount'          => $total,
                'outstanding_amount'    => round(max(0, $total - $inst->paid_amount), 2),
            ]);
        }

        // ── Step 3: Update Loan Metadata ──
        $allInst = $loan->installments()->get();
        $loan->update([
            'term_months'         => $allInst->count(),
            'monthly_installment' => $remaining->first()?->total_amount ?? $loan->monthly_installment,
            'maturity_date'       => $allInst->last()?->due_date ?? $loan->maturity_date,
            'total_amount'        => round($allInst->sum('total_amount'), 2),
            'outstanding_balance' => round($allInst->sum('outstanding_amount'), 2),
        ]);
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
            'verified_at'       => $data['payment_date'] ?? now(),
            'created_at'        => $data['payment_date'] ?? now(),
        ]);

        // ── Payment allocation order: Penalty → Admin Fee → Interest → Principal ──
        $remaining = (float) $data['amount'];
        $portions = [
            'penalty'   => 0,
            'admin'     => 0,
            'interest'  => 0,
            'principal' => 0,
            'initiation'=> 0
        ];

        $installments = $loan->installments()
            ->whereIn('status', ['pending','overdue','partial'])
            ->orderBy('due_date')
            ->get();

        foreach ($installments as $inst) {
            if ($remaining <= 0) break;

            $totalComp   = (float) $inst->total_amount;
            $alreadyPaid = (float) $inst->paid_amount;
            $stillOwed   = max(0, round($totalComp - $alreadyPaid, 2));
            if ($stillOwed <= 0) continue;

            $components = [
                'penalty'    => (float) $inst->late_fee,
                'admin'      => (float) ($inst->admin_fee_amount ?? 0),
                'interest'   => (float) $inst->interest_amount,
                'initiation' => (float) ($inst->initiation_fee_amount ?? 0),
                'principal'  => (float) $inst->principal_amount,
            ];

            // How much of each component is still owed for this installment?
            // (Simplified: we track what was already paid overall and fill in order)
            $paidSoFar = $alreadyPaid;
            $appliedToInst = 0;

            foreach ($components as $key => $total) {
                if ($paidSoFar > 0) {
                    $deduct = min($paidSoFar, $total);
                    $total -= $deduct;
                    $paidSoFar -= $deduct;
                }

                if ($total > 0 && $remaining > 0) {
                    $pay = min($remaining, $total);
                    $remaining -= $pay;
                    $appliedToInst += $pay;
                    $portions[$key] += $pay;
                }
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

        // Update payment with portions
        $payment->update([
            'principal_portion'      => $portions['principal'],
            'interest_portion'       => $portions['interest'],
            'initiation_fee_portion' => $portions['initiation'],
            'admin_fee_portion'      => $portions['admin'],
            'penalty_portion'        => $portions['penalty'],
            'repayment_components'   => json_encode($portions),
        ]);

        // Update loan outstanding balance
        $actualApplied = (float) $data['amount'] - $remaining;
        $loan->decrement('outstanding_balance', $actualApplied);

        // Check fully paid
        $loan->refresh();
        if ($loan->outstanding_balance <= 0 || $loan->installments()->whereNotIn('status',['paid','waived'])->count() === 0) {
            $loan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
        }

        // Referral System: Check if this payment qualifies a referral
        $this->checkReferralQualification($loan);

        // Send Receipt SMS
        try {
            $loan->user->notify(new \App\Notifications\PaymentReceivedSms($payment));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send receipt SMS for payment {$payment->payment_reference}: " . $e->getMessage());
        }

        return $payment;
    }

    public function closeLoan(Loan $loan, string $reason, User $admin): void
    {
        // 1. Zero out the loan balance
        $loan->update([
            'status'              => 'closed',
            'outstanding_balance' => 0,
            'closed_at'           => now(),
            'closed_reason'       => $reason,
            'closed_by'           => $admin->id,
        ]);

        // 2. Waive all unpaid installments
        $loan->installments()
            ->whereNotIn('status', ['paid', 'waived'])
            ->update([
                'status'             => 'waived',
                'outstanding_amount' => 0
            ]);
    }

    public function writeOffLoan(Loan $loan, string $reason, User $admin): void
    {
        // 1. Mark as written off and zero balance
        $loan->update([
            'status'              => 'written_off',
            'outstanding_balance' => 0,
            'closed_at'           => now(),
            'closed_reason'       => $reason,
            'closed_by'           => $admin->id,
        ]);

        // 2. Waive all unpaid installments
        $loan->installments()
            ->whereNotIn('status', ['paid', 'waived'])
            ->update([
                'status'             => 'waived',
                'outstanding_amount' => 0
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
                $payday     = (int) ($row['salary_payday'] ?? 25);

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
                    'salary_payday'       => $payday,
                    'first_payment_date'  => \Carbon\Carbon::parse($disbDate)->addMonth()->setDay($payday)->toDateString(),
                    'maturity_date'       => \Carbon\Carbon::parse($disbDate)->addMonths($term)->setDay($payday)->toDateString(),
                    'collection_method'   => $row['collection_method'] ?? 'payroll',
                    'payout_method'       => $row['payout_method'] ?? 'bank_transfer',
                ]);

                // Generate installments
                $principalPerMonth  = round($principal / $term, 2);
                $interestPerMonth   = round($principal * ($rate / 100), 2);
                $initiationPerMonth = round($totalInitiation / $term, 2);
                $payDate = \Carbon\Carbon::parse($disbDate)->addMonth()->setDay($payday);

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

        $q = \App\Models\LoanInstallment::with(['loan.user', 'loan.loanProduct', 'loan.application.assignedOfficer', 'loan.application.bankDetails'])
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
        $method     = $product?->interest_method ?? 'reducing';

        $monthlyRate        = (float) $loan->interest_rate / 100;
        $initiationRate     = ($product?->initiation_fee_rate ?? 0) / 100;
        $adminPerMonth      = (float) ($product?->admin_fee_fixed ?? 0);
        $totalInitiation    = round($principal * $initiationRate, 2);
        $initiationPerMonth = round($totalInitiation / $term, 2);

        $payday  = (int) ($loan->salary_payday ?? 25);
        $payDate = \Carbon\Carbon::parse($loan->disbursement_date)->addMonth()->setDay($payday);

        if ($method === 'reducing') {
            $pmt = ($monthlyRate > 0)
                ? ($principal * $monthlyRate * pow(1 + $monthlyRate, $term)) / (pow(1 + $monthlyRate, $term) - 1)
                : ($principal / $term);
            $monthlyInstallment = round($pmt + $adminPerMonth + $initiationPerMonth, 2);

            $balance = $principal;
            for ($i = 1; $i <= $term; $i++) {
                $isLast = ($i === $term);
                $interest = round($balance * $monthlyRate, 2);
                $init = $isLast ? round($totalInitiation - $initiationPerMonth * ($term - 1), 2) : $initiationPerMonth;

                if ($isLast) {
                    $prin = $balance;
                    $total = round($prin + $interest + $adminPerMonth + $init, 2);
                } else {
                    $prin = round($pmt - $interest, 2);
                    if ($prin > $balance) {
                        $prin = $balance;
                    }
                    $total = $monthlyInstallment;
                }

                \App\Models\LoanInstallment::create([
                    'loan_id'               => $loan->id,
                    'installment_number'    => $i,
                    'due_date'              => $payDate->copy()->toDateString(),
                    'principal_amount'      => $prin,
                    'interest_amount'       => $interest,
                    'initiation_fee_amount' => $init,
                    'admin_fee_amount'      => $adminPerMonth,
                    'total_amount'          => $total,
                    'paid_amount'           => 0,
                    'outstanding_amount'    => $total,
                    'status'                => 'pending',
                ]);

                $balance = round($balance - $prin, 2);
                $payDate->addMonth();
            }
        } else {
            $totalInterest      = round($principal * $monthlyRate * $term, 2);
            $principalPerMonth  = round($principal / $term, 2);
            $interestPerMonth   = round($totalInterest / $term, 2);

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

    public function finalizeDisbursement(\App\Models\Loan $loan, array $data, \App\Models\User $admin): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($loan, $data, $admin) {
            $account = \App\Models\TreasuryAccount::findOrFail($data['treasury_account_id']);
            $isMD = $account->is_director_owned;

            $loan->update([
                'status'                    => 'active',
                'disbursement_date'         => $data['disbursement_date'],
                'disbursed_from_account_id' => $account->id,
                'transaction_reference'     => $data['transaction_reference'],
                'payment_method'            => $isMD ? 'Mobile Wallet' : ($account->type === 'bank' ? 'Bank Transfer' : 'Mobile Wallet'),
                'funding_source_type'       => $isMD ? 'Director' : 'Company',
                'authorisation_confirmed'   => true,
                'authorisation_at'          => now(),
                'disbursement_notes'        => $data['notes'] ?? null,
                'proof_of_payment_path'     => $data['proof_path'] ?? null,
            ]);

            if ($loan->application) {
                $loan->application->update(['status' => 'disbursed']);
            }

            if ($isMD) {
                \App\Models\DirectorInvestment::create([
                    'amount_invested' => $loan->principal_amount,
                    'investment_date' => $data['disbursement_date'],
                    'monthly_rate'    => 0.05,
                    'status'          => 'active',
                    'notes'           => "Funded Loan: {$loan->loan_number}",
                ]);
            } else {
                \App\Models\TreasuryTransaction::create([
                    'treasury_account_id' => $account->id,
                    'type'                => 'DISBURSEMENT',
                    'direction'           => 'out',
                    'amount'              => $loan->principal_amount,
                    'reference'           => $data['transaction_reference'],
                    'description'         => "Disbursement for Loan {$loan->loan_number}",
                    'loan_id'             => $loan->id,
                    'recorded_by'         => $admin->id,
                ]);
                $account->decrement('balance', $loan->principal_amount);
            }

            if ($loan->installments()->count() === 0) {
                $this->generateInstallments($loan);
            }
        });
    }
}
