<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanApplication, LoanInstallment, LoanProduct, User};
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApplicationService
{
    public function getPaginated(array $filters, int $perPage = 15)
    {
        $q = LoanApplication::with(['user', 'loanProduct', 'assignedOfficer', 'agent.agentProfile']);

        if (isset($filters['status']) && $filters['status'] === 'draft') {
            $q->where('status', 'draft');
        } else {
            $q->where('status', '!=', 'draft');
            if (!empty($filters['status']))    $q->where('status', $filters['status']);
        }
        if (!empty($filters['product']))   $q->where('loan_product_id', $filters['product']);
        if (!empty($filters['date_from'])) $q->whereDate('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('created_at', '<=', $filters['date_to']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($q) use ($s) {
                $q->where('application_number', 'like', "%{$s}%")
                  ->orWhere('first_name',  'like', "%{$s}%")
                  ->orWhere('surname',     'like', "%{$s}%")
                  ->orWhere('cell_number', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        return $q->latest()->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total'          => LoanApplication::where('status', '!=', 'draft')->count(),
            'pending'        => LoanApplication::whereIn('status', ['submitted','under_review','info_requested','on_hold'])->count(),
            'drafts'         => LoanApplication::where('status', 'draft')->count(),
            'approved_today' => LoanApplication::whereDate('decided_at', today())->where('status', 'approved')->count(),
            'declined_today' => LoanApplication::whereDate('decided_at', today())->where('status', 'declined')->count(),
        ];
    }

    public function approve(LoanApplication $app, array $data, User $admin): Loan
    {
        // Validate against loan product limits
        $product = $app->loanProduct;
        if ($product) {
            $amount = (float) $data['approved_amount'];
            $term   = (int)   $data['approved_term'];

            if ($amount < $product->min_amount || $amount > $product->max_amount) {
                throw new \InvalidArgumentException(
                    "Approved amount M{$amount} is outside product limits (M{$product->min_amount} – M{$product->max_amount})."
                );
            }
            if ($term < $product->min_term_months || $term > $product->max_term_months) {
                throw new \InvalidArgumentException(
                    "Approved term {$term} months is outside product limits ({$product->min_term_months} – {$product->max_term_months} months)."
                );
            }
        }

        $app->update([
            'status'                 => 'approved',
            'approved_amount'        => $data['approved_amount'],
            'approved_term'          => $data['approved_term'],
            'approved_interest_rate' => $data['interest_rate'],
            'disbursement_date'      => $data['disbursement_date'],
            'decided_at'             => now(),
        ]);

        if (!empty($data['notes'])) {
            $app->notes()->create([
                'created_by'  => $admin->id,
                'type'        => 'approval',
                'content'     => $data['notes'],
                'is_internal' => true,
            ]);
        }

        $loan = $this->createLoanFromApplication($app);

        // Send Approval SMS
        try {
            $app->user->notify(new \App\Notifications\LoanApprovedSms($app));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send approval SMS for app {$app->id}: " . $e->getMessage());
        }

        return $loan;
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

        // Send Decline SMS
        try {
            $app->user->notify(new \App\Notifications\LoanDeclinedSms($app));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send decline SMS for app {$app->id}: " . $e->getMessage());
        }
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

    // ── Calculate monthly installment ─────────────────────────────────────────
    public function calcMonthly(float $principal, float $ratePercent, int $term, ?object $product = null): float
    {
        if ($term <= 0) return 0.0;
        $rate            = $ratePercent / 100;
        $initiationRate  = ($product?->initiation_fee_rate ?? 0) / 100;
        $adminPerMonth   = (float) ($product?->admin_fee_fixed ?? 0);
        $method          = $product?->interest_method ?? 'reducing';

        $totalInitiation = round($principal * $initiationRate, 2);
        $totalAdmin      = $adminPerMonth * $term;

        if ($method === 'reducing') {
            $pmt = ($rate > 0)
                ? ($principal * $rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
                : ($principal / $term);
            $monthly = $pmt + $adminPerMonth + ($totalInitiation / $term);
            return round($monthly, 2);
        }

        $totalInterest   = round($principal * $rate * $term, 2);
        $totalRepay      = $principal + $totalInterest + $totalInitiation + $totalAdmin;
        return round($totalRepay / $term, 2);
    }

    // ── Affordability check with actionable message ────────────────────────────
    public function checkAffordability(LoanApplication $app): array
    {
        $assessment  = $app->affordability;
        $disposable  = (float) ($assessment?->disposable_income ?? 0);
        $netSalary   = (float) ($assessment?->net_salary ?? 0);

        // Use 30% of net salary as max allowed (configurable via settings)
        $maxAllowed  = round($disposable * 1.0, 2); // 100% of disposable income
        $product     = $app->loanProduct;
        $principal   = (float) ($app->approved_amount ?? $app->requested_amount ?? 0);
        $rate        = (float) ($app->approved_interest_rate ?? $product?->interest_rate ?? 15);
        $term        = (int)   ($app->approved_term ?? $app->requested_term ?? 1);
        $monthly     = $principal > 0 ? $this->calcMonthly($principal, $rate, $term, $product) : 0;

        $passes = $disposable > 0 && $monthly <= $disposable;

        $warning = null;
        if ($disposable > 0 && $monthly > $disposable) {
            // Calculate what amount would be affordable
            $affordableMonthly = $disposable;
            // Reverse-calculate affordable principal from affordable monthly
            $adminPerMonth   = (float) ($product?->admin_fee_fixed ?? 16);
            $initiationRate  = ($product?->initiation_fee_rate ?? 0) / 100;
            $rateDecimal     = $rate / 100;
            // monthly = principal * (1 + rate*term + initiationRate) / term + adminPerMonth
            // => principal = (monthly - adminPerMonth) * term / (1 + rate*term + initiationRate)
            $divisor         = 1 + ($rateDecimal * $term) + $initiationRate;
            $affordablePrincipal = $divisor > 0
                ? round(($affordableMonthly - $adminPerMonth) * $term / $divisor, 2)
                : 0;
            $affordablePrincipal = max(0, $affordablePrincipal);

            // Calculate what term would make it affordable
            // Solve: monthly = principal * (1 + rate*t + initiationRate) / t + adminPerMonth
            // => monthly - adminPerMonth = principal * (1 + initiationRate) / t + principal * rate
            // => t = principal * (1 + initiationRate) / (monthly - adminPerMonth - principal * rate)
            $denominator    = $affordableMonthly - $adminPerMonth - ($principal * $rateDecimal);
            $affordableTerm = $denominator > 0
                ? (int) ceil($principal * (1 + $initiationRate) / $denominator)
                : null;

            $warning = sprintf(
                '⚠️ Monthly installment M%s exceeds affordability limit of M%s. You can either: Decrease the loan amount requested (affordable amount ≈ M%s) or Increase the loan term to reduce the monthly installment%s.',
                number_format($monthly, 2),
                number_format($disposable, 2),
                number_format(max(0, $affordablePrincipal), 2),
                $affordableTerm ? " (suggested term: {$affordableTerm} months)" : ''
            );
        }

        return [
            'net_salary'          => $netSalary,
            'disposable_income'   => $disposable,
            'max_allowed'         => $maxAllowed,
            'monthly'             => $monthly,
            'passes'              => $passes,
            'warning'             => $warning,
            'assessment'          => $assessment,
        ];
    }

    // ── Validate loan amount/term against product rules ────────────────────────
    public function validateProductLimits(float $amount, int $term, LoanProduct $product): ?string
    {
        if ($amount < $product->min_amount) {
            return "Loan amount M{$amount} is below the minimum of M{$product->min_amount} for {$product->name}.";
        }
        if ($amount > $product->max_amount) {
            return "Loan amount M{$amount} exceeds the maximum of M{$product->max_amount} for {$product->name}.";
        }
        if ($term < $product->min_term_months) {
            return "Loan term {$term} months is below the minimum of {$product->min_term_months} months for {$product->name}.";
        }
        if ($term > $product->max_term_months) {
            return "Loan term {$term} months exceeds the maximum of {$product->max_term_months} months for {$product->name}.";
        }
        return null;
    }

    private function createLoanFromApplication(LoanApplication $app): Loan
    {
        $principal = (float) $app->approved_amount;
        $term      = (int)   $app->approved_term;
        $product   = $app->loanProduct;

        $monthlyRate     = (float) $app->approved_interest_rate / 100;
        $initiationRate  = ($product?->initiation_fee_rate ?? 0) / 100;
        $totalInitiation = round($principal * $initiationRate, 2);
        $adminPerMonth   = (float) ($product?->admin_fee_fixed ?? 0);
        $totalAdmin      = $adminPerMonth * $term;
        $method          = $product?->interest_method ?? 'reducing';

        if ($method === 'reducing') {
            $monthly    = $this->calcMonthly($principal, (float)$app->approved_interest_rate, $term, $product);
            $totalRepay = round($monthly * $term, 2);
        } else {
            $totalInterest = round($principal * $monthlyRate * $term, 2);
            $totalRepay    = $principal + $totalInterest + $totalInitiation + $totalAdmin;
            $monthly       = round($totalRepay / $term, 2);
        }

        $disbDate   = Carbon::parse($app->disbursement_date);
        $nextId     = (Loan::max('id') ?? 0) + 1;
        $loanNumber = 'LN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        $loan = Loan::create([
            'loan_number'         => $loanNumber,
            'user_id'             => $app->user_id,
            'loan_product_id'     => $app->loan_product_id,
            'application_id'      => $app->id,
            'principal_amount'    => $principal,
            'interest_rate'       => $app->approved_interest_rate,
            'term_months'         => $term,
            'total_amount'        => $totalRepay,
            'outstanding_balance' => $totalRepay,
            'monthly_installment' => $monthly,
            'processing_fee'      => $totalInitiation, // kept for DB compatibility; labelled as Initiation Fee in UI
            'status'              => 'approved',        // stays 'approved' until disbursed
            'payout_method'       => $app->payout_method,
            'collection_method'   => $app->collection_method,
            'salary_payday'       => $app->salary_payday,
        ]);

        // Installments are created at disbursement time, not approval
        return $loan;
    }
}
