<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add disbursement_reference + written_off status to loans
        Schema::table('loans', function (Blueprint $t) {
            $t->string('disbursement_reference', 50)->nullable()->after('loan_number');
            $t->string('disbursement_method', 50)->nullable()->after('payout_method');
            $t->string('disbursement_phone', 30)->nullable()->after('disbursement_method');
            $t->string('disbursement_provider', 50)->nullable()->after('disbursement_phone');
            // written_off was missing from status enum — add via raw if needed
        });

        // 2. Add extra expense columns to affordability_assessments
        Schema::table('affordability_assessments', function (Blueprint $t) {
            $t->decimal('education', 12, 2)->default(0)->after('other_expenses');
            $t->decimal('communication', 12, 2)->default(0)->after('education');
            $t->decimal('other_insurance', 12, 2)->default(0)->after('communication');
            $t->decimal('medical', 12, 2)->default(0)->after('other_insurance');
            $t->decimal('other_loan_repayments', 12, 2)->default(0)->after('medical');
            $t->decimal('family_support', 12, 2)->default(0)->after('other_loan_repayments');
            $t->decimal('entertainment', 12, 2)->default(0)->after('family_support');
        });

        // 3. Add assigned_officer_id + national_id to users (for borrower records)
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete()->after('role');
            $t->string('national_id', 50)->nullable()->after('phone');
            $t->date('date_of_birth')->nullable()->after('national_id');
            $t->string('address', 500)->nullable()->after('date_of_birth');
        });

        // 4. Make email nullable on users (was unique but not nullable)
        Schema::table('users', function (Blueprint $t) {
            $t->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $t) {
            $t->dropColumn(['disbursement_reference', 'disbursement_method', 'disbursement_phone', 'disbursement_provider']);
        });
        Schema::table('affordability_assessments', function (Blueprint $t) {
            $t->dropColumn(['education','communication','other_insurance','medical','other_loan_repayments','family_support','entertainment']);
        });
        Schema::table('users', function (Blueprint $t) {
            $t->dropForeign(['assigned_officer_id']);
            $t->dropColumn(['assigned_officer_id','national_id','date_of_birth','address']);
        });
    }
};
