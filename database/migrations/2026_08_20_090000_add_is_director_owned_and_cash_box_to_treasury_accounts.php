<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TreasuryAccount;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('treasury_accounts', 'is_director_owned')) {
            Schema::table('treasury_accounts', function (Blueprint $table) {
                $table->boolean('is_director_owned')->default(false)->after('is_active');
            });
        }

        // Seed/Update accounts
        $accounts = [
            [
                'name' => 'Myloan Mpesa',
                'type' => 'mobile_wallet',
                'institution' => 'Vodacom M-Pesa',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => false,
            ],
            [
                'name' => 'Standard Lesotho Bank',
                'type' => 'bank',
                'institution' => 'Standard Lesotho Bank',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => false,
            ],
            [
                'name' => 'First National Bank',
                'type' => 'bank',
                'institution' => 'FNB Lesotho',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => false,
            ],
            [
                'name' => 'Managing Director Mpesa',
                'type' => 'mobile_wallet',
                'institution' => 'Vodacom M-Pesa',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => true,
            ],
            [
                'name' => 'Cpay',
                'type' => 'mobile_wallet',
                'institution' => 'Chaperone CPay',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => false,
            ],
            [
                'name' => 'Quinnpay/Webfin',
                'type' => 'bank',
                'institution' => 'Quinnpay/Webfin',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => false,
            ],
            [
                'name' => 'Cash Box',
                'type' => 'cash_float',
                'institution' => 'Office Safe',
                'balance' => 0.00,
                'is_active' => true,
                'is_director_owned' => false,
            ],
        ];

        foreach ($accounts as $acc) {
            TreasuryAccount::updateOrCreate(['name' => $acc['name']], $acc);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('treasury_accounts', 'is_director_owned')) {
            Schema::table('treasury_accounts', function (Blueprint $table) {
                $table->dropColumn('is_director_owned');
            });
        }
    }
};
