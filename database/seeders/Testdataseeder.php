<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\LoanProduct;
use App\Models\LoanApplication;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\Payment;
use App\Models\ApplicationNote;
use App\Models\Document;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding borrowers...');
        $borrowers = $this->seedBorrowers();

        $this->command->info('Seeding applications & loans...');
        $this->seedApplications($borrowers);

        $this->command->info('✓ All test data seeded!');
    }

    // ─────────────────────────────────────────────────────────
    // BORROWERS
    // ─────────────────────────────────────────────────────────
    private function seedBorrowers(): array
    {
        $people = [
            ['name' => 'Mary Johnson',   'email' => 'mary@test.com',   'phone' => '26622111001'],
            ['name' => 'Peter Brown',    'email' => 'peter@test.com',  'phone' => '26622111002'],
            ['name' => 'Susan White',    'email' => 'susan@test.com',  'phone' => '26622111003'],
            ['name' => 'James Mokoena', 'email' => 'james@test.com',  'phone' => '26622111004'],
            ['name' => 'Lineo Ntšekhe', 'email' => 'lineo@test.com',  'phone' => '26622111005'],
            ['name' => 'David Lebesa',  'email' => 'david@test.com',  'phone' => '26622111006'],
            ['name' => 'Mpho Sefali',   'email' => 'mpho@test.com',   'phone' => '26622111007'],
            ['name' => 'Palesa Mokete', 'email' => 'palesa@test.com', 'phone' => '26622111008'],
        ];

        return array_map(fn($d) => User::firstOrCreate(['email' => $d['email']], [
            'name'              => $d['name'],
            'phone'             => $d['phone'],
            'role'              => 'borrower',
            'password'          => Hash::make('Password@123'),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]), $people);
    }

    // ─────────────────────────────────────────────────────────
    // APPLICATIONS
    // ─────────────────────────────────────────────────────────
    private function seedApplications(array $borrowers): void
    {
        $products = LoanProduct::all();
        if ($products->isEmpty()) {
            $this->command->warn('No products — run AdminSeeder first!');
            return;
        }

        $admin   = User::where('role', 'admin')->first();
        $officer = User::where('role', 'loan_officer')->first();
        $noteBy  = $officer ?? $admin;

        $purposes = ['Home Renovation', 'Medical Emergency', 'Education Fees', 'Business Capital', 'Debt Consolidation'];

        // [borrower_idx, status, amount, term_months, months_ago]
        $scenarios = [
            [0, 'submitted',      5000, 12, 0],
            [1, 'submitted',      3500,  6, 0],
            [2, 'under_review',   8000, 24, 1],
            [3, 'under_review',   6000, 18, 1],
            [4, 'info_requested', 4000, 12, 2],
            [5, 'on_hold',        7000, 24, 2],
            [6, 'approved',      10000, 36, 3],
            [7, 'approved',       5500, 12, 3],
            [0, 'declined',       2000,  6, 4],
            [1, 'disbursed',     12000, 36, 6],  // active loan
            [2, 'disbursed',      8000, 24, 4],  // active loan
            [3, 'disbursed',      5000, 12, 3],  // active loan
            [4, 'disbursed',      6000, 18, 8],  // overdue loan
        ];

        foreach ($scenarios as $idx => [$bi, $status, $amount, $term, $monthsAgo]) {
            $borrower   = $borrowers[$bi];
            $product    = $products->random();
            $appNum     = 'APP-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);

            if (LoanApplication::where('application_number', $appNum)->exists()) continue;

            $isApproved  = in_array($status, ['approved', 'disbursed']);
            $hasDecision = in_array($status, ['approved', 'declined', 'disbursed', 'on_hold']);
            $createdAt   = Carbon::now()->subMonths($monthsAgo)->subDays(rand(1, 5));
            $submittedAt = $createdAt->copy()->addHours(rand(1, 12));
            $reviewedAt  = $hasDecision ? $createdAt->copy()->addDays(2) : null;
            $decidedAt   = $isApproved  ? $createdAt->copy()->addDays(3) : null;
            $nameParts   = explode(' ', $borrower->name, 2);

            $app = LoanApplication::create([
                'application_number'  => $appNum,
                'user_id'             => $borrower->id,
                'loan_product_id'     => $product->id,
                'assigned_officer_id' => $officer?->id,
                'status'              => $status,
                'step'                => 9,

                // personal info fields on application
                'first_name'          => $nameParts[0],
                'surname'             => $nameParts[1] ?? '',
                'cell_number'         => $borrower->phone,
                'email'               => $borrower->email,

                // loan request
                'requested_amount'    => $amount,
                'requested_term'      => $term,
                'loan_purpose'        => $purposes[array_rand($purposes)],

                // approved
                'approved_amount'           => $isApproved ? $amount : null,
                'approved_term'             => $isApproved ? $term   : null,
                'approved_interest_rate'    => $isApproved ? $product->interest_rate : null,

                'risk_score'          => rand(60, 95),
                'decline_reason'      => $status === 'declined'        ? 'Insufficient income to service the requested loan amount.' : null,
                'admin_notes'         => $status === 'info_requested'  ? 'Please provide payslip and bank statements for the last 3 months.' : null,

                'submitted_at'        => $submittedAt,
                'reviewed_at'         => $reviewedAt,
                'decided_at'          => $decidedAt,

                'created_at'          => $createdAt,
                'updated_at'          => $createdAt,
            ]);

            // application_notes — column is `created_by`, NOT `user_id`
            ApplicationNote::create([
                'application_id' => $app->id,
                'created_by'     => $noteBy?->id,   // ← correct column
                'type'           => 'review',
                'content'        => $this->note($status),
                'is_internal'    => true,
                'created_at'     => $createdAt->copy()->addDay(),
                'updated_at'     => $createdAt->copy()->addDay(),
            ]);

            // documents — columns: filename, original_name, path, size (NOT file_name / file_path)
            Document::create([
                'application_id' => $app->id,
                'user_id'        => $borrower->id,
                'type'           => 'national_id',
                'filename'       => 'national_id_'.$borrower->id.'.pdf',     // ← correct column
                'original_name'  => 'National_ID_'.$borrower->name.'.pdf',  // ← correct column
                'path'           => 'documents/national_id_'.$borrower->id.'.pdf', // ← correct column
                'size'           => rand(100000, 500000),                    // ← correct column
                'mime_type'      => 'application/pdf',
                'status'         => $isApproved ? 'verified' : 'pending',
                'verified_by'    => $isApproved ? $officer?->id : null,
                'verified_at'    => $isApproved ? $decidedAt   : null,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);

            if ($status === 'disbursed') {
                $this->createLoan($app, $borrower, $product, $monthsAgo, $idx, $admin);
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // LOANS + INSTALLMENTS + PAYMENTS
    // ─────────────────────────────────────────────────────────
    private function createLoan(LoanApplication $app, User $borrower, LoanProduct $product, int $monthsAgo, int $idx, ?User $admin): void
    {
        $principal   = $app->approved_amount;
        $rate        = $product->interest_rate / 100;
        $term        = $app->approved_term;
        $disbursedAt = Carbon::now()->subMonths($monthsAgo)->addDays(5);
        $isOverdue   = $idx === 12;

        $monthlyPayment = $rate > 0
            ? $principal * ($rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
            : $principal / $term;
        $monthlyPayment = round($monthlyPayment, 2);
        $totalAmount    = round($monthlyPayment * $term, 2);

        $loanNum = 'LN-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT);
        if (Loan::where('loan_number', $loanNum)->exists()) return;

        $loan = Loan::create([
            'loan_number'         => $loanNum,
            'application_id'      => $app->id,
            'user_id'             => $borrower->id,
            'loan_product_id'     => $app->loan_product_id,
            'status'              => $isOverdue ? 'overdue' : 'active',
            'principal_amount'    => $principal,
            'interest_rate'       => $product->interest_rate,
            'term_months'         => $term,
            'total_amount'        => $totalAmount,
            'outstanding_balance' => $principal,
            'monthly_installment' => $monthlyPayment,
            'processing_fee'      => round($principal * 0.03, 2),
            'disbursement_date'   => $disbursedAt->toDateString(),
            'maturity_date'       => $disbursedAt->copy()->addMonths($term)->toDateString(),
            'first_payment_date'  => $disbursedAt->copy()->addMonth()->startOfMonth()->toDateString(),
            'last_payment_date'   => $disbursedAt->copy()->addMonths($term)->startOfMonth()->toDateString(),
            'created_at'          => $disbursedAt,
            'updated_at'          => $disbursedAt,
        ]);

        // Installments
        $balance   = $principal;
        $rows      = [];
        $totalPaid = 0;

        for ($m = 1; $m <= $term; $m++) {
            $interest      = round($balance * $rate, 2);
            $principalPart = min(round($monthlyPayment - $interest, 2), $balance);
            $balance       = max(0, round($balance - $principalPart, 2));
            $dueDate       = $disbursedAt->copy()->addMonths($m)->startOfMonth();
            $isPast        = $dueDate->isPast();

            if ($isOverdue) {
                $paymentsMade = max(0, $monthsAgo - 2);
                if ($m <= $paymentsMade)  { $instStatus = 'paid';    $paidAt = $dueDate->copy()->addDays(rand(0, 2)); }
                elseif ($isPast)          { $instStatus = 'overdue'; $paidAt = null; }
                else                      { $instStatus = 'pending'; $paidAt = null; }
            } else {
                $instStatus = $isPast ? 'paid' : 'pending';
                $paidAt     = $isPast ? $dueDate->copy()->addDays(rand(0, 3)) : null;
            }

            $rows[] = [
                'loan_id'            => $loan->id,
                'installment_number' => $m,
                'due_date'           => $dueDate->format('Y-m-d'),
                'principal_amount'   => $principalPart,
                'interest_amount'    => $interest,
                'total_amount'       => $monthlyPayment,
                'paid_amount'        => $instStatus === 'paid' ? $monthlyPayment : 0,
                'outstanding_amount' => $instStatus === 'paid' ? 0 : $monthlyPayment,
                'status'             => $instStatus,
                'paid_at'            => $paidAt?->format('Y-m-d H:i:s'),
                'created_at'         => $disbursedAt->format('Y-m-d H:i:s'),
                'updated_at'         => ($paidAt ?? $disbursedAt)->format('Y-m-d H:i:s'),
            ];

            if ($instStatus === 'paid') $totalPaid += $monthlyPayment;
        }

        LoanInstallment::insert($rows);

        // Payments for paid installments
        $paidInstallments = LoanInstallment::where('loan_id', $loan->id)->where('status', 'paid')->get();
        foreach ($paidInstallments as $inst) {
            Payment::create([
                'payment_reference' => 'PAY-' . strtoupper(Str::random(8)),
                'loan_id'           => $loan->id,
                'user_id'           => $borrower->id,
                'amount'            => $inst->paid_amount,
                'method'            => collect(['bank_transfer', 'mobile_money', 'cash'])->random(),
                'status'            => 'verified',
                'notes'             => 'Installment #' . $inst->installment_number,
                'is_manual'         => false,
                'verified_by'       => $admin?->id,
                'verified_at'       => $inst->paid_at,
                'created_at'        => $inst->paid_at,
                'updated_at'        => $inst->paid_at,
            ]);
        }

        // One pending payment
        Payment::create([
            'payment_reference' => 'PAY-' . strtoupper(Str::random(8)),
            'loan_id'           => $loan->id,
            'user_id'           => $borrower->id,
            'amount'            => $monthlyPayment,
            'method'            => 'mobile_money',
            'status'            => 'pending',
            'notes'             => 'Pending verification',
            'is_manual'         => false,
            'created_at'        => now()->subDays(rand(1, 3)),
            'updated_at'        => now()->subDays(rand(1, 3)),
        ]);

        $loan->update(['outstanding_balance' => max(0, round($principal - $totalPaid, 2))]);
    }

    private function note(string $status): string
    {
        return match($status) {
            'submitted'      => 'Application received and queued for officer review.',
            'under_review'   => 'Documents verified. Affordability check in progress.',
            'info_requested' => 'Applicant contacted. Awaiting payslip and bank statement.',
            'on_hold'        => 'On hold pending employer confirmation letter.',
            'approved'       => 'All checks passed. Approved and ready for disbursement.',
            'declined'       => 'Application declined due to insufficient income. Borrower notified.',
            'disbursed'      => 'Loan disbursed to borrower account. Agreement signed.',
            default          => 'Application created.',
        };
    }
}