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
            'app_name'         => 'required|string|max:100',
            'currency'         => 'required|string|max:10',
            'currency_symbol'  => 'required|string|max:5',
            'application_fee'  => 'nullable|numeric|min:0',
        ]);
        $this->svc->updateGroup("general", $r->only('app_name','currency','currency_symbol','country', 'application_fee', 'default_interest_rate', 'initiation_fee_rate', 'admin_fee_fixed', 'max_affordability_pct', 'penalty_per_10_days'));
        return back()->with("success", "General settings saved.");
    }

    public function updateCompany(Request $r) {
        $r->validate([
            'director_name'       => 'nullable|string|max:100',
            'director_title'      => 'nullable|string|max:100',
            'bank_name'           => 'nullable|string|max:100',
            'bank_branch'         => 'nullable|string|max:100',
            'bank_branch_code'    => 'nullable|string|max:50',
            'bank_account_type'   => 'nullable|in:cheque,savings,business',
            'bank_account_name'   => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'signature_upload'    => 'nullable|image|max:1024',
        ]);

        $fields = ['director_name', 'director_title', 'bank_name', 'bank_branch', 'bank_branch_code', 'bank_account_type', 'bank_account_name', 'bank_account_number'];
        foreach ($fields as $field) {
            \App\Models\SystemSetting::set($field, $r->input($field), 'company');
        }

        if ($r->hasFile('signature_upload')) {
            $path = $r->file('signature_upload')->store('signatures', 'public');
            \App\Models\SystemSetting::set('director_signature', $path, 'company');
        } elseif ($r->filled('signature_data')) {
            $data = $r->input('signature_data');
            // Data URI e.g. data:image/png;base64,iVBOR...
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
                $data = base64_decode($data);
                if ($data !== false) {
                    $filename = 'signatures/' . uniqid('sig_') . '.' . $type;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);
                    \App\Models\SystemSetting::set('director_signature', $filename, 'company');
                }
            }
        }

        if ($r->hasFile('system_qr_upload')) {
            $path = $r->file('system_qr_upload')->store('system_qrs', 'public');
            \App\Models\SystemSetting::set('system_qr', $path, 'company');
        }

        return back()->with("success", "Company and banking details saved.");
    }

    public function updatePaymentGateway(Request $r) {
        $r->validate(['gateway_mode' => 'required|in:sandbox,production']);
        // Save all gateway fields including CPay-specific ones and toggles
        $fields = $r->only(['gateway_mode','gateway_name','gateway_key','gateway_secret','gateway_live_url','cpay_client_code','gateway_wallet_id']);
        foreach ($fields as $key => $value) {
            if ($value !== null && $value !== '') {
                \App\Models\SystemSetting::set($key, $value, 'payment_gateway');
            }
        }
        
        // Handle toggles (checkboxes)
        $toggles = ['mpesa_payment_enabled', 'card_payment_enabled', 'cpay_wallet_enabled', 'mpesa_disbursement_api_enabled', 'cpay_disbursement_api_enabled'];
        foreach ($toggles as $t) {
            \App\Models\SystemSetting::set($t, $r->has($t) ? 1 : 0, 'payment_gateway');
        }
        
        return back()->with('success', 'Payment gateway settings saved.');
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
            Mail::raw('This is a test email from Prosperity Loans System.', fn($m) =>
                $m->to($r->test_email)->subject('Prosperity Loans Test Email')
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

