@extends('admin.layouts.app')

@section('title','Settings')
@section('page-title','System Settings')

@section('content')

<div class="tabs mb6">

<button class="tab active" data-tg="settings" data-t="payment" onclick="switchTab('settings','payment')">
<i class="bi bi-credit-card"></i> Payment Gateway
</button>

<button class="tab" data-tg="settings" data-t="credit" onclick="switchTab('settings','credit')">
<i class="bi bi-shield-check"></i> Credit Bureau
</button>

<button class="tab" data-tg="settings" data-t="notif" onclick="switchTab('settings','notif')">
<i class="bi bi-bell"></i> Notifications
</button>

<button class="tab" data-tg="settings" data-t="security" onclick="switchTab('settings','security')">
<i class="bi bi-lock"></i> Security
</button>

</div>



{{-- PAYMENT GATEWAY --}}

<div class="tpanel active" data-pg="settings" data-p="payment">

<div class="card" style="max-width:700px">

<div class="card-hdr">
<span class="card-title">Payment Gateway</span>
</div>

<form method="POST" action="{{ route('admin.settings.payment-gateway') }}">
@csrf

<div class="card-body">

<div class="g2">

<div class="fg">
<label class="fl">Gateway Name</label>
<input type="text" name="gateway_name" class="fc"
value="{{ \App\Models\SystemSetting::get('gateway_name','stripe') }}">
</div>

<div class="fg">
<label class="fl">Mode</label>
<select name="gateway_mode" class="fc">
<option value="sandbox" {{ \App\Models\SystemSetting::get('gateway_mode')==='sandbox'?'selected':'' }}>Sandbox</option>
<option value="production" {{ \App\Models\SystemSetting::get('gateway_mode')==='production'?'selected':'' }}>Production</option>
</select>
</div>

<div class="fg">
<label class="fl">API Key</label>
<input type="text" name="gateway_key" class="fc"
value="{{ \App\Models\SystemSetting::get('gateway_key') }}"
placeholder="pk_test_…">
</div>

<div class="fg">
<label class="fl">API Secret</label>
<input type="password" name="gateway_secret" class="fc"
placeholder="sk_test_…">
</div>

<div class="fg" style="grid-column:span 2">
<label class="fl">Webhook Secret</label>
<input type="text" name="webhook_secret" class="fc"
value="{{ \App\Models\SystemSetting::get('webhook_secret') }}">
</div>

</div>

</div>

<div style="padding:16px 24px;border-top:1px solid var(--border);text-align:right">
<button class="btn btn-p">
<i class="bi bi-save"></i> Save
</button>
</div>

</form>

</div>

</div>



{{-- CREDIT BUREAU --}}

<div class="tpanel" data-pg="settings" data-p="credit">

<div class="card" style="max-width:700px">

<div class="card-hdr">
<span class="card-title">Credit Bureau API</span>
</div>

<form method="POST" action="{{ route('admin.settings.credit-bureau') }}">
@csrf

<div class="card-body">

<div class="g2">

<div class="fg">
<label class="fl">Provider</label>
<input type="text" name="bureau_provider" class="fc"
value="{{ \App\Models\SystemSetting::get('bureau_provider','Experian') }}">
</div>

<div class="fg">
<label class="fl">Mode</label>
<select name="bureau_mode" class="fc">
<option value="sandbox" {{ \App\Models\SystemSetting::get('bureau_mode')==='sandbox'?'selected':'' }}>Sandbox</option>
<option value="production">Production</option>
</select>
</div>

<div class="fg">
<label class="fl">API Key</label>
<input type="text" name="bureau_api_key" class="fc"
value="{{ \App\Models\SystemSetting::get('bureau_api_key') }}">
</div>

<div class="fg">
<label class="fl">API URL</label>
<input type="url" name="bureau_api_url" class="fc"
value="{{ \App\Models\SystemSetting::get('bureau_api_url','https://sandbox.experian.com/api') }}">
</div>

</div>

</div>

<div style="padding:16px 24px;border-top:1px solid var(--border);text-align:right">
<button class="btn btn-p">
<i class="bi bi-save"></i> Save
</button>
</div>

</form>

</div>

</div>



{{-- NOTIFICATIONS --}}

<div class="tpanel" data-pg="settings" data-p="notif">

<div class="card" style="max-width:700px">

<div class="card-hdr">
<span class="card-title">Notification Settings</span>
</div>

<form method="POST" action="{{ route('admin.settings.notifications') }}">
@csrf

<div class="card-body">

<div class="g2">

<div class="fg">
<label class="fl">From Email</label>
<input type="email" name="email_from" class="fc"
value="{{ \App\Models\SystemSetting::get('email_from','noreply@loanplatform.com') }}">
</div>

<div class="fg">
<label class="fl">From Name</label>
<input type="text" name="email_from_name" class="fc"
value="{{ \App\Models\SystemSetting::get('email_from_name','LoanPlatform') }}">
</div>

<div class="fg" style="grid-column:span 2">

<label style="display:flex;align-items:center;gap:8px">

<input type="checkbox" name="sms_enabled" value="1">
Enable SMS notifications

</label>

</div>

</div>

</div>

<div style="padding:16px 24px;border-top:1px solid var(--border);text-align:right">
<button class="btn btn-p">
<i class="bi bi-save"></i> Save
</button>
</div>

</form>

</div>

</div>



{{-- SECURITY --}}

<div class="tpanel" data-pg="settings" data-p="security">

<div class="card" style="max-width:700px">

<div class="card-hdr">
<span class="card-title">Security Settings</span>
</div>

<form method="POST" action="{{ route('admin.settings.security') }}">
@csrf

<div class="card-body">

<div class="g2">

<div class="fg">
<label class="fl">Session Timeout (minutes)</label>
<input type="number" name="session_timeout" class="fc"
value="{{ \App\Models\SystemSetting::get('session_timeout',60) }}">
</div>

<div class="fg">
<label class="fl">Max Login Attempts</label>
<input type="number" name="max_login_attempts" class="fc"
value="{{ \App\Models\SystemSetting::get('max_login_attempts',5) }}">
</div>

<div class="fg">
<label class="fl">Password Expiry (days)</label>
<input type="number" name="password_expiry_days" class="fc"
value="{{ \App\Models\SystemSetting::get('password_expiry_days',90) }}">
</div>

</div>

</div>

<div style="padding:16px 24px;border-top:1px solid var(--border);text-align:right">
<button class="btn btn-p">
<i class="bi bi-save"></i> Save
</button>
</div>

</form>

</div>

</div>

@endsection