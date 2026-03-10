<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\CreditReport; use App\Models\User;
use App\Services\Admin\CreditBureauService;
use Illuminate\Http\Request;
class CreditBureauController extends Controller {
    public function __construct(private CreditBureauService $svc) {}
    public function index() {
        $reports = CreditReport::with("user")->latest()->paginate(20);
        return view("admin.credit.index",compact("reports"));
    }
    public function pullReport(Request $request) {
        $request->validate(["user_id"=>"required|exists:users,id","national_id"=>"required|string","check_type"=>"required|in:soft,hard"]);
        $result = $this->svc->pullReport(User::findOrFail($request->user_id),$request->national_id,$request->check_type);
        return $result["success"]
            ? redirect()->route("admin.credit.view-report",$result["report_id"])->with("success","Credit report retrieved.")
            : back()->with("error","Failed: ".$result["message"]);
    }
    public function submitMonthly() {
        $result = $this->svc->submitMonthlyFile();
        return back()->with($result["success"]?"success":"error",$result["success"]?"Submitted ".$result["count"]." records.":$result["message"]);
    }
    public function viewReport(CreditReport $report) {
        $report->load("user");
        return view("admin.credit.report",compact("report"));
    }
}
