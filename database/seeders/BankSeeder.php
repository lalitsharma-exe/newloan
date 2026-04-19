<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\BankBranch;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Lesotho Postbank' => [
                'Maseru' => '500100',
                'Katse' => '500333',
                'Mount Moorosi' => '500750',
                'Qacha’s Nek' => '500600',
                'Hlotse' => '500300',
                'Mafeteng' => '500900',
                'Mohale’s Hoek' => '500800',
                'Mokhotlong' => '500500',
                'Bonhomme' => '500110',
                'Pitseng' => '500320',
                'Semonkong' => '500120',
                'Machache' => '500130',
                'Maputsoe' => '500350',
                'Thaba Tseka' => '500550',
                'Mapoteng' => '500250',
                'Mapholaneng' => '500520',
                'Quthing' => '500700',
            ],
            'Standard Lesotho Bank' => [
                'Butha Buthe' => '061167',
                'Cathedral' => '063067',
                'City' => '060667',
                'Industrial' => '062367',
                'Leribe' => '060867',
                'Lesotho OPC' => '062867',
                'Mafeteng' => '060967',
                'Maputsoe' => '061067',
                'Maseru Mall' => '063167',
                'Mohale’s Hoek' => '060767',
                'Mokhotlong' => '062567',
                'Pioneer' => '062967',
                'Qacha’s Nek' => '062667',
                'Quthing' => '062467',
                'Teyateyaneng' => '062167',
                'Thaba Tseka' => '062767',
                'Tower' => '062067',
            ],
            'Nedbank Lesotho' => [
                'Butha Buthe' => '390561',
                'Hlotse' => '390761',
                'Maputsoe' => '390261',
                'Maseru Kingsway' => '390161',
                'Maseru Mall' => '390961',
                'Mohale’s Hoek' => '390361',
                'Berea' => '390061',
                'Mafeteng' => '390461',
                'Pioneer Mall' => '390861',
                'Roma' => '390661',
            ],
            'First National Bank Lesotho' => [
                'Pioneer Mall' => '280061',
                'Maputsoe' => '281161',
                'Kingsway' => '280261',
                'Butha Buthe' => '280561',
                'Mafeteng' => '280861',
                'Ty' => '',
                'Hlotse' => '282861',
                'Mokhotlong' => '',
                'Masianokeng' => '',
                'Maseru Mall' => '',
                'Quthing' => '',
                'Mohale’s Hoek' => '',
            ]
        ];

        foreach ($data as $bankName => $branches) {
            $bank = Bank::updateOrCreate(['name' => $bankName]);
            foreach ($branches as $branchName => $code) {
                BankBranch::updateOrCreate(
                    ['bank_id' => $bank->id, 'name' => $branchName],
                    ['code' => $code]
                );
            }
        }
    }
}
