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
        Schema::create('decline_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('application_id')->constrained('loan_applications');
            $table->string('application_number');
            $table->string('applicant_name')->nullable();
            $table->foreignId('category_id')->constrained('decline_categories');
            $table->string('reason');
            $table->decimal('loan_amount', 12, 2)->nullable();
            $table->date('declined_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decline_records');
    }
};
