@extends('admin.layouts.app')
@section('title','Settings')
@section('page-title','System Settings')
@section('content')
<div class="tabs mb-6">
    <button class="tab-btn active" data-tab-group="settings" data-tab="payment" onclick="switchTab('settings','payment')"><i class="bi bi-credit-card"></i> Payment Gateway</button>
    <button class="tab-btn" data-tab-group="settings" data-tab="credit" onclick="switchTab('settings','credit')"><i class="bi bi-shield-check"></i> Credit Bureau</button>
    <button class="tab-btn" data-tab-group="settings" data-tab="notif" onclick="switchTab('settings','notif')"><i class="bi bi-bell"></i> Notifications</button>
    <button class="tab-btn" data-tab-group="settings" data-tab="security" onclick="switchTab('settings','security')"><i class="bi bi-lock"></i> Security</button>
</div>

<div class="tab-panel active" data-panel-group="settings" data-panel="payment">
<div class="card" style="max-width:700px">
    <div class="card-header"><span class="card-title">Payment Gateway</span></div>
    <form method="POST" action="{{ route('admin.settings.payment-gateway') }}">
        @csrf
        <div class="card-body">
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group"><label class="form-label">Gateway Name</label><input type="text" name="gateway_name" class="form-control" value="{{ \App\Models\SystemSetting::get('gateway_name','stripe') }}"></div>
                <div class="form-group"><label class="form-label">Mode</label><select name="gateway_mode" class="form-control"><option value="sandbox" {{ \App\Models\SystemSetting::get('gateway_mode')==='sandbox'?'selected':'' }}>Sandbox</option><option value="production" {{ \App\Models\SystemSetting::get('gateway_mode')==='production'?'selected':'' }}>Production</option></select></div>
                <div class="form-group"><label class="form-label">API Key</label><input type="text" name="gateway_key" class="form-control" value="{{ \App\Models\SystemSetting::get('gateway_key') }}" placeholder="pk_test_…"></div>
                <div class="form-group"><label class="form-label">API Secret</label><input type="password" name="gateway_secret" class="form-control" placeholder="sk_test_…"></div>
                <div class="form-group" style="grid-column:span 2"><label class="form-label">Webhook Secret</label><input type="text" name="webhook_secret" class="form-control" value="{{ \App\Models\SystemSetting::get('webhook_secret') }}"></div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;text-align:right"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button></div>
    </form>
</div>
</div>

<div class="tab-panel" data-panel-group="settings" data-panel="credit">
<div class="card" style="max-width:700px">
    <div class="card-header"><span class="card-title">Credit Bureau API</span></div>
    <form method="POST" action="{{ route('admin.settings.credit-bureau') }}">
        @csrf
        <div class="card-body">
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group"><label class="form-label">Provider</label><input type="text" name="bureau_provider" class="form-control" value="{{ \App\Models\SystemSetting::get('bureau_provider','Experian') }}"></div>
                <div class="form-group"><label class="form-label">Mode</label><select name="bureau_mode" class="form-control"><option value="sandbox" {{ \App\Models\SystemSetting::get('bureau_mode')==='sandbox'?'selected':'' }}>Sandbox</option><option value="production">Production</option></select></div>
                <div class="form-group"><label class="form-label">API Key</label><input type="text" name="bureau_api_key" class="form-control" value="{{ \App\Models\SystemSetting::get('bureau_api_key') }}"></div>
                <div class="form-group"><label class="form-label">API URL</label><input type="url" name="bureau_api_url" class="form-control" value="{{ \App\Models\SystemSetting::get('bureau_api_url','https://sandbox.experian.com/api') }}"></div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;text-align:right"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button></div>
    </form>
</div>
</div>

<div class="tab-panel" data-panel-group="settings" data-panel="notif">
<div class="card" style="max-width:700px">
    <div class="card-header"><span class="card-title">Notification Settings</span></div>
    <form method="POST" action="{{ route('admin.settings.notifications') }}">
        @csrf
        <div class="card-body">
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group"><label class="form-label">From Email</label><input type="email" name="email_from" class="form-control" value="{{ \App\Models\SystemSetting::get('email_from','noreply@loanplatform.com') }}"></div>
                <div class="form-group"><label class="form-label">From Name</label><input type="text" name="email_from_name" class="form-control" value="{{ \App\Models\SystemSetting::get('email_from_name','LoanPlatform') }}"></div>
                <div class="form-group" style="grid-column:span 2"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="sms_enabled" value="1"> Enable SMS notifications</label></div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;text-align:right"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button></div>
    </form>
</div>
</div>

<div class="tab-panel" data-panel-group="settings" data-panel="security">
<div class="card" style="max-width:700px">
    <div class="card-header"><span class="card-title">Security Settings</span></div>
    <form method="POST" action="{{ route('admin.settings.security') }}">
        @csrf
        <div class="card-body">
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group"><label class="form-label">Session Timeout (minutes)</label><input type="number" name="session_timeout" class="form-control" value="{{ \App\Models\SystemSetting::get('session_timeout',60) }}" min="5" max="1440"></div>
                <div class="form-group"><label class="form-label">Max Login Attempts</label><input type="number" name="max_login_attempts" class="form-control" value="{{ \App\Models\SystemSetting::get('max_login_attempts',5) }}" min="3" max="20"></div>
                <div class="form-group"><label class="form-label">Password Expiry (days, 0=never)</label><input type="number" name="password_expiry_days" class="form-control" value="{{ \App\Models\SystemSetting::get('password_expiry_days',90) }}" min="0"></div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;text-align:right"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button></div>
    </form>
</div>
</div>
@endsection
