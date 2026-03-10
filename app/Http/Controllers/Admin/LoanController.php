<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Loan; use App\Models\LoanProduct;
use App\Services\Admin\LoanService;
use Illuminate\Http\Request;
class LoanController extends Controller {
    public function __construct(private LoanService $svc) {}
    public function index(Request $request) {
        $filters = $request->only(["status","product","date_from","date_to","search","overdue"]);
        return view("admin.loans.index",[
            "loans"   =>$this->svc->getPaginated($filters),
            "stats"   =>$this->svc->getStats(),
            "products"=>LoanProduct::active()->get(),
            "filters" =>$filters,
        ]);
    }
    public function show(Loan $loan) {
        $loan->load(["user","loanProduct","application","installments","payments"]);
        return view("admin.loans.show",compact("loan"));
    }
    public function adjustSchedule(Request $request, Loan $loan) {
        $request->validate(["installments"=>"required|array","reason"=>"required|string"]);
        $this->svc->adjustSchedule($loan,$request->installments,$request->reason,auth("admin")->user());
        return redirect()->route("admin.loans.show",$loan)->with("success","Schedule updated.");
    }
    public function markPayment(Request $request, Loan $loan) {
        $request->validate(["installment_id"=>"required|exists:loan_installments,id","amount"=>"required|numeric|min:0.01","payment_date"=>"required|date","method"=>"required|in:cash,bank_transfer,mobile_money,card"]);
        $this->svc->recordManualPayment($loan,$request->all(),auth("admin")->user());
        return redirect()->route("admin.loans.show",$loan)->with("success","Payment recorded.");
    }
    public function close(Request $request, Loan $loan) {
        $request->validate(["reason"=>"required|string"]);
        $this->svc->closeLoan($loan,$request->reason,auth("admin")->user());
        return redirect()->route("admin.loans.show",$loan)->with("success","Loan closed.");
    }
    public function downloadAgreement(Loan $loan) {
        $loan->load(["user","loanProduct","installments"]);
        $pdf = app("dompdf.wrapper")->loadView("admin.loans.agreement-pdf",compact("loan"));
        return $pdf->download("loan-agreement-".$loan->loan_number.".pdf");
    }
}
