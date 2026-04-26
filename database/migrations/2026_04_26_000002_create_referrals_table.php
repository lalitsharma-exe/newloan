<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->string('referral_code');
            $table->foreignId('loan_id')->nullable()->constrained('loans')->onDelete('set null');
            $table->enum('status', ['pending', 'validated', 'qualified', 'paid', 'rejected'])->default('pending');
            $table->boolean('is_first_loan')->default(false);
            $table->boolean('is_repeat_loan')->default(false);
            $table->string('rejected_reason')->nullable();
            $table->decimal('amount', 12, 2)->default(50.00);
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Ensure one referral per referred user
            $table->unique('referred_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
