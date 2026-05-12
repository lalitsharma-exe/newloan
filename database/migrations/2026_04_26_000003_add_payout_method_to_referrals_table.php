<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('referrals', 'payout_method')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->string('payout_method')->nullable()->after('paid_at'); // manual, loan_credit, mpesa
            });
        }
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn('payout_method');
        });
    }
};
