<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add MyLoan-specific fee config to loan_products
        Schema::table('loan_products', function (Blueprint $t) {
            $t->decimal('initiation_fee_rate', 5, 2)->default(40)->after('processing_fee');
            // 40% of principal (can be overridden per product)
            $t->decimal('admin_fee_fixed', 10, 2)->default(50)->after('initiation_fee_rate');
            // Fixed monthly admin fee (default M50)
            $t->string('interest_method', 20)->default('flat')->after('admin_fee_fixed');
            // 'flat' = on original principal | 'reducing' = amortised
            $t->integer('max_term_months_allowed')->default(6)->after('interest_method');
            // Enforce 1–6 month limit per spec
        });

        // Add per-installment fee breakdown to loan_installments
        Schema::table('loan_installments', function (Blueprint $t) {
            $t->decimal('initiation_fee_amount', 10, 2)->default(0)->after('interest_amount');
            $t->decimal('admin_fee_amount', 10, 2)->default(0)->after('initiation_fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $t) {
            $t->dropColumn(['initiation_fee_rate', 'admin_fee_fixed', 'interest_method', 'max_term_months_allowed']);
        });
        Schema::table('loan_installments', function (Blueprint $t) {
            $t->dropColumn(['initiation_fee_amount', 'admin_fee_amount']);
        });
    }
};
