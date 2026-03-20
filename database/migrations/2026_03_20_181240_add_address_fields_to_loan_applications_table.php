<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Only add columns that don't already exist
            $existing = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM loan_applications");
            $cols = array_column($existing, 'Field');

            if (!in_array('village', $cols))               $table->string('village')->nullable();
            if (!in_array('town', $cols))                  $table->string('town')->nullable();
            if (!in_array('district', $cols))              $table->string('district')->nullable();
            if (!in_array('address_duration', $cols))      $table->string('address_duration')->nullable();
            if (!in_array('residence_type', $cols))        $table->string('residence_type')->nullable();
            if (!in_array('nearest_landmark', $cols))      $table->string('nearest_landmark')->nullable();
            if (!in_array('home_directions', $cols))       $table->text('home_directions')->nullable();
            if (!in_array('gps_latitude', $cols))          $table->decimal('gps_latitude', 10, 6)->nullable();
            if (!in_array('gps_longitude', $cols))         $table->decimal('gps_longitude', 10, 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn([
                'village', 'town', 'district',
                'address_duration', 'residence_type',
                'nearest_landmark', 'home_directions',
                'gps_latitude', 'gps_longitude',
            ]);
        });
    }
};