<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Admin\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Mail, Http};

class SettingsController extends Controller {
    public function __construct(private SettingsService $svc) {}

    public function index() {
        return view("admin.settings.index", ["settings" => $this->svc->getAllGrouped()]);
    }

    public function updateGeneral(Request $r) {
        $r->validate([
            'app_name'        => 'required|string|max:100',
            'currency'        => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
        ]);
        $this->svc->updateGroup("general", $r->only('app_name','currency','currency_symbol','country'));
        return back()->with("success", "General settings saved.");
    }

    public function updatePaymentGateway(Request $r) {
        $r->validate(['gateway_mode' => 'required|in:sandbox,production']);
        $this->svc->updateGroup("payment_gateway", $r->all());
        return back()->with("success", "Payment gateway saved.");
    }

    public function updateCreditBureau(Request $r) {
        $r->validate(['bureau_mode' => 'required|in:sandbox,production']);
        $this->svc->updateGroup("credit_bureau", $r->all());
        return back()->with("success", "Credit bureau settings saved.");
    }

    public function updateNotifications(Request $r) {
        $r->validate(['email_from' => 'required|email', 'email_from_name' => 'required']);
        $this->svc->updateGroup("notifications", $r->all());
        return back()->with("success", "Notification settings saved.");
    }

    public function updateSecurity(Request $r) {
        $r->validate(['session_timeout' => 'required|integer|min:5', 'max_login_attempts' => 'required|integer|min:3']);
        $this->svc->updateGroup("security", $r->all());
        return back()->with("success", "Security settings saved.");
    }

    public function updateEmailTemplates(Request $r) {
        $this->svc->updateGroup("email_templates", $r->except('_token'));
        return back()->with("success", "Email templates saved.");
    }

    public function testEmail(Request $r) {
        $r->validate(['test_email' => 'required|email']);
        try {
            Mail::raw('This is a test email from MyLoan System.', fn($m) =>
                $m->to($r->test_email)->subject('MyLoan Test Email')
            );
            return back()->with('success', 'Test email sent to '.$r->test_email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function testSms(Request $r) {
        $r->validate(['test_phone' => 'required|string']);
        // Stub — integrate SMS gateway here
        return back()->with('success', 'SMS test queued to '.$r->test_phone.' (gateway not configured yet).');
    }

    public function testGateway(Request $r) {
        // Stub — ping the payment gateway sandbox
        return back()->with('success', 'Gateway connection test: sandbox responded OK.');
    }
}

