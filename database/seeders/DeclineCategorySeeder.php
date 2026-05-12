<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\DeclineCategory;

class DeclineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Affordability',
                'display_order' => 1,
                'reasons' => [
                    'Insufficient income',
                    'High debt-to-income ratio',
                    'Disposable income too low',
                    'Net income below minimum threshold'
                ]
            ],
            [
                'id' => 2,
                'name' => 'Credit / repayment behaviour',
                'display_order' => 2,
                'reasons' => [
                    'Poor repayment history',
                    'Previous defaults',
                    'High arrears (PAR history)',
                    'Existing overdue loans',
                    'Missed payments on internal loans'
                ]
            ],
            [
                'id' => 3,
                'name' => 'Incomplete / invalid information',
                'display_order' => 3,
                'reasons' => [
                    'Missing ID document',
                    'Missing payslip',
                    'Missing bank statement',
                    'Incorrect personal details',
                    'Failed KYC verification'
                ]
            ],
            [
                'id' => 4,
                'name' => 'Fraud / risk flags',
                'display_order' => 4,
                'reasons' => [
                    'Suspicious application pattern',
                    'Device/identity mismatch',
                    'Multiple applications in short period',
                    'Suspected fake documents',
                    'Flagged fraud alert'
                ]
            ],
            [
                'id' => 5,
                'name' => 'Employment / income instability',
                'display_order' => 5,
                'reasons' => [
                    'Not permanently employed',
                    'Short employment history',
                    'Unverified employer',
                    'Irregular/unstable income',
                    'High-risk employment segment'
                ]
            ],
            [
                'id' => 6,
                'name' => 'Internal policy rules',
                'display_order' => 6,
                'reasons' => [
                    'Loan amount exceeds allowed limit',
                    'Applicant outside target segment',
                    'Age outside policy range',
                    'Blacklisted client',
                    'Product not available for segment'
                ]
            ],
            [
                'id' => 7,
                'name' => 'Existing exposure',
                'display_order' => 7,
                'reasons' => [
                    'Already has active loan',
                    'Overexposed to MyLoan',
                    'Total exposure exceeds limit',
                    'Multiple active facilities'
                ]
            ],
            [
                'id' => 8,
                'name' => 'Recency / behaviour rules',
                'display_order' => 8,
                'reasons' => [
                    'Applied too recently',
                    'Recently declined',
                    'No repayment history (new client)',
                    'Cooling-off period not elapsed'
                ]
            ]
        ];

        foreach ($categories as $cat) {
            DeclineCategory::updateOrCreate(['id' => $cat['id']], $cat);
        }
    }
}
