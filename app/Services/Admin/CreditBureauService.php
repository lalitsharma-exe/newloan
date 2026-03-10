<?php
namespace App\Services\Admin;
use App\Models\{CreditReport,Loan,User};
class CreditBureauService {
    public function pullReport(User $user, string $nationalId, string $checkType):array {
        $mock=["score"=>rand(400,800),"national_id"=>$nationalId,"defaults"=>0,"judgments"=>0,"enquiries_6mo"=>rand(0,5),"summary"=>"Sandbox check"];
        $r=CreditReport::create(["user_id"=>$user->id,"national_id"=>$nationalId,"provider"=>"Experian","check_type"=>$checkType,"credit_score"=>$mock["score"],"report_data"=>$mock,"status"=>"retrieved","retrieved_at"=>now()]);
        return ["success"=>true,"report_id"=>$r->id];
    }
    public function submitMonthlyFile():array {
        $count=Loan::with("user")->whereMonth("updated_at",now()->month)->count();
        return ["success"=>true,"count"=>$count];
    }
}
