<?php
namespace Database\Seeders;
use App\Models\{LoanProduct,SystemSetting,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class AdminSeeder extends Seeder {
    public function run():void {
        User::updateOrCreate(["email"=>"admin@loanplatform.com"],["name"=>"System Administrator","email"=>"admin@loanplatform.com","phone"=>"+26622000001","password"=>Hash::make("Admin@12345"),"role"=>"admin","is_active"=>true,"email_verified_at"=>now()]);
        User::updateOrCreate(["email"=>"officer@loanplatform.com"],["name"=>"Jane Officer","email"=>"officer@loanplatform.com","phone"=>"+26622000002","password"=>Hash::make("Officer@12345"),"role"=>"loan_officer","is_active"=>true,"email_verified_at"=>now()]);
        $products=[
            ["name"=>"Government Loan","slug"=>"government-loan","interest_rate"=>3.5,"min_amount"=>500,"max_amount"=>50000,"min_term_months"=>1,"max_term_months"=>60,"processing_fee"=>2.5,"processing_fee_type"=>"percentage","late_payment_fee"=>50,"description"=>"For government employees with stable income.","is_active"=>true],
            ["name"=>"Private Sector Loan","slug"=>"private-sector-loan","interest_rate"=>4.5,"min_amount"=>500,"max_amount"=>30000,"min_term_months"=>1,"max_term_months"=>36,"processing_fee"=>3.0,"processing_fee_type"=>"percentage","late_payment_fee"=>75,"description"=>"For private sector employees.","is_active"=>true],
            ["name"=>"Pensioner Loan","slug"=>"pensioner-loan","interest_rate"=>3.0,"min_amount"=>500,"max_amount"=>20000,"min_term_months"=>1,"max_term_months"=>24,"processing_fee"=>2.0,"processing_fee_type"=>"percentage","late_payment_fee"=>30,"description"=>"Specially designed for pensioners.","is_active"=>true],
            ["name"=>"Student Loan","slug"=>"student-loan","interest_rate"=>2.5,"min_amount"=>500,"max_amount"=>10000,"min_term_months"=>3,"max_term_months"=>12,"processing_fee"=>1.5,"processing_fee_type"=>"percentage","late_payment_fee"=>25,"description"=>"Educational financing.","is_active"=>true],
        ];
        foreach($products as $p) LoanProduct::updateOrCreate(["slug"=>$p["slug"]],$p);
        $settings=[
            ["group"=>"general","key"=>"app_name","value"=>"LoanPlatform"],
            ["group"=>"general","key"=>"currency","value"=>"LSL"],
            ["group"=>"general","key"=>"currency_symbol","value"=>"L"],
            ["group"=>"payment_gateway","key"=>"gateway_mode","value"=>"sandbox"],
            ["group"=>"credit_bureau","key"=>"bureau_mode","value"=>"sandbox"],
            ["group"=>"notifications","key"=>"email_from","value"=>"noreply@loanplatform.com"],
            ["group"=>"notifications","key"=>"email_from_name","value"=>"LoanPlatform"],
            ["group"=>"security","key"=>"session_timeout","value"=>"60"],
            ["group"=>"security","key"=>"max_login_attempts","value"=>"5"],
        ];
        foreach($settings as $s) SystemSetting::updateOrCreate(["key"=>$s["key"]],$s);
        $this->command->info("Admin: admin@loanplatform.com / Admin@12345");
        $this->command->info("Officer: officer@loanplatform.com / Officer@12345");
    }
}
