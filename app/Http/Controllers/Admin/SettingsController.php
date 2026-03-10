<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Admin\SettingsService;
use Illuminate\Http\Request;
class SettingsController extends Controller {
    public function __construct(private SettingsService $svc) {}
    public function index() { return view("admin.settings.index",["settings"=>$this->svc->getAllGrouped()]); }
    public function updatePaymentGateway(Request $r) {
        $r->validate(["gateway_name"=>"required","gateway_key"=>"required","gateway_secret"=>"required","gateway_mode"=>"required|in:sandbox,production"]);
        $this->svc->updateGroup("payment_gateway",$r->all()); return back()->with("success","Payment gateway saved.");
    }
    public function updateCreditBureau(Request $r) {
        $r->validate(["bureau_provider"=>"required","bureau_api_key"=>"required","bureau_api_url"=>"required|url","bureau_mode"=>"required|in:sandbox,production"]);
        $this->svc->updateGroup("credit_bureau",$r->all()); return back()->with("success","Credit bureau saved.");
    }
    public function updateNotifications(Request $r) {
        $r->validate(["email_from"=>"required|email","email_from_name"=>"required"]);
        $this->svc->updateGroup("notifications",$r->all()); return back()->with("success","Notifications saved.");
    }
    public function updateSecurity(Request $r) {
        $r->validate(["session_timeout"=>"required|integer|min:5","max_login_attempts"=>"required|integer|min:3"]);
        $this->svc->updateGroup("security",$r->all()); return back()->with("success","Security settings saved.");
    }
}
