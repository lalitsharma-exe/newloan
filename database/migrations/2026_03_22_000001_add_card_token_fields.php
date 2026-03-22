<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add card token to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'card_token'))
                $table->string('card_token')->nullable()->after('profile_photo');
            if (!Schema::hasColumn('users', 'card_last_four'))
                $table->string('card_last_four', 4)->nullable()->after('card_token');
            if (!Schema::hasColumn('users', 'card_expiry'))
                $table->string('card_expiry', 7)->nullable()->after('card_last_four');
            if (!Schema::hasColumn('users', 'card_brand'))
                $table->string('card_brand', 20)->nullable()->after('card_expiry');
            if (!Schema::hasColumn('users', 'card_tokenised_at'))
                $table->timestamp('card_tokenised_at')->nullable()->after('card_brand');
        });

        // Add card tokenization step to loan_applications
        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'card_tokenised'))
                $table->boolean('card_tokenised')->default(false)->after('step');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['card_token','card_last_four','card_expiry','card_brand','card_tokenised_at']);
        });
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn('card_tokenised');
        });
    }
};
