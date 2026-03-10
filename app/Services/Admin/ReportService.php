<?php
namespace App\Services\Admin;
use App\Models\{Loan,LoanApplication,LoanInstallment,Payment,LoanProduct,User};
class ReportService {
    public function getPortfolioReport(array $f):array {
        $q=Loan::with(["user","loanProduct"]);
        $this->dates($q,$f,"disbursement_date");
        if(!empty($f["product"])) $q->where("loan_product_id",$f["product"]);
        if(!empty($f["status"]))  $q->where("status",$f["status"]);
        $loans=$q->get();
        return ["loans"=>$loans,"total_principal"=>$loans->sum("principal_amount"),"total_outstanding"=>$loans->sum("outstanding_balance"),"by_status"=>$loans->groupBy("status")->map->count(),"by_product"=>$loans->groupBy("loanProduct.name")->map->sum("principal_amount")];
    }
    public function getDisbursementReport(array $f):array {
        $q=Loan::with(["user","loanProduct"]); $this->dates($q,$f,"disbursement_date");
        if(!empty($f["product"])) $q->where("loan_product_id",$f["product"]);
        $loans=$q->latest("disbursement_date")->get();
        return ["loans"=>$loans,"total_amount"=>$loans->sum("principal_amount"),"total_loans"=>$loans->count(),"average"=>$loans->avg("principal_amount")];
    }
    public function getRepaymentReport(array $f):array {
        $q=Payment::with(["loan.user","loan.loanProduct"])->where("status","verified"); $this->dates($q,$f);
        $payments=$q->latest()->get();
        return ["payments"=>$payments,"total"=>$payments->sum("amount"),"by_method"=>$payments->groupBy("method")->map->sum("amount")];
    }
    public function getArrearsReport(array $f):array {
        $q=LoanInstallment::with(["loan.user","loan.loanProduct"])->where("status","overdue");
        if(!empty($f["days_overdue"])) $q->whereDate("due_date","<=",now()->subDays($f["days_overdue"]));
        $inst=$q->orderBy("due_date")->get();
        return ["installments"=>$inst,"total_overdue"=>$inst->sum("outstanding_amount"),"by_bucket"=>["1-30 days"=>$inst->filter(fn($i)=>$i->due_date->diffInDays(now())<=30)->sum("outstanding_amount"),"31-60 days"=>$inst->filter(fn($i)=>$i->due_date->diffInDays(now())>30&&$i->due_date->diffInDays(now())<=60)->sum("outstanding_amount"),"61-90 days"=>$inst->filter(fn($i)=>$i->due_date->diffInDays(now())>60&&$i->due_date->diffInDays(now())<=90)->sum("outstanding_amount"),"90+ days"=>$inst->filter(fn($i)=>$i->due_date->diffInDays(now())>90)->sum("outstanding_amount")]];
    }
    public function getCollectionsReport(array $f):array {
        $q=Payment::with(["loan.user"])->where("status","verified"); $this->dates($q,$f);
        if(!empty($f["method"])) $q->where("method",$f["method"]);
        $payments=$q->latest()->get();
        return ["payments"=>$payments,"total"=>$payments->sum("amount"),"by_method"=>$payments->groupBy("method")->map->sum("amount"),"daily"=>$payments->groupBy(fn($p)=>$p->created_at->format("Y-m-d"))->map->sum("amount")];
    }
    public function getProductPerformanceReport(array $f):array {
        $products=LoanProduct::with("loans")->get()->map(fn($p)=>["product"=>$p,"total_loans"=>$p->loans->count(),"total_disbursed"=>$p->loans->sum("principal_amount"),"outstanding"=>$p->loans->sum("outstanding_balance"),"overdue_count"=>$p->loans->where("status","overdue")->count()]);
        return ["products"=>$products];
    }
    public function getOfficerPerformanceReport(array $f):array {
        $q=LoanApplication::with(["assignedOfficer","user"])->whereNotNull("assigned_officer_id")->where("status","!=","draft");
        if(!empty($f["officer_id"])) $q->where("assigned_officer_id",$f["officer_id"]);
        $this->dates($q,$f);
        $officers=$q->get()->groupBy("assigned_officer_id")->map(fn($apps,$id)=>["officer"=>$apps->first()->assignedOfficer,"total"=>$apps->count(),"approved"=>$apps->where("status","approved")->count(),"declined"=>$apps->where("status","declined")->count(),"avg_amount"=>$apps->where("status","approved")->avg("approved_amount")]);
        return ["officers"=>$officers];
    }
    public function export(string $type, string $format, array $f) {
        $method = "get".str_replace(" ","",ucwords(str_replace("_"," ",$type)))."Report";
        $data = $this->$method($f);
        $filename = $type."_".now()->format("Y-m-d").".csv";
        $headers  = ["Content-Type"=>"text/csv","Content-Disposition"=>"attachment; filename=\"$filename\""];
        return response()->stream(function() use($data){ $fh=fopen("php://output","w"); foreach($data as $k=>$v){ if(is_iterable($v)){ foreach($v as $row){ if(is_object($row) && method_exists($row,"toArray")) fputcsv($fh,$row->toArray()); elseif(is_array($row)) fputcsv($fh,$row); } break; } } fclose($fh); },200,$headers);
    }
    private function dates($q,array $f,string $col="created_at"):void {
        if(!empty($f["date_from"])) $q->whereDate($col,">=",$f["date_from"]);
        if(!empty($f["date_to"]))   $q->whereDate($col,"<=",$f["date_to"]);
    }
}
