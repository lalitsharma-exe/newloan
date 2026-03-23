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
            $table->integer('salary_payday')->nullable();
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->integer('salary_payday')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('salary_payday');
        });
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn('salary_payday');
        });
    }
};
