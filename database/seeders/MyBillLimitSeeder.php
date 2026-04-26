<?php

namespace Database\Seeders;

use App\Models\MyBillLimit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds MyBill credit limits for all existing active borrowers.
 * Safe to run multiple times — uses firstOrCreate.
 */
class MyBillLimitSeeder extends Seeder
{
    public function run(): void
    {
        $borrowers = User::where('role', 'borrower')
            ->where('is_active', true)
            ->get(['id']);

        $created = 0;
        $skipped = 0;

        foreach ($borrowers as $borrower) {
            $limit = MyBillLimit::firstOrCreate(
                ['user_id' => $borrower->id],
                [
                    'total_limit'  => 500.00,
                    'used_amount'  => 0.00,
                    'activated_at' => now(),
                ]
            );

            if ($limit->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("MyBill limits: {$created} created, {$skipped} already existed.");
    }
}
