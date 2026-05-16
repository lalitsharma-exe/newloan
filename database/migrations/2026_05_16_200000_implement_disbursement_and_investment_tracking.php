<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Update Loans table for disbursement tracking
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('disbursed_from_account_id')->nullable()->constrained('treasury_accounts');
            $table->string('transaction_reference')->nullable();
            $table->string('payment_method')->nullable(); // Mobile Wallet, Bank Transfer
            $table->enum('funding_source_type', ['Company', 'Director'])->default('Company');
            $table->boolean('authorisation_confirmed')->default(false);
            $table->timestamp('authorisation_at')->nullable();
            $table->text('disbursement_notes')->nullable();
            $table->string('proof_of_payment_path')->nullable();
        });

        // 2. Create Director Investment Ledger
        Schema::create('director_investments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount_invested', 15, 2);
            $table->date('investment_date');
            $table->decimal('monthly_rate', 5, 4)->default(0.05); // 5% flat
            $table->decimal('interest_accrued_to_date', 15, 2)->default(0);
            $table->decimal('interest_paid_to_date', 15, 2)->default(0);
            $table->decimal('principal_repaid', 15, 2)->default(0);
            $table->enum('status', ['active', 'paid_out', 'partially_paid'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Create Internal Transfers table
        Schema::create('internal_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_type')->default('INTERNAL_TRANSFER');
            $table->foreignId('from_account_id')->constrained('treasury_accounts');
            $table->foreignId('to_account_id')->constrained('treasury_accounts');
            $table->decimal('amount', 15, 2);
            $table->date('transfer_date');
            $table->string('bank_reference')->nullable();
            $table->string('mpesa_confirmation')->nullable();
            $table->foreignId('initiated_by_user_id')->constrained('users');
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');
            $table->string('proof_of_transfer')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Update Payments table for itemized components
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('principal_portion', 15, 2)->default(0);
            $table->decimal('interest_portion', 15, 2)->default(0);
            $table->decimal('initiation_fee_portion', 15, 2)->default(0);
            $table->decimal('admin_fee_portion', 15, 2)->default(0);
            $table->decimal('penalty_portion', 15, 2)->default(0);
            $table->json('repayment_components')->nullable();
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'disbursed_from_account_id', 'transaction_reference', 'payment_method',
                'funding_source_type', 'authorisation_confirmed', 'authorisation_at',
                'disbursement_notes', 'proof_of_payment_path'
            ]);
        });
        Schema::dropIfExists('director_investments');
        Schema::dropIfExists('internal_transfers');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['principal_portion', 'interest_portion', 'initiation_fee_portion', 'admin_fee_portion', 'penalty_portion', 'repayment_components']);
        });
    }
};
