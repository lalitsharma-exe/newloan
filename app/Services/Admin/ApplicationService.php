<?php
namespace App\Services\Admin;
use App\Models\{Loan,LoanApplication,LoanInstallment,ApplicationNote,User};
use Illuminate\Support\Str; use Carbon\Carbon;
class ApplicationService {
    public function getPaginated(array $f) {
        $q = LoanApplication::with(["user","loanProduct"])->where("status","!=","draft");
        if (!empty($f["status"]))  $q->where("status",$f["status"]);
        if (!empty($f["product"])) $q->where("loan_product_id",$f["product"]);
        if (!empty($f["date_from"])) $q->whereDate("created_at",">=",$f["date_from"]);
        if (!empty($f["date_to"]))   $q->whereDate("created_at","<=",$f["date_to"]);
        if (!empty($f["search"])) {
            $s=$f["search"];
            $q->where(fn($x)=>$x->where("application_number","like","%$s%")->orWhereHas("user",fn($u)=>$u->where("name","like","%$s%")->orWhere("email","like","%$s%")));
        }
        return $q->latest()->paginate(20);
    }
    public function getStats():array {
        return [
            "total"         =>LoanApplication::submitted()->count(),
            "pending"       =>LoanApplication::pending()->count(),
            "approved_today"=>LoanApplication::whereDate("decided_at",today())->where("status","approved")->count(),
            "declined_today"=>LoanApplication::whereDate("decided_at",today())->where("status","declined")->count(),
        ];
    }
    public function approve(LoanApplication $app, array $data, User $admin):Loan {
        $app->update(["status"=>"approved","approved_amount"=>$data["approved_amount"],"approved_term"=>$data["approved_term"],"approved_interest_rate"=>$data["interest_rate"],"disbursement_date"=>$data["disbursement_date"],"decided_at"=>now()]);
        $this->addNote($app,"approval",$data["notes"]??"Approved.",$admin,false);
        return $this->createLoan($app);
    }
    public function decline(LoanApplication $app, string $reason, User $admin):void {
        $app->update(["status"=>"declined","decline_reason"=>$reason,"decided_at"=>now()]);
        $this->addNote($app,"decline",$reason,$admin,false);
    }
    public function hold(LoanApplication $app, string $reason, User $admin):void {
        $app->update(["status"=>"on_hold"]);
        $this->addNote($app,"hold",$reason,$admin,true);
    }
    public function requestInfo(LoanApplication $app, string $msg, User $admin):void {
        $app->update(["status"=>"info_requested"]);
        $this->addNote($app,"info_request",$msg,$admin,false);
    }
    public function overrideLoanTerms(LoanApplication $app, array $data, User $admin):void {
        $app->update(["requested_amount"=>$data["loan_amount"],"approved_interest_rate"=>$data["interest_rate"],"requested_term"=>$data["term_months"]]);
        $this->addNote($app,"override","Terms overridden: Amount {$data["loan_amount"]}, Rate {$data["interest_rate"]}%, Term {$data["term_months"]}mo",$admin,true);
    }
    private function createLoan(LoanApplication $app):Loan {
        $amount=$app->approved_amount; $rate=$app->approved_interest_rate/100/12; $term=$app->approved_term;
        $product=$app->loanProduct;
        $monthly = $rate>0 ? $amount*($rate*pow(1+$rate,$term))/(pow(1+$rate,$term)-1) : $amount/$term;
        $fee = $product && $product->processing_fee_type==="percentage" ? $amount*($product->processing_fee/100) : ($product->processing_fee??0);
        $loan=Loan::create(["loan_number"=>"LN-".strtoupper(Str::random(8)),"user_id"=>$app->user_id,"loan_product_id"=>$app->loan_product_id,"application_id"=>$app->id,"principal_amount"=>$amount,"interest_rate"=>$app->approved_interest_rate,"term_months"=>$term,"total_amount"=>round($monthly*$term,2),"outstanding_balance"=>$amount,"monthly_installment"=>round($monthly,2),"processing_fee"=>$fee,"status"=>"active","disbursement_date"=>$app->disbursement_date,"first_payment_date"=>Carbon::parse($app->disbursement_date)->addMonth(),"maturity_date"=>Carbon::parse($app->disbursement_date)->addMonths($term),"payout_method"=>$app->payout_method,"collection_method"=>$app->collection_method]);
        $balance=$amount; $pd=Carbon::parse($app->disbursement_date)->addMonth();
        for($i=1;$i<=$term;$i++){
            $interest=round($balance*$rate,2); $principal=round(min($monthly-$interest,$balance),2); $balance-=$principal;
            LoanInstallment::create(["loan_id"=>$loan->id,"installment_number"=>$i,"due_date"=>$pd->copy(),"principal_amount"=>$principal,"interest_amount"=>$interest,"total_amount"=>$principal+$interest,"paid_amount"=>0,"outstanding_amount"=>$principal+$interest,"status"=>"pending"]);
            $pd->addMonth();
        }
        $app->update(["status"=>"disbursed"]);
        return $loan;
    }
    private function addNote(LoanApplication $app, string $type, string $content, User $admin, bool $internal):void {
        ApplicationNote::create(["application_id"=>$app->id,"created_by"=>$admin->id,"type"=>$type,"content"=>$content,"is_internal"=>$internal]);
    }
}
