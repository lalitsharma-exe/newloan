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

    private function createLoanFromApplication(LoanApplication $app): Loan
    {
        $amount  = $app->approved_amount;
        $rate    = $app->approved_interest_rate / 100;
        $term    = $app->approved_term;
        $product = $app->loanProduct;
        $fee     = $product?->processing_fee_type === 'percentage'
                     ? round($amount * $product->processing_fee / 100, 2)
                     : ($product?->processing_fee ?? 0);

        // Monthly amortised payment
        $monthly = $rate > 0
            ? $amount * ($rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
            : $amount / $term;
        $monthly = round($monthly, 2);

        $disbDate = Carbon::parse($app->disbursement_date);

        $loan = Loan::create([
            'loan_number'         => 'LN-' . strtoupper(Str::random(8)),
            'user_id'             => $app->user_id,
            'loan_product_id'     => $app->loan_product_id,
            'application_id'      => $app->id,
            'principal_amount'    => $amount,
            'interest_rate'       => $app->approved_interest_rate,
            'term_months'         => $term,
            'total_amount'        => round($monthly * $term, 2),
            'outstanding_balance' => $amount,
            'monthly_installment' => $monthly,
            'processing_fee'      => $fee,
            'status'              => 'active',
            'disbursement_date'   => $disbDate->toDateString(),
            'first_payment_date'  => $disbDate->copy()->addMonth()->startOfMonth()->toDateString(),
            'maturity_date'       => $disbDate->copy()->addMonths($term)->toDateString(),
            'payout_method'       => $app->payout_method,
            'collection_method'   => $app->collection_method,
        ]);

        // Generate installments
        $balance  = $amount;
        $payDate  = $disbDate->copy()->addMonth()->startOfMonth();

        for ($i = 1; $i <= $term; $i++) {
            $interest  = round($balance * $rate, 2);
            $principal = min(round($monthly - $interest, 2), $balance);
            $balance   = max(0, round($balance - $principal, 2));

            LoanInstallment::create([
                'loan_id'            => $loan->id,
                'installment_number' => $i,
                'due_date'           => $payDate->copy()->toDateString(),
                'principal_amount'   => $principal,
                'interest_amount'    => $interest,
                'total_amount'       => $principal + $interest,
                'paid_amount'        => 0,
                'outstanding_amount' => $principal + $interest,
                'status'             => 'pending',
            ]);

            $payDate->addMonth();
        }

        $app->update(['status' => 'disbursed']);

        return $loan;
    }
}