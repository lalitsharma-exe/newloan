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
        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('loan_applications', 'verification_status')) {
                $table->string('verification_status')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('loan_applications', 'verification_meta')) {
                $table->json('verification_meta')->nullable()->after('verification_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['agent_id', 'verification_status', 'verification_meta']);
        });
    }
};
