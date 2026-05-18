<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Investor, Investment, InvestmentAccrual, TreasuryAccount};
use Carbon\Carbon;

class InvestorSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure we have at least one active Treasury Account to fund
        $account = TreasuryAccount::where('is_active', true)->first();
        if (!$account) {
            $account = TreasuryAccount::create([
                'name' => 'Standard Lesotho Bank Corporate',
                'type' => 'bank',
                'institution' => 'Standard Lesotho Bank',
                'account_number' => '1020304050',
                'currency' => 'LSL',
                'balance' => 250000.00,
                'is_active' => true
            ]);
        }

        // 2. Fetch or create users to link to investors
        $adminUser = User::where('role', 'admin')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Executive Director',
                'email' => 'admin@lending.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active'
            ]);
        }

        $publicUser = User::where('role', 'borrower')->first();
        if (!$publicUser) {
            $publicUser = User::create([
                'name' => 'Thabo Mokoena',
                'email' => 'thabo@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'borrower',
                'status' => 'active'
            ]);
        }

        // Clean up any existing records to allow clean re-runs
        InvestmentAccrual::query()->delete();
        Investment::query()->delete();
        Investor::query()->delete();

        // 3. Seed MD Investor Profile
        $mdInvestor = Investor::create([
            'user_id' => $adminUser->id,
            'investor_type' => 'MD',
            'full_name' => $adminUser->name . ' (MD)',
            'id_number' => 'MD-NAT-8899',
            'phone' => '+266 5885 0100',
            'email' => $adminUser->email,
            'address' => 'Corporate Villa 12, Maseru 100',
            'bank_name' => 'Standard Lesotho Bank',
            'account_number' => '9080706050',
            'status' => 'active'
        ]);

        // 4. Seed Public Investor Profile
        $publicInvestor = Investor::create([
            'user_id' => $publicUser->id,
            'investor_type' => 'PUBLIC',
            'full_name' => 'Thabo Mokoena (Public Partner)',
            'id_number' => 'PUB-NAT-1122',
            'phone' => '+266 6200 4545',
            'email' => $publicUser->email,
            'address' => 'Subdivision 4, Leribe',
            'bank_name' => 'Nedbank Lesotho',
            'account_number' => '1122334455',
            'status' => 'active'
        ]);

        // 5. Seed MD Investment Tranche: Injected 4 months ago (e.g. Jan 15th)
        $janValueDate = Carbon::create(date('Y'), 1, 15);
        $mdTranche = Investment::create([
            'investor_id' => $mdInvestor->id,
            'treasury_account_id' => $account->id,
            'contract_ref' => 'INV-MD-' . date('Y') . '-001',
            'principal_cents' => 15000000, // LSL 150,000.00
            'interest_rate' => 0.0500,     // 5% monthly return
            'interest_start_date' => Carbon::create(date('Y'), 2, 28),
            'investment_date' => $janValueDate,
            'maturity_date' => Carbon::create(date('Y'), 12, 31),
            'total_months' => 11,
            'monthly_interest_cents' => 750000, // 5% of 150,000 = LSL 7,500.00
            'total_interest_cents' => 750000 * 11,
            'total_repayable_cents' => 15000000 + (750000 * 11),
            'status' => 'active'
        ]);

        // Populate past posted interest accruals for Jan, Feb, Mar, Apr
        $months = ['January', 'February', 'March', 'April'];
        foreach ($months as $idx => $mName) {
            $accrualDate = Carbon::create(date('Y'), 2 + $idx, 28)->endOfMonth();
            if ($accrualDate->isPast()) {
                InvestmentAccrual::create([
                    'investment_id' => $mdTranche->id,
                    'accrual_date' => $accrualDate,
                    'interest_cents' => 750000,
                    'status' => 'posted',
                    'posted_at' => $accrualDate->copy()->addDay()
                ]);
            }
        }
        // Add pending accruals for remainder of the year
        for ($i = 5; $i <= 12; $i++) {
            $accrualDate = Carbon::create(date('Y'), $i, 28)->endOfMonth();
            InvestmentAccrual::create([
                'investment_id' => $mdTranche->id,
                'accrual_date' => $accrualDate,
                'interest_cents' => 750000,
                'status' => 'pending'
            ]);
        }

        // 6. Seed Public Investment Tranche: Injected 2 months ago (e.g. Mar 10th)
        $marValueDate = Carbon::create(date('Y'), 3, 10);
        $pubTranche = Investment::create([
            'investor_id' => $publicInvestor->id,
            'treasury_account_id' => $account->id,
            'contract_ref' => 'INV-PUB-' . date('Y') . '-002',
            'principal_cents' => 8000000, // LSL 80,000.00
            'interest_rate' => 0.0150,    // 1.5% monthly return
            'interest_start_date' => Carbon::create(date('Y'), 4, 30),
            'investment_date' => $marValueDate,
            'maturity_date' => Carbon::create(date('Y'), 12, 31),
            'total_months' => 9,
            'monthly_interest_cents' => 120000, // 1.5% of 80,000 = LSL 1,200.00
            'total_interest_cents' => 120000 * 9,
            'total_repayable_cents' => 8000000 + (120000 * 9),
            'status' => 'active'
        ]);

        // Populate accruals for March and April
        $pubMonths = ['April'];
        foreach ($pubMonths as $idx => $mName) {
            $accrualDate = Carbon::create(date('Y'), 4 + $idx, 28)->endOfMonth();
            if ($accrualDate->isPast()) {
                InvestmentAccrual::create([
                    'investment_id' => $pubTranche->id,
                    'accrual_date' => $accrualDate,
                    'interest_cents' => 120000,
                    'status' => 'posted',
                    'posted_at' => $accrualDate->copy()->addDay()
                ]);
            }
        }
        // Add pending accruals for remainder of the year
        for ($i = 5; $i <= 12; $i++) {
            $accrualDate = Carbon::create(date('Y'), $i, 28)->endOfMonth();
            InvestmentAccrual::create([
                'investment_id' => $pubTranche->id,
                'accrual_date' => $accrualDate,
                'interest_cents' => 120000,
                'status' => 'pending'
            ]);
        }

        // 7. Seed Public Exit-Pending Tranche: Termination Pending
        $febValueDate = Carbon::create(date('Y'), 2, 5);
        $exitTranche = Investment::create([
            'investor_id' => $publicInvestor->id,
            'treasury_account_id' => $account->id,
            'contract_ref' => 'INV-PUB-' . date('Y') . '-003',
            'principal_cents' => 5000000, // LSL 50,000.00
            'interest_rate' => 0.0150,    // 1.5% monthly return
            'interest_start_date' => Carbon::create(date('Y'), 3, 31),
            'investment_date' => $febValueDate,
            'maturity_date' => Carbon::create(date('Y'), 12, 31),
            'total_months' => 10,
            'monthly_interest_cents' => 75000, // LSL 750.00
            'total_interest_cents' => 75000 * 10,
            'total_repayable_cents' => 5000000 + (75000 * 10),
            'status' => 'termination_pending',
            'termination_requested_at' => now()->subDays(15),
            'termination_notice_expiry' => now()->addDays(15) // Notice active for 15 more days
        ]);

        // Add posted accruals for Feb and March
        $exitMonths = ['March', 'April'];
        foreach ($exitMonths as $idx => $mName) {
            $accrualDate = Carbon::create(date('Y'), 3 + $idx, 28)->endOfMonth();
            if ($accrualDate->isPast()) {
                InvestmentAccrual::create([
                    'investment_id' => $exitTranche->id,
                    'accrual_date' => $accrualDate,
                    'interest_cents' => 75000,
                    'status' => 'posted',
                    'posted_at' => $accrualDate->copy()->addDay()
                ]);
            }
        }
        // Add pending accruals for remainder of the year
        for ($i = 5; $i <= 12; $i++) {
            $accrualDate = Carbon::create(date('Y'), $i, 28)->endOfMonth();
            InvestmentAccrual::create([
                'investment_id' => $exitTranche->id,
                'accrual_date' => $accrualDate,
                'interest_cents' => 75000,
                'status' => 'pending'
            ]);
        }

        $this->command->info('✓ Seeding complete: Active MD, Active Public, and Notice-Pending exit tranches seeded successfully!');
    }
}
