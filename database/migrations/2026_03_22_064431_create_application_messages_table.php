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
        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('loan_applications')->cascadeOnDelete();
            $table->string('sender_type'); // 'admin', 'borrower', 'officer'
            $table->unsignedBigInteger('sender_id')->nullable(); // Depending on sender type
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_messages');
    }
};
