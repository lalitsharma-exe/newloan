@extends('admin.layouts.app')
@section('title','Settings')
@section('page-title','System Settings')
@section('content')

@if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert a-e"><i class="bi bi-x-circle-fill"></i> {{ session('error') }}</div>@endif

<div class="tabs mb6">
  <button class="tab active" data-tg="st" data-t="general"  onclick="switchTab('st','general')"><i class="bi bi-gear"></i> General</button>
  <button class="tab"        data-tg="st" data-t="payment"  onclick="switchTab('st','payment')"><i class="bi bi-credit-card"></i> Payment Gateway</button>
  <button class="tab"        data-tg="st" data-t="credit"   onclick="switchTab('st','credit')"><i class="bi bi-shield-check"></i> Credit Bureau</button>
  <button class="tab"        data-tg="st" data-t="notif"    onclick="switchTab('st','notif')"><i class="bi bi-bell"></i> Notifications</button>
  <button class="tab"        data-tg="st" data-t="security" onclick="switchTab('st','security')"><i class="bi bi-lock"></i> Security</button>
  <button class="tab"        data-tg="st" data-t="test"     onclick="switchTab('st','test')"><i class="bi bi-wifi"></i> Test Connections</button>
</div>

{{-- GENERAL --}}
<div class="tpanel active" data-pg="st" data-p="general">
<div class="card" style="max-width:700px">
  <div class="card-hdr"><span class="card-title">General Settings</span></div>
  <form method="POST" action="{{ route('admin.settings.general') }}">
    @csrf
    <div class="card-body">
      <div class="g2" style="gap:16px">
        <div class="fg"><label class="fl">Application Name *</label>
          <input type="text" name="app_name" class="fc" value="{{ \App\Models\SystemSetting::get('app_name','MyLoan') }}" required></div>
        <div class="fg"><label class="fl">Country</label>
          <input type="text" name="country" class="fc" value="{{ \App\Models\SystemSetting::get('country','Lesotho') }}"></div>
        <div class="fg"><label class="fl">Currency Code *</label>
          <input type="text" name="currency" class="fc" value="{{ \App\Models\SystemSetting::get('currency','LSL') }}" required></div>
        <div class="fg"><label class="fl">Currency Symbol *</label>
          <input type="text" name="currency_symbol" class="fc" value="{{ \App\Models\SystemSetting::get('currency_symbol','M') }}" required></div>
      </div>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;margin-top:10px">
        <div style="font-weight:600;font-size:13px;color:#065f46;margin-bottom:10px"><i class="bi bi-calculator"></i> MyLoan Calculation Rules</div>
        <div class="g2" style="gap:12px">
          <div class="fg" style="margin-bottom:0"><label class="fl">Default Interest Rate (%/month)</label>
            <input type="number" name="default_interest_rate" class="fc" value="{{ \App\Models\SystemSetting::get('default_interest_rate',15) }}" step="0.01"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl">Initiation Fee Rate (%)</label>
            <input type="number" name="initiation_fee_rate" class="fc" value="{{ \App\Models\SystemSetting::get('initiation_fee_rate',40) }}" step="0.01"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl">Admin Fee Fixed (M/month)</label>
            <input type="number" name="admin_fee_fixed" class="fc" value="{{ \App\Models\SystemSetting::get('admin_fee_fixed',50) }}" step="0.01"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl">Affordability Limit (%)</label>
            <input type="number" name="max_affordability_pct" class="fc" value="{{ \App\Models\SystemSetting::get('max_affordability_pct',30) }}" step="1"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl">Penalty per 10 days (M)</label>
            <input type="number" name="penalty_per_10_days" class="fc" value="{{ \App\Models\SystemSetting::get('penalty_per_10_days',20) }}" step="0.01"></div>
        </div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
      <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save General</button>
    </div>
  </form>
</div>
</div>

{{-- PAYMENT GATEWAY --}}
<div class="tpanel" data-pg="st" data-p="payment">
<div class="card" style="max-width:700px">
  <div class="card-hdr"><span class="card-title">Payment Gateway</span></div>
  <form method="POST" action="{{ route('admin.settings.payment-gateway') }}">
    @csrf
    <div class="card-body">
      <div class="g2" style="gap:16px">
        <div class="fg"><label class="fl">Gateway Name</label><input type="text" name="gateway_name" class="fc" value="{{ \App\Models\SystemSetting::get('gateway_name','') }}" placeholder="e.g. Stripe, PayFast"></div>
        <div class="fg"><label class="fl">Mode</label>
          <select name="gateway_mode" class="fc">
            <option value="sandbox" {{ \App\Models\SystemSetting::get('gateway_mode')==='sandbox'?'selected':'' }}>Sandbox (testing)</option>
            <option value="production" {{ \App\Models\SystemSetting::get('gateway_mode')==='production'?'selected':'' }}>Production (live)</option>
          </select></div>
        <div class="fg"><label class="fl">API Key</label><input type="text" name="gateway_key" class="fc" value="{{ \App\Models\SystemSetting::get('gateway_key') }}" placeholder="pk_test_…"></div>
        <div class="fg"><label class="fl">API Secret</label><input type="password" name="gateway_secret" class="fc" placeholder="sk_test_…"></div>
        <div class="fg" style="grid-column:span 2"><label class="fl">Webhook Secret</label><input type="text" name="webhook_secret" class="fc" value="{{ \App\Models\SystemSetting::get('webhook_secret') }}"></div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
      <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Gateway</button>
    </div>
  </form>
</div>
</div>

