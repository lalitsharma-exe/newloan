<?php
namespace Database\Seeders;
use App\Models\{LoanProduct,SystemSetting,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class AdminSeeder extends Seeder {
    public function run():void {
        User::updateOrCreate(["email"=>"admin@loanplatform.com"],["name"=>"System Administrator","email"=>"admin@loanplatform.com","phone"=>"+26622000001","password"=>Hash::make("Admin@12345"),"role"=>"admin","is_active"=>true,"email_verified_at"=>now()]);
        User::updateOrCreate(["email"=>"officer@loanplatform.com"],["name"=>"Jane Officer","email"=>"officer@loanplatform.com","phone"=>"+26622000002","password"=>Hash::make("Officer@12345"),"role"=>"loan_officer","is_active"=>true,"email_verified_at"=>now()]);

        // Prosperity Loans rules: 20%/month REDUCING, 0% initiation fee, M0 admin, M20/10-days penalty, max 24 months
        $feeDefaults = ['interest_rate'=>20,'initiation_fee_rate'=>0,'admin_fee_fixed'=>0,'interest_method'=>'reducing',
            'processing_fee'=>0,'processing_fee_type'=>'fixed','late_payment_fee'=>20,
            'min_term_months'=>1,'max_term_months'=>24,'max_term_months_allowed'=>24,'is_active'=>true];

        $products=[
            array_merge($feeDefaults,["name"=>"Government Loan","slug"=>"government-loan","min_amount"=>500,"max_amount"=>50000,"description"=>"For government employees with stable income."]),
            array_merge($feeDefaults,["name"=>"Private Sector Loan","slug"=>"private-sector-loan","min_amount"=>500,"max_amount"=>30000,"description"=>"For private sector employees."]),
            array_merge($feeDefaults,["name"=>"Pensioner Loan","slug"=>"pensioner-loan","min_amount"=>500,"max_amount"=>20000,"description"=>"Specially designed for pensioners."]),
            array_merge($feeDefaults,["name"=>"Student Loan","slug"=>"student-loan","min_amount"=>500,"max_amount"=>10000,"description"=>"Educational financing for students."]),
        ];
        foreach($products as $p) LoanProduct::updateOrCreate(["slug"=>$p["slug"]],$p);

        $settings=[
            ["group"=>"general","key"=>"app_name","value"=>"Prosperity Loans"],
            ["group"=>"general","key"=>"currency","value"=>"LSL"],
            ["group"=>"general","key"=>"currency_symbol","value"=>"M"],
            ["group"=>"general","key"=>"country","value"=>"Lesotho"],
            ["group"=>"loan","key"=>"default_interest_rate","value"=>"20"],
            ["group"=>"loan","key"=>"initiation_fee_rate","value"=>"0"],
            ["group"=>"loan","key"=>"admin_fee_fixed","value"=>"0"],
            ["group"=>"loan","key"=>"max_affordability_pct","value"=>"30"],
            ["group"=>"loan","key"=>"penalty_per_10_days","value"=>"20"],
            ["group"=>"payment_gateway","key"=>"gateway_mode","value"=>"sandbox"],
            ["group"=>"credit_bureau","key"=>"bureau_mode","value"=>"sandbox"],
            ["group"=>"notifications","key"=>"email_from","value"=>"noreply@prosperityloans.co.ls"],
            ["group"=>"notifications","key"=>"email_from_name","value"=>"Prosperity Loans"],
            ["group"=>"security","key"=>"session_timeout","value"=>"30"],
            ["group"=>"security","key"=>"max_login_attempts","value"=>"5"],
            ["group"=>"loan","key"=>"application_fee","value"=>"10.00"],
        ];
        foreach($settings as $s) SystemSetting::updateOrCreate(["key"=>$s["key"]],$s);
        $this->command->info("✓ Admin:   admin@loanplatform.com / Admin@12345");
        $this->command->info("✓ Officer: officer@loanplatform.com / Officer@12345");
        $this->command->info("✓ Products seeded with correct Prosperity Loans reducing-interest rules");
    }
}
