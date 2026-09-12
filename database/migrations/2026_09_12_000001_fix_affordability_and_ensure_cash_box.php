<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TreasuryAccount;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure all affordability assessment columns are nullable with default 0
        if (Schema::hasTable('affordability_assessments')) {
            $cols = [
                'monthly_earnings',
                'tax_deduction',
                'existing_loans_deduction',
                'pension_deduction',
                'insurance_deduction',
                'subscriptions_deduction',
                'other_deductions',
                'net_salary',
                'transport',
                'groceries',
                'utilities',
                'rent',
                'education',
                'communication',
                'other_insurance',
                'medical',
                'other_loan_repayments',
                'family_support',
                'entertainment',
                'other_expenses',
                'total_living_expenses',
                'disposable_income',
                'suggested_loan_amount',
                'max_loan_amount',
            ];

            Schema::table('affordability_assessments', function (Blueprint $table) use ($cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn('affordability_assessments', $col)) {
                        $table->decimal($col, 12, 2)->nullable()->default(0)->change();
                    } else {
                        $table->decimal($col, 12, 2)->nullable()->default(0);
                    }
                }
            });
        }

        // 2. Ensure treasury_accounts has is_director_owned column
        if (Schema::hasTable('treasury_accounts')) {
            if (!Schema::hasColumn('treasury_accounts', 'is_director_owned')) {
                Schema::table('treasury_accounts', function (Blueprint $table) {
                    $table->boolean('is_director_owned')->default(false)->after('is_active');
                });
            }

            // 3. Ensure Cash Box account exists and is active
            TreasuryAccount::updateOrCreate(
                ['name' => 'Cash Box'],
                [
                    'type'              => 'cash_float',
                    'institution'       => 'Office Safe',
                    'balance'           => 0.00,
                    'is_active'         => true,
                    'is_director_owned' => false,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive reverse
    }
};
