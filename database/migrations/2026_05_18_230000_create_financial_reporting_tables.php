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
        // 1. Financial Periods Table
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_label', 50)->unique(); // e.g. "2024-03", "2024-Q1", "2024-annual"
            $table->date('period_end_date');
            $table->integer('year');
            $table->integer('quarter')->nullable(); // 1, 2, 3, 4
            $table->integer('month')->nullable(); // 1-12
            $table->enum('status', ['open', 'closed', 'audited'])->default('open');
            $table->timestamps();
        });

        // 2. FS Revenue Lines Table
        Schema::create('fs_revenue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('financial_periods')->onDelete('cascade');
            $table->string('revenue_type', 100); // interest_received, initiation_fees, admin_fees, penalties, other
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. FS Expense Lines Table
        Schema::create('fs_expense_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('financial_periods')->onDelete('cascade');
            $table->string('expense_code', 50); // e.g. EXP-001
            $table->string('expense_label', 200);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. FS PPE Register Table
        Schema::create('fs_ppe_register', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name', 200);
            $table->string('asset_class', 100); // e.g. Computer Equipment, Motor Vehicles
            $table->date('purchase_date');
            $table->decimal('cost', 15, 2);
            $table->integer('useful_life_years');
            $table->decimal('accumulated_dep', 15, 2)->default(0.00);
            $table->decimal('net_book_value', 15, 2);
            $table->enum('status', ['active', 'disposed'])->default('active');
            $table->timestamps();
        });

        // 5. FS Provisions Table
        Schema::create('fs_provisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('financial_periods')->onDelete('cascade');
            $table->string('provision_type', 100); // bad_debt, severance, other
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 6. FS Equity Movements Table
        Schema::create('fs_equity_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('financial_periods')->onDelete('cascade');
            $table->string('movement_type', 100); // capital_injection, retained_earnings, dividends, prior_year_adj
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 7. FS Cash Flow Lines Table
        Schema::create('fs_cash_flow_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('financial_periods')->onDelete('cascade');
            $table->string('line_code', 100); // direct/indirect adjustment codes
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 8. Portfolio Snapshots Table
        Schema::create('portfolio_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            $table->integer('active_loan_count');
            $table->decimal('gross_loan_book', 15, 2);
            $table->integer('disbursed_count');
            $table->decimal('disbursed_amount', 15, 2);
            $table->integer('settled_count');
            $table->decimal('settled_amount', 15, 2);
            $table->integer('written_off_count');
            $table->decimal('written_off_amount', 15, 2);
            $table->decimal('par_30_amount', 15, 2);
            $table->decimal('par_30_rate', 7, 4);
            $table->decimal('par_90_amount', 15, 2);
            $table->decimal('par_90_rate', 7, 4);
            $table->decimal('provision_balance', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_snapshots');
        Schema::dropIfExists('fs_cash_flow_lines');
        Schema::dropIfExists('fs_equity_movements');
        Schema::dropIfExists('fs_provisions');
        Schema::dropIfExists('fs_ppe_register');
        Schema::dropIfExists('fs_expense_lines');
        Schema::dropIfExists('fs_revenue_lines');
        Schema::dropIfExists('financial_periods');
    }
};
