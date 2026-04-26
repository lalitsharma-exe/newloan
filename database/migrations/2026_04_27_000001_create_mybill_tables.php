<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MyBill — Credit-based bill-payment tables.
 *
 * Completely independent of existing loan tables.
 * No modifications to any existing table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── mybill_limits: per-client credit limit ────────────────────────
        Schema::create('mybill_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_limit', 10, 2)->default(500.00);
            $table->decimal('used_amount', 10, 2)->default(0.00);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('used_amount');
        });

        // ── mybill_loans: individual bill-payment loan records ───────────
        Schema::create('mybill_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 20)->unique();          // MBILL-00001
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Bill details
            $table->decimal('bill_value', 10, 2);                 // Original bill amount
            $table->decimal('loan_amount', 10, 2);                // bill × 1.30
            $table->enum('tier', ['30', '40']);                    // Pricing tier
            $table->decimal('upfront_amount', 10, 2)->default(0); // 10% or 0
            $table->decimal('payday_amount', 10, 2);              // 120% or 140% of bill
            $table->decimal('settled_amount', 10, 2)->default(0); // How much collected so far

            // Category & provider info
            $table->enum('bill_category', ['electricity', 'airtime', 'insurance', 'ticket']);
            $table->string('provider_ref')->nullable();           // CPay transaction ID
            $table->text('provider_response')->nullable();        // Raw JSON from CPay
            $table->string('cpay_txn_id')->nullable();            // CPay's cPayTransactionId

            // Category-specific identifiers
            $table->string('meter_number')->nullable();           // Electricity
            $table->string('phone_number')->nullable();           // Airtime recipient
            $table->string('airtime_type')->nullable();           // VCL, ETL, etc.
            $table->string('policy_number')->nullable();          // Insurance
            $table->integer('insurance_partner_id')->nullable();  // Insurance provider ID
            $table->string('event_id')->nullable();               // Ticket event
            $table->string('ticket_id')->nullable();              // Ticket type
            $table->string('ticket_reference')->nullable();       // Ticket ref from provider

            // Lifecycle
            $table->timestamp('disbursed_at')->nullable();        // When bill was paid to provider
            $table->enum('status', ['pending', 'active', 'settled', 'partial', 'failed', 'refunded'])
                  ->default('pending');
            $table->timestamp('payday_deducted_at')->nullable();  // When fully settled
            $table->string('failure_reason')->nullable();         // If failed

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('bill_category');
            $table->index('created_at');
        });

        // ── mybill_repayments: audit trail for deductions ────────────────
        Schema::create('mybill_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mybill_loan_id')->constrained('mybill_loans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->decimal('amount', 10, 2);
            $table->enum('deduction_type', ['upfront', 'payday']);
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->string('salary_credit_ref')->nullable();      // Triggering salary reference
            $table->string('cpay_txn_id')->nullable();            // CPay transaction for this deduction
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['mybill_loan_id', 'deduction_type']);
            $table->index(['user_id', 'processed_at']);
        });

        // ── mybill_payday_events: log of detected payday salary credits ─
        Schema::create('mybill_payday_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->decimal('salary_amount', 10, 2)->nullable();  // Detected salary amount
            $table->timestamp('detected_at')->nullable();
            $table->integer('loans_settled')->default(0);
            $table->decimal('total_deducted', 10, 2)->default(0);
            $table->enum('trigger_type', ['auto', 'manual'])->default('auto');
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mybill_payday_events');
        Schema::dropIfExists('mybill_repayments');
        Schema::dropIfExists('mybill_loans');
        Schema::dropIfExists('mybill_limits');
    }
};
