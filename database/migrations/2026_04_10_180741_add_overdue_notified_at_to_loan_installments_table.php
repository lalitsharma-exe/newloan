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
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->timestamp('overdue_notified_at')->nullable()->after('last_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->dropColumn('overdue_notified_at');
        });
    }
};
