<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Float Records Table
        Schema::create('float_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected', 'disbursed', 'due', 'overdue', 'frozen', 'closed'])->default('pending');
            $table->decimal('principal_amount', 10, 2)->default(500.00);
            $table->decimal('charge_amount', 10, 2)->default(125.00);
            $table->decimal('outstanding_balance', 10, 2);
            $table->string('purpose')->nullable();
            $table->enum('affordability_result', ['pass', 'fail'])->nullable();
            $table->decimal('disposable_income', 10, 2)->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->integer('penalty_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Ensure only one active float per user
            // We use a partial index if the DB supports it, or handle in application logic.
            // For Laravel/MySQL, we'll enforce this in the service layer.
        });

        // 2. Penalty Log Table
        Schema::create('float_penalty_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('float_record_id')->constrained('float_records')->onDelete('cascade');
            $table->decimal('penalty_amount', 10, 2)->default(125.00);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->date('penalty_date');
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();
        });

        // 3. Update Users table with float flags
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('float_eligible')->default(false)->after('is_active');
            $table->boolean('float_frozen')->default(false)->after('float_eligible');
            $table->text('float_freeze_reason')->nullable()->after('float_frozen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('float_penalty_logs');
        Schema::dropIfExists('float_records');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['float_eligible', 'float_frozen', 'float_freeze_reason']);
        });
    }
};
