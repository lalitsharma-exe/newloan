<?php
namespace App\Services\Admin;
use App\Models\{Loan,LoanInstallment,Payment,ApplicationNote,User};
use Illuminate\Support\Str;
class LoanService {
    public function getPaginated(array $f) {
        $q=Loan::with(["user","loanProduct"]);
        if (!empty($f["status"]))  $q->where("status",$f["status"]);
        if (!empty($f["product"])) $q->where("loan_product_id",$f["product"]);
        if (!empty($f["overdue"])) $q->where("status","overdue");
        if (!empty($f["date_from"])) $q->whereDate("disbursement_date",">=",$f["date_from"]);
        if (!empty($f["date_to"]))   $q->whereDate("disbursement_date","<=",$f["date_to"]);
        if (!empty($f["search"])) { $s=$f["search"]; $q->where(fn($x)=>$x->where("loan_number","like","%$s%")->orWhereHas("user",fn($u)=>$u->where("name","like","%$s%"))); }
        return $q->latest()->paginate(20);
    }
    public function getStats():array {
        return ["total_active"=>Loan::active()->count(),"total_overdue"=>Loan::overdue()->count(),"total_portfolio"=>Loan::active()->sum("outstanding_balance"),"paid_off"=>Loan::where("status","paid_off")->count()];
    }
    public function adjustSchedule(Loan $loan, array $installments, string $reason, User $admin):void {
        foreach($installments as $d) LoanInstallment::find($d["id"])->update(["total_amount"=>$d["amount"],"outstanding_amount"=>$d["amount"],"due_date"=>$d["due_date"]]);
        if($loan->application_id) ApplicationNote::create(["application_id"=>$loan->application_id,"created_by"=>$admin->id,"type"=>"schedule_adjustment","content"=>"Schedule adjusted: $reason","is_internal"=>true]);
    }
    public function recordManualPayment(Loan $loan, array $data, User $admin):Payment {
        $inst=LoanInstallment::find($data["installment_id"]);
        $payment=Payment::create(["payment_reference"=>"PAY-".strtoupper(Str::random(8)),"loan_id"=>$loan->id,"installment_id"=>$inst->id,"user_id"=>$loan->user_id,"amount"=>$data["amount"],"method"=>$data["method"],"reference"=>$data["reference"]??null,"status"=>"verified","is_manual"=>true,"verified_by"=>$admin->id,"verified_at"=>now(),"notes"=>"Manual payment on ".$data["payment_date"]]);
        $newPaid=$inst->paid_amount+$data["amount"];
        $status=$newPaid>=$inst->total_amount?"paid":"partial";
        $inst->update(["paid_amount"=>$newPaid,"outstanding_amount"=>max(0,$inst->total_amount-$newPaid),"status"=>$status,"paid_at"=>$status==="paid"?now():null]);
        $loan->decrement("outstanding_balance",$data["amount"]);
        if($loan->fresh()->outstanding_balance<=0) $loan->update(["status"=>"paid_off","last_payment_date"=>now()]);
        return $payment;
    }
    public function closeLoan(Loan $loan, string $reason, User $admin):void {
        $loan->update(["status"=>"closed","closed_at"=>now(),"closed_reason"=>$reason,"closed_by"=>$admin->id]);
    }
}
