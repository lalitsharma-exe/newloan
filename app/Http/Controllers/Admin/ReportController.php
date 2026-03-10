<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;
class ReportController extends Controller {
    public function __construct(private ReportService $svc) {}
    public function index()                { return view("admin.reports.index"); }
    public function portfolio(Request $r)  { return view("admin.reports.portfolio",["data"=>$this->svc->getPortfolioReport($r->all()),"filters"=>$r->all()]); }
    public function disbursement(Request $r){ return view("admin.reports.disbursement",["data"=>$this->svc->getDisbursementReport($r->all()),"filters"=>$r->all()]); }
    public function repayment(Request $r)  { return view("admin.reports.repayment",["data"=>$this->svc->getRepaymentReport($r->all()),"filters"=>$r->all()]); }
    public function arrears(Request $r)    { return view("admin.reports.arrears",["data"=>$this->svc->getArrearsReport($r->all()),"filters"=>$r->all()]); }
    public function collections(Request $r){ return view("admin.reports.collections",["data"=>$this->svc->getCollectionsReport($r->all()),"filters"=>$r->all()]); }
    public function productPerformance(Request $r){ return view("admin.reports.product-performance",["data"=>$this->svc->getProductPerformanceReport($r->all()),"filters"=>$r->all()]); }
    public function officerPerformance(Request $r){
        $officers = User::loanOfficers()->get();
        return view("admin.reports.officer-performance",["data"=>$this->svc->getOfficerPerformanceReport($r->all()),"filters"=>$r->all(),"officers"=>$officers]);
    }
    public function export(Request $request) {
        $request->validate(["report_type"=>"required|string","format"=>"required|in:pdf,csv","date_from"=>"nullable|date","date_to"=>"nullable|date"]);
        return $this->svc->export($request->report_type,$request->format,$request->all());
    }
}
