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
            $existing = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM loan_applications");
            $cols = array_column($existing, 'Field');
            if (!in_array('residential_address', $cols)) {
                $table->string('residential_address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn('residential_address');
        });
    }
};
