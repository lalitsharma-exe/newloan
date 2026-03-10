<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Admin\PaymentService;
use Illuminate\Http\Request;
class PaymentController extends Controller {
    public function __construct(private PaymentService $svc) {}
    public function index(Request $request) {
        $filters = $request->only(["status","method","date_from","date_to","search"]);
        return view("admin.payments.index",[
            "payments"=>$this->svc->getPaginated($filters),
            "stats"   =>$this->svc->getStats(),
            "filters" =>$filters,
        ]);
    }
    public function show(Payment $payment) {
        $payment->load(["loan.user","installment"]);
        return view("admin.payments.show",compact("payment"));
    }
    public function verify(Request $request, Payment $payment) {
        $request->validate(["status"=>"required|in:verified,rejected","notes"=>"nullable|string"]);
        $this->svc->verify($payment,$request->status,$request->notes,auth("admin")->user());
        return redirect()->route("admin.payments.show",$payment)->with("success","Payment ".$request->status.".");
    }
}
