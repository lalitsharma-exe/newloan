<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TreasuryAccount;

class TreasuryAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Myloan Mpesa',
                'type' => 'mobile_wallet',
                'institution' => 'Vodacom M-Pesa',
                'balance' => 500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Standard Lesotho Bank',
                'type' => 'bank',
                'institution' => 'Standard Lesotho Bank',
                'balance' => 1000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'First National Bank',
                'type' => 'bank',
                'institution' => 'FNB Lesotho',
                'balance' => 1000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Managing Director Mpesa',
                'type' => 'mobile_wallet',
                'institution' => 'Vodacom M-Pesa',
                'balance' => 100000.00,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $acc) {
            TreasuryAccount::updateOrCreate(['name' => $acc['name']], $acc);
        }
    }
}
