<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affordability_assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('affordability_assessments', 'pension_deduction')) {
                $table->decimal('pension_deduction', 12, 2)->default(0)->after('existing_loans_deduction');
            }
            if (!Schema::hasColumn('affordability_assessments', 'insurance_deduction')) {
                $table->decimal('insurance_deduction', 12, 2)->default(0)->after('pension_deduction');
            }
            if (!Schema::hasColumn('affordability_assessments', 'subscriptions_deduction')) {
                $table->decimal('subscriptions_deduction', 12, 2)->default(0)->after('insurance_deduction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affordability_assessments', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('affordability_assessments', 'subscriptions_deduction')) {
                $columns[] = 'subscriptions_deduction';
            }
            if (Schema::hasColumn('affordability_assessments', 'insurance_deduction')) {
                $columns[] = 'insurance_deduction';
            }
            if (Schema::hasColumn('affordability_assessments', 'pension_deduction')) {
                $columns[] = 'pension_deduction';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
