<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AgentProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds to setup a default agent.
     */
    public function run(): void
    {
        // 1. Create agent user
        $agentUser = User::updateOrCreate(
            ['email' => 'agent@myloan.co.ls'],
            [
                'name' => 'UAT Agent',
                'phone' => '+266 58123456',
                'national_id' => '900101-0002-00',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'is_active' => true,
            ]
        );

        // 2. Create agent profile linked to user
        AgentProfile::updateOrCreate(
            ['user_id' => $agentUser->id],
            [
                'agent_id' => 'AGT-1001',
                'agent_type' => 'shop',
                'shop_name' => 'MyLoan Corner Shop',
                'business_type' => 'Mobile Money Agent',
                'shop_location' => 'Kingsway Road, Maseru',
                'contract_ref' => 'CON-AGT-1001',
                'payout_method' => 'M-Pesa',
                'payout_account_name' => 'UAT Agent',
                'payout_number_or_details' => '+266 58123456',
                'signed_at' => now(),
                'total_earned' => 150.00,
                'pending_earnings' => 50.00,
            ]
        );

        $this->command->info('-------------------------------------------');
        $this->command->info(' Agent Portal User Seeded Successfully! ');
        $this->command->info('-------------------------------------------');
        $this->command->info(' Login URL : http://127.0.0.1:8000/agent/login');
        $this->command->info(' Email     : agent@myloan.co.ls');
        $this->command->info(' Password  : password');
        $this->command->info(' Agent ID  : AGT-1001');
        $this->command->info('-------------------------------------------');
    }
}
