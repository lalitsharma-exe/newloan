<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add phone_verified_at to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            }
        });

        // OTP table — stores pending phone verifications
        Schema::create('phone_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->index();
            $table->string('otp', 10);
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_otps');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_verified_at');
        });
    }
};
