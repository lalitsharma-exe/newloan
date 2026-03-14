<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanApplication, LoanInstallment, User};
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApplicationService
{
    public function getPaginated(array $filters, int $perPage = 15)
    {
        $q = LoanApplication::with(['user', 'loanProduct', 'assignedOfficer'])
            ->where('status', '!=', 'draft');

        if (!empty($filters['status']))   $q->where('status', $filters['status']);
        if (!empty($filters['product']))  $q->where('loan_product_id', $filters['product']);
        if (!empty($filters['date_from'])) $q->whereDate('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('created_at', '<=', $filters['date_to']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($q) use ($s) {
                $q->where('application_number', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('surname', 'like', "%{$s}%")
                  ->orWhere('cell_number', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%"));
            });
        }

        return $q->latest()->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total'          => LoanApplication::where('status', '!=', 'draft')->count(),
            'pending'        => LoanApplication::whereIn('status', ['submitted','under_review','info_requested','on_hold'])->count(),
            'approved_today' => LoanApplication::whereDate('decided_at', today())->where('status', 'approved')->count(),
            'declined_today' => LoanApplication::whereDate('decided_at', today())->where('status', 'declined')->count(),
        ];
    }

    public function approve(LoanApplication $app, array $data, User $admin): Loan
    {
        $app->update([
            'status'               => 'approved',
            'approved_amount'      => $data['approved_amount'],
            'approved_term'        => $data['approved_term'],
            'approved_interest_rate' => $data['interest_rate'],
            'disbursement_date'    => $data['disbursement_date'],
            'decided_at'           => now(),
        ]);

        if (!empty($data['notes'])) {
            $app->notes()->create([
                'created_by'  => $admin->id,
                'type'        => 'approval',
                'content'     => $data['notes'],
                'is_internal' => true,
            ]);
        }

        return $this->createLoanFromApplication($app);
    }

    public function decline(LoanApplication $app, string $reason, User $admin): void
    {
        $app->update(['status' => 'declined', 'decline_reason' => $reason, 'decided_at' => now()]);
        $app->notes()->create([
            'created_by'  => $admin->id,
            'type'        => 'decision',
            'content'     => "Declined: {$reason}",
            'is_internal' => true,
        ]);
    }

    public function hold(LoanApplication $app, string $reason, User $admin): void
    {
        $app->update(['status' => 'on_hold', 'admin_notes' => $reason]);
        $app->notes()->create([
            'created_by'  => $admin->id,
            'type'        => 'status',
            'content'     => "Placed on hold: {$reason}",
            'is_internal' => true,
        ]);
    }

    public function requestInfo(LoanApplication $app, string $message, User $admin): void
    {
        $app->update(['status' => 'info_requested', 'admin_notes' => $message]);
        $app->notes()->create([
            'created_by'  => $admin->id,
            'type'        => 'info_request',
            'content'     => $message,
            'is_internal' => false,
        ]);
    }

    public function overrideLoanTerms(LoanApplication $app, array $data, User $admin): void
    {
        $app->update([
            'requested_amount'       => $data['loan_amount'],
            'approved_interest_rate' => $data['interest_rate'],
            'requested_term'         => $data['term_months'],
        ]);
        $app->notes()->create([
            'created_by'  => $admin->id,
            'type'        => 'override',
            'content'     => "Terms overridden — Amount: {$data['loan_amount']}, Rate: {$data['interest_rate']}%, Term: {$data['term_months']} months",
            'is_internal' => true,
        ]);
    }

    // ── Public helper: calculate monthly installment for preview/affordability ──
    public function calcMonthly(float $principal, float $ratePercent, int $term, ?object $product = null): float
    {
        $rate           = $ratePercent / 100;
        $initiationRate = ($product->initiation_fee_rate ?? 40) / 100;
        $adminPerMonth  = $product->admin_fee_fixed ?? 50;

        $totalInterest   = round($principal * $rate * $term, 2);
        $totalInitiation = round($principal * $initiationRate, 2);
        $totalAdmin      = $adminPerMonth * $term;
        $totalRepay      = $principal + $totalInterest + $totalInitiation + $totalAdmin;

        return round($totalRepay / $term, 2);
    }

    // ── Public helper: affordability check (30% rule) ─────────────────────────
    public function checkAffordability(LoanApplication $app): array
    {
        $assessment    = $app->affordabilityAssessment;
        $netSalary     = (float) ($assessment?->net_salary ?? 0);
        $maxAllowed    = round($netSalary * 0.30, 2);
        $product       = $app->loanProduct;
        $principal     = (float) ($app->approved_amount ?? $app->requested_amount ?? 0);
        $rate          = (float) ($app->approved_interest_rate ?? $product?->interest_rate ?? 15);
        $term          = (int)   ($app->approved_term ?? $app->requested_term ?? 1);
        $monthly       = $principal > 0 ? $this->calcMonthly($principal, $rate, $term, $product) : 0;

        return [
            'net_salary'  => $netSalary,
            'max_allowed' => $maxAllowed,
            'monthly'     => $monthly,
            'passes'      => ($netSalary > 0 && $monthly <= $maxAllowed),
            'warning'     => ($netSalary > 0 && $monthly > $maxAllowed)
                ? "Monthly installment M".number_format($monthly,2)." exceeds the 30% affordability limit of M".number_format($maxAllowed,2)
                : null,
        ];
    }

    private function createLoanFromApplication(LoanApplication $app): Loan
    {
        $principal = (float) $app->approved_amount;
        $term      = (int)   $app->approved_term;
        $product   = $app->loanProduct;

        // ── FLAT INTEREST (15% per month on original principal — same every month) ──
        $monthlyRate     = (float) $app->approved_interest_rate / 100; // e.g. 0.15
        $totalInterest   = round($principal * $monthlyRate * $term, 2);

        // ── INITIATION FEE (40% of principal — split evenly over term) ──────────
        $initiationRate  = ($product?->initiation_fee_rate ?? 40) / 100;
        $totalInitiation = round($principal * $initiationRate, 2);

        // ── ADMIN FEE (fixed M50 per month) ──────────────────────────────────────
        $adminPerMonth   = (float) ($product?->admin_fee_fixed ?? 50);
        $totalAdmin      = $adminPerMonth * $term;

        // ── TOTALS ────────────────────────────────────────────────────────────────
        $totalRepay      = $principal + $totalInterest + $totalInitiation + $totalAdmin;
        $monthly         = round($totalRepay / $term, 2);

        $disbDate = Carbon::parse($app->disbursement_date);

        // Loan number: LN-00001 format (sequential, padded)
        $nextId      = (Loan::max('id') ?? 0) + 1;
        $loanNumber  = 'LN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        $loan = Loan::create([
            'loan_number'         => $loanNumber,
            'user_id'             => $app->user_id,
            'loan_product_id'     => $app->loan_product_id,
            'application_id'      => $app->id,
            'principal_amount'    => $principal,
            'interest_rate'       => $app->approved_interest_rate,
            'term_months'         => $term,
            'total_amount'        => $totalRepay,
            'outstanding_balance' => $totalRepay,   // borrower owes full amount incl. all fees
            'monthly_installment' => $monthly,
            'processing_fee'      => $totalInitiation,
            'status'              => 'active',
            'disbursement_date'   => $disbDate->toDateString(),
            'first_payment_date'  => $disbDate->copy()->addMonth()->startOfMonth()->toDateString(),
            'maturity_date'       => $disbDate->copy()->addMonths($term)->toDateString(),
            'payout_method'       => $app->payout_method,
            'collection_method'   => $app->collection_method,
        ]);

        // ── GENERATE INSTALLMENTS ─────────────────────────────────────────────────
        // Each month: same principal slice, same flat interest, same admin, same initiation slice
        $principalPerMonth  = round($principal / $term, 2);
        $interestPerMonth   = round($principal * $monthlyRate, 2);  // FLAT — identical every month
        $initiationPerMonth = round($totalInitiation / $term, 2);

        $payDate = $disbDate->copy()->addMonth()->startOfMonth();

        for ($i = 1; $i <= $term; $i++) {
            $isLast = ($i === $term);
            // Last month absorbs any rounding cents
            $prin  = $isLast ? round($principal - $principalPerMonth * ($term - 1), 2) : $principalPerMonth;
            $init  = $isLast ? round($totalInitiation - $initiationPerMonth * ($term - 1), 2) : $initiationPerMonth;
            $total = round($prin + $interestPerMonth + $adminPerMonth + $init, 2);

            LoanInstallment::create([
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

        $app->update(['status' => 'disbursed']);

        return $loan;
    }
}
