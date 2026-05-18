<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Investors Table
        Schema::create('investors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('investor_type', ['MD', 'PUBLIC']);
            $table->string('full_name');
            $table->string('id_number', 50);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Investments Table
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('investor_id')->constrained('investors')->onDelete('cascade');
            $table->string('contract_ref', 50)->unique();
            $table->bigInteger('principal_cents');
            $table->date('investment_date');
            $table->decimal('interest_rate', 5, 4); // 0.0500 or 0.0150
            $table->date('interest_start_date');
            $table->date('maturity_date'); // 31 Dec of year
            $table->integer('total_months');
            $table->bigInteger('monthly_interest_cents');
            $table->bigInteger('total_interest_cents');
            $table->bigInteger('total_repayable_cents');
            $table->enum('status', ['active', 'termination_pending', 'terminated', 'matured', 'repaid', 'cancelled'])->default('active');
            $table->string('contract_url', 500)->nullable();
            $table->foreignId('treasury_account_id')->nullable()->constrained('treasury_accounts');
            $table->timestamp('termination_requested_at')->nullable();
            $table->date('termination_notice_expiry')->nullable();
            $table->foreignId('termination_approved_by')->nullable()->constrained('users');
            $table->bigInteger('termination_fee_cents')->nullable();
            $table->bigInteger('net_payout_cents')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->timestamp('repaid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Investment Accruals Table
        Schema::create('investment_accruals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('investment_id')->constrained('investments')->onDelete('cascade');
            $table->date('accrual_date'); // last calendar day of the month
            $table->bigInteger('interest_cents');
            $table->enum('status', ['pending', 'posted', 'forfeited'])->default('pending');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_accruals');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('investors');
    }
};
