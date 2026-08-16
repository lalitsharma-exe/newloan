<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'village'))               $table->string('village')->nullable();
            if (!Schema::hasColumn('loan_applications', 'town'))                  $table->string('town')->nullable();
            if (!Schema::hasColumn('loan_applications', 'district'))              $table->string('district')->nullable();
            if (!Schema::hasColumn('loan_applications', 'address_duration'))      $table->string('address_duration')->nullable();
            if (!Schema::hasColumn('loan_applications', 'residence_type'))        $table->string('residence_type')->nullable();
            if (!Schema::hasColumn('loan_applications', 'nearest_landmark'))      $table->string('nearest_landmark')->nullable();
            if (!Schema::hasColumn('loan_applications', 'home_directions'))       $table->text('home_directions')->nullable();
            if (!Schema::hasColumn('loan_applications', 'gps_latitude'))          $table->decimal('gps_latitude', 10, 6)->nullable();
            if (!Schema::hasColumn('loan_applications', 'gps_longitude'))         $table->decimal('gps_longitude', 10, 6)->nullable();
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