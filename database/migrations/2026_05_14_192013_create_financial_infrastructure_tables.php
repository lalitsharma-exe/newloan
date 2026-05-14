<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Treasury Accounts: Bank, Wallets, Cash
        Schema::create('treasury_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Standard Lesotho Bank"
            $table->enum('type', ['bank', 'mobile_wallet', 'cash_float', 'investment']);
            $table->string('institution')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency')->default('LSL');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Treasury Transactions: The Centralized Ledger
        Schema::create('treasury_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treasury_account_id')->constrained('treasury_accounts')->onDelete('cascade');
            $table->enum('type', ['collection', 'disbursement', 'expense', 'transfer_in', 'transfer_out', 'funding', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['in', 'out']);
            $table->string('reference')->nullable(); // External ref or internal tx id
            $table->string('description')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->foreignId('loan_id')->nullable()->constrained('loans')->onDelete('set null');
            $table->foreignId('expense_id')->nullable(); // Linked later if it's an expense
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. Operating Expenses
        Schema::create('operating_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->enum('category', [
                'STAFF', 'TECHNOLOGY', 'OPERATIONS', 'MARKETING', 
                'COLLECTIONS', 'FINANCE', 'COMPLIANCE', 'CREDIT_RISK', 'BAD_DEBT'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_period')->nullable(); // monthly, weekly
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('treasury_account_id')->nullable()->constrained('treasury_accounts')->onDelete('set null');
            $table->timestamps();
        });

        // 4. Repayment Forecasts
        Schema::create('repayment_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Borrower
            $table->decimal('expected_amount', 15, 2);
            $table->decimal('probability', 5, 2)->default(1.00); // 0.00 to 1.00
            $table->date('forecast_date');
            $table->timestamps();
        });

        // 5. Liquidity Snapshots (Daily Positions)
        Schema::create('liquidity_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date');
            $table->decimal('cash_available', 15, 2);
            $table->decimal('expected_inflows_30d', 15, 2);
            $table->decimal('expected_outflows_30d', 15, 2);
            $table->decimal('net_liquidity', 15, 2);
            $table->integer('runway_days')->nullable();
            $table->decimal('daily_burn_rate', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidity_snapshots');
        Schema::dropIfExists('repayment_forecasts');
        Schema::dropIfExists('operating_expenses');
        Schema::dropIfExists('treasury_transactions');
        Schema::dropIfExists('treasury_accounts');
    }
};
