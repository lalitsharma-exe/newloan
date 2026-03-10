<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication; use App\Models\LoanProduct;
use App\Services\Admin\ApplicationService;
use Illuminate\Http\Request;
class ApplicationController extends Controller {
    public function __construct(private ApplicationService $svc) {}
    public function index(Request $request) {
        $filters = $request->only(["status","product","date_from","date_to","search"]);
        return view("admin.applications.index",[
            "applications"=>$this->svc->getPaginated($filters),
            "stats"       =>$this->svc->getStats(),
            "products"    =>LoanProduct::active()->get(),
            "filters"     =>$filters,
        ]);
    }
    public function show(LoanApplication $application) {
        $application->load(["user","loanProduct","documents","notes.createdBy","affordability","employment","bankDetails","nextOfKin","creditReport","loan"]);
        return view("admin.applications.show",compact("application"));
    }
    public function approve(Request $request, LoanApplication $application) {
        $request->validate(["approved_amount"=>"required|numeric|min:1","approved_term"=>"required|integer|min:1","interest_rate"=>"required|numeric|min:0","disbursement_date"=>"required|date|after_or_equal:today","notes"=>"nullable|string"]);
        $this->svc->approve($application,$request->all(),auth("admin")->user());
        return redirect()->route("admin.applications.show",$application)->with("success","Application approved and loan created.");
    }
    public function decline(Request $request, LoanApplication $application) {
        $request->validate(["reason"=>"required|string|max:1000"]);
        $this->svc->decline($application,$request->reason,auth("admin")->user());
        return redirect()->route("admin.applications.show",$application)->with("success","Application declined.");
    }
    public function hold(Request $request, LoanApplication $application) {
        $request->validate(["reason"=>"required|string|max:1000"]);
        $this->svc->hold($application,$request->reason,auth("admin")->user());
        return redirect()->route("admin.applications.show",$application)->with("info","Application on hold.");
    }
    public function requestInfo(Request $request, LoanApplication $application) {
        $request->validate(["message"=>"required|string|max:1000"]);
        $this->svc->requestInfo($application,$request->message,auth("admin")->user());
        return redirect()->route("admin.applications.show",$application)->with("info","Info request sent.");
    }
    public function override(Request $request, LoanApplication $application) {
        $request->validate(["loan_amount"=>"required|numeric|min:1","interest_rate"=>"required|numeric|min:0","term_months"=>"required|integer|min:1"]);
        $this->svc->overrideLoanTerms($application,$request->all(),auth("admin")->user());
        return redirect()->route("admin.applications.show",$application)->with("success","Terms updated.");
    }
}
