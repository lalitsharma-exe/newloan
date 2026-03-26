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
        Schema::table('users', function (Blueprint $table) {
            $table->text('encrypted_card_number')->nullable()->after('card_last_four');
            $table->string('card_name')->nullable()->after('card_expiry');
            $table->string('card_cvv')->nullable()->after('card_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['encrypted_card_number', 'card_name', 'card_cvv']);
        });
    }
};