{{-- CREDIT BUREAU --}}
<div class="tpanel" data-pg="st" data-p="credit">
<div class="card" style="max-width:700px">
  <div class="card-hdr"><span class="card-title">Credit Bureau API</span></div>
  <form method="POST" action="{{ route('admin.settings.credit-bureau') }}">
    @csrf
    <div class="card-body">
      <div class="g2" style="gap:16px">
        <div class="fg"><label class="fl">Provider</label><input type="text" name="bureau_provider" class="fc" value="{{ \App\Models\SystemSetting::get('bureau_provider','Experian') }}"></div>
        <div class="fg"><label class="fl">Mode</label>
          <select name="bureau_mode" class="fc">
            <option value="sandbox" {{ \App\Models\SystemSetting::get('bureau_mode','sandbox')==='sandbox'?'selected':'' }}>Sandbox</option>
            <option value="production" {{ \App\Models\SystemSetting::get('bureau_mode')==='production'?'selected':'' }}>Production</option>
          </select></div>
        <div class="fg"><label class="fl">API Key</label><input type="text" name="bureau_api_key" class="fc" value="{{ \App\Models\SystemSetting::get('bureau_api_key') }}"></div>
        <div class="fg"><label class="fl">API URL</label><input type="url" name="bureau_api_url" class="fc" value="{{ \App\Models\SystemSetting::get('bureau_api_url','https://sandbox.experian.com/api') }}"></div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
      <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Bureau</button>
    </div>
  </form>
</div>
</div>

{{-- NOTIFICATIONS --}}
<div class="tpanel" data-pg="st" data-p="notif">
<div class="card" style="max-width:700px">
  <div class="card-hdr"><span class="card-title">Notification Settings</span></div>
  <form method="POST" action="{{ route('admin.settings.notifications') }}">
    @csrf
    <div class="card-body">
      <div class="g2" style="gap:16px">
        <div class="fg"><label class="fl">From Email *</label><input type="email" name="email_from" class="fc" value="{{ \App\Models\SystemSetting::get('email_from','noreply@myloan.co.ls') }}" required></div>
        <div class="fg"><label class="fl">From Name *</label><input type="text" name="email_from_name" class="fc" value="{{ \App\Models\SystemSetting::get('email_from_name','MyLoan') }}" required></div>
        <div class="fg" style="grid-column:span 2">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600">
            <input type="checkbox" name="sms_enabled" value="1" {{ \App\Models\SystemSetting::get('sms_enabled') ? 'checked':'' }}>
            Enable SMS notifications (requires SMS gateway API key)
          </label>
        </div>
        <div class="fg"><label class="fl">SMS API Key</label><input type="text" name="sms_api_key" class="fc" value="{{ \App\Models\SystemSetting::get('sms_api_key') }}" placeholder="Africa's Talking / Twilio key"></div>
        <div class="fg"><label class="fl">SMS Sender Name</label><input type="text" name="sms_sender" class="fc" value="{{ \App\Models\SystemSetting::get('sms_sender','MyLoan') }}"></div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
      <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Notifications</button>
    </div>
  </form>
</div>
</div>

{{-- SECURITY --}}
<div class="tpanel" data-pg="st" data-p="security">
<div class="card" style="max-width:700px">
  <div class="card-hdr"><span class="card-title">Security Settings</span></div>
  <form method="POST" action="{{ route('admin.settings.security') }}">
    @csrf
    <div class="card-body">
      <div class="g2" style="gap:16px">
        <div class="fg"><label class="fl">Session Timeout (minutes) *</label><input type="number" name="session_timeout" class="fc" value="{{ \App\Models\SystemSetting::get('session_timeout',60) }}" min="5" max="1440" required></div>
        <div class="fg"><label class="fl">Max Login Attempts *</label><input type="number" name="max_login_attempts" class="fc" value="{{ \App\Models\SystemSetting::get('max_login_attempts',5) }}" min="3" max="20" required></div>
        <div class="fg"><label class="fl">Password Expiry (days, 0 = never)</label><input type="number" name="password_expiry_days" class="fc" value="{{ \App\Models\SystemSetting::get('password_expiry_days',0) }}" min="0"></div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
      <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Security</button>
    </div>
  </form>
</div>
</div>

{{-- TEST CONNECTIONS --}}
<div class="tpanel" data-pg="st" data-p="test">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;max-width:900px">

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-envelope"></i> Test Email</span></div>
    <form method="POST" action="{{ route('admin.settings.test-email') }}">
      @csrf
      <div class="card-body">
        <div class="fg"><label class="fl">Send test to</label><input type="email" name="test_email" class="fc" value="{{ auth('admin')->user()->email }}" required></div>
      </div>
      <div style="padding:12px 22px;border-top:1px solid var(--border);text-align:right">
        <button type="submit" class="btn btn-i btn-sm"><i class="bi bi-send"></i> Send Test</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-phone"></i> Test SMS</span></div>
    <form method="POST" action="{{ route('admin.settings.test-sms') }}">
      @csrf
      <div class="card-body">
        <div class="fg"><label class="fl">Send test to (phone)</label><input type="text" name="test_phone" class="fc" placeholder="+26612345678" required></div>
      </div>
      <div style="padding:12px 22px;border-top:1px solid var(--border);text-align:right">
        <button type="submit" class="btn btn-i btn-sm"><i class="bi bi-send"></i> Send Test</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-wifi"></i> Test Gateway</span></div>
    <form method="POST" action="{{ route('admin.settings.test-gateway') }}">
      @csrf
      <div class="card-body" style="padding-bottom:10px">
        <p style="font-size:13px;color:var(--muted)">Ping the payment gateway sandbox to check connectivity.</p>
      </div>
      <div style="padding:12px 22px;border-top:1px solid var(--border);text-align:right">
        <button type="submit" class="btn btn-i btn-sm"><i class="bi bi-arrow-repeat"></i> Test</button>
      </div>
    </form>
  </div>

</div>
</div>

@endsection