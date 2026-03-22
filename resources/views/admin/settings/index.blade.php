@extends('admin.layouts.app')
@section('title','Settings')
@section('page-title','System Settings')
@section('content')

@if(session('success'))<div class="alert a-ok" style="margin-bottom:20px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert a-e" style="margin-bottom:20px"><i class="bi bi-x-circle-fill"></i> {{ session('error') }}</div>@endif

@push('styles')
<style>
.settings-layout {
  display: grid;
  grid-template-columns: 210px 1fr;
  gap: 22px;
  align-items: start;
}
/* ── Sidebar nav ── */
.st-nav {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
  position: sticky;
  top: 76px;
}
.st-nav-head {
  padding: 12px 16px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: var(--muted);
  border-bottom: 1px solid var(--border);
  background: #fafbff;
}
.st-btn {
  display: flex;
  align-items: center;
  gap: 9px;
  width: 100%;
  padding: 11px 16px;
  border: none;
  background: none;
  font-family: inherit;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  text-align: left;
  border-left: 3px solid transparent;
  transition: all .15s;
  line-height: 1;
}
.st-btn i { font-size: 14px; width: 16px; flex-shrink: 0; }
.st-btn:hover { color: var(--dark); background: var(--bg); }
.st-btn.active { color: var(--p); background: rgba(26,92,46,.06); border-left-color: var(--p); font-weight: 600; }
.st-sep { height: 1px; background: var(--border); margin: 4px 0; }

/* ── Section title inside card ── */
.st-section {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--muted);
  padding: 18px 0 10px;
  margin-bottom: 12px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 8px;
}
.st-section:first-of-type { padding-top: 0; }
.st-section i { color: var(--p); font-size: 14px; }

/* ── Signature pad ── */
.sig-box {
  border: 1.5px dashed var(--border);
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
}
#sig-canvas {
  width: 100%;
  height: 120px;
  display: block;
  cursor: crosshair;
}
.sig-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: #fafbff;
  border-top: 1px solid var(--border);
}

/* ── Input with prefix/suffix ── */
.input-addon {
  position: relative;
}
.input-addon .prefix,
.input-addon .suffix {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  font-size: 13px;
  color: var(--muted);
  font-weight: 600;
  pointer-events: none;
}
.input-addon .prefix { left: 11px; }
.input-addon .suffix { right: 11px; }
.input-addon .fc.has-prefix { padding-left: 26px; }
.input-addon .fc.has-suffix { padding-right: 46px; }

/* ── Eye toggle button ── */
.eye-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--muted);
  font-size: 15px;
  padding: 4px;
}
.eye-btn:hover { color: var(--dark); }

/* ── Test connection cards ── */
.test-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }

/* ── Copy toast ── */
#copy-toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  background: var(--p); color: #fff;
  padding: 10px 18px; border-radius: 8px;
  font-size: 13px; font-weight: 600;
  box-shadow: 0 4px 16px rgba(0,0,0,.18);
  opacity: 0; transition: opacity .25s; pointer-events: none;
}
#copy-toast.show { opacity: 1; }

@media(max-width:768px) {
  .settings-layout { grid-template-columns: 1fr; }
  .st-nav { position: static; }
  .test-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

<div class="settings-layout">

  {{-- ── SIDEBAR ──────────────────────────────────── --}}
  <div class="st-nav">
    <div class="st-nav-head">Configuration</div>
    <button class="st-btn active" data-tg="st" data-t="general"   onclick="stab('general')"><i class="bi bi-gear-fill"></i> General</button>
    <button class="st-btn"        data-tg="st" data-t="company"   onclick="stab('company'); setTimeout(setupCanvas,80)"><i class="bi bi-building"></i> Company &amp; Banking</button>
    <div class="st-sep"></div>
    <button class="st-btn"        data-tg="st" data-t="payment"   onclick="stab('payment')"><i class="bi bi-credit-card-fill"></i> Payment Gateway</button>
    <button class="st-btn"        data-tg="st" data-t="credit"    onclick="stab('credit')"><i class="bi bi-shield-check-fill"></i> Credit Bureau</button>
    <div class="st-sep"></div>
    <button class="st-btn"        data-tg="st" data-t="notif"     onclick="stab('notif')"><i class="bi bi-bell-fill"></i> Notifications</button>
    <button class="st-btn"        data-tg="st" data-t="security"  onclick="stab('security')"><i class="bi bi-lock-fill"></i> Security</button>
    <div class="st-sep"></div>
    <button class="st-btn"        data-tg="st" data-t="test"      onclick="stab('test')"><i class="bi bi-wifi"></i> Test Connections</button>
  </div>

  {{-- ── PANELS ───────────────────────────────────── --}}
  <div>

    {{-- ═══════════ GENERAL ═══════════ --}}
    <div id="sp-general" class="sp active">
      <form method="POST" action="{{ route('admin.settings.general') }}">
        @csrf

        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-globe2" style="color:var(--p);margin-right:7px"></i>Application &amp; Currency</span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">Application Name *</label>
                <input type="text" name="app_name" class="fc" value="{{ \App\Models\SystemSetting::get('app_name','MyLoan') }}" required>
              </div>
              <div class="fg">
                <label class="fl">Country</label>
                <input type="text" name="country" class="fc" value="{{ \App\Models\SystemSetting::get('country','Lesotho') }}">
              </div>
              <div class="fg">
                <label class="fl">Currency Code *</label>
                <input type="text" name="currency" class="fc" value="{{ \App\Models\SystemSetting::get('currency','LSL') }}" required>
                <div class="ft">ISO code e.g. LSL</div>
              </div>
              <div class="fg">
                <label class="fl">Currency Symbol *</label>
                <input type="text" name="currency_symbol" class="fc" value="{{ \App\Models\SystemSetting::get('currency_symbol','M') }}" required>
                <div class="ft">Prefix shown before amounts — must be <strong>M</strong></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-calculator" style="color:var(--p);margin-right:7px"></i>Calculation Rules</span></div>
          <div class="card-body">
            <div class="alert a-i" style="margin-bottom:18px">
              <i class="bi bi-info-circle-fill"></i> These are system-wide defaults. Individual loan products can override them.
            </div>
            <div class="g2">
              <div class="fg">
                <label class="fl">Default Interest Rate <span style="font-weight:400;color:var(--muted)">(%/month)</span></label>
                <div class="input-addon">
                  <input type="number" name="default_interest_rate" class="fc has-suffix" value="{{ \App\Models\SystemSetting::get('default_interest_rate',15) }}" step="0.01">
                  <span class="suffix">%</span>
                </div>
              </div>
              <div class="fg">
                <label class="fl">Initiation Fee Rate</label>
                <div class="input-addon">
                  <input type="number" name="initiation_fee_rate" class="fc has-suffix" value="{{ \App\Models\SystemSetting::get('initiation_fee_rate',40) }}" step="0.01">
                  <span class="suffix">%</span>
                </div>
              </div>
              <div class="fg">
                <label class="fl">Admin Fee Fixed <span style="font-weight:400;color:var(--muted)">(M/month)</span></label>
                <div class="input-addon">
                  <span class="prefix">M</span>
                  <input type="number" name="admin_fee_fixed" class="fc has-prefix" value="{{ \App\Models\SystemSetting::get('admin_fee_fixed',50) }}" step="0.01">
                </div>
              </div>
              <div class="fg">
                <label class="fl">Affordability Cap <span style="font-weight:400;color:var(--muted)">(% of net salary)</span></label>
                <div class="input-addon">
                  <input type="number" name="max_affordability_pct" class="fc has-suffix" value="{{ \App\Models\SystemSetting::get('max_affordability_pct',30) }}" step="1">
                  <span class="suffix">%</span>
                </div>
                <div class="ft">Monthly repayment must not exceed this</div>
              </div>
              <div class="fg">
                <label class="fl">Penalty per 10 days overdue</label>
                <div class="input-addon">
                  <span class="prefix">M</span>
                  <input type="number" name="penalty_per_10_days" class="fc has-prefix" value="{{ \App\Models\SystemSetting::get('penalty_per_10_days',20) }}" step="0.01">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="bi bi-floppy-fill"></i> Save General</button>
        </div>
      </form>
    </div>

    {{-- ═══════════ COMPANY & BANKING ═══════════ --}}
    <div id="sp-company" class="sp">
      <form method="POST" action="{{ route('admin.settings.company') }}" enctype="multipart/form-data">
        @csrf

        {{-- Director --}}
        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-person-badge-fill" style="color:var(--p);margin-right:7px"></i>Director Details</span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">Full Name</label>
                <input type="text" name="director_name" class="fc" value="{{ \App\Models\SystemSetting::get('director_name','Tjale Maila') }}">
              </div>
              <div class="fg">
                <label class="fl">Title / Position</label>
                <input type="text" name="director_title" class="fc" value="{{ \App\Models\SystemSetting::get('director_title','Managing Director') }}">
                <div class="ft">Printed on agreements and PDF letters</div>
              </div>
            </div>
          </div>
        </div>

        {{-- Signature --}}
        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr">
            <span class="card-title"><i class="bi bi-pen-fill" style="color:var(--p);margin-right:7px"></i>Director Signature</span>
            <span style="font-size:12px;color:var(--muted)">Upload image or draw below</span>
          </div>
          <div class="card-body">

            @php $sig = \App\Models\SystemSetting::get('director_signature'); @endphp
            @if($sig)
            <div style="display:flex;align-items:center;gap:16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px">
              <div>
                <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px">Current Signature</div>
                <img src="{{ Storage::url($sig) }}" style="max-height:52px;max-width:180px;background:#fff;border:1px solid var(--border);border-radius:6px;padding:5px">
              </div>
              <div style="font-size:12px;color:var(--muted)">Upload a new file or draw below to replace.</div>
            </div>
            @endif

            <div class="fg">
              <label class="fl">Upload Image <span style="font-weight:400;text-transform:none;color:var(--muted)">(PNG with transparent background preferred)</span></label>
              <input type="file" name="signature_upload" accept="image/*" class="fc" style="padding:8px 12px">
            </div>

            <div style="display:flex;align-items:center;gap:12px;margin:14px 0">
              <div style="flex:1;height:1px;background:var(--border)"></div>
              <span style="font-size:11px;font-weight:700;color:var(--muted);letter-spacing:.08em">OR DRAW</span>
              <div style="flex:1;height:1px;background:var(--border)"></div>
            </div>

            <div class="sig-box">
              <canvas id="sig-canvas"></canvas>
              <div class="sig-footer">
                <span style="font-size:12px;color:var(--muted)"><i class="bi bi-pencil" style="margin-right:4px"></i>Sign in the box above</span>
                <button type="button" class="btn btn-o btn-sm" onclick="clearCanvas()"><i class="bi bi-eraser"></i> Clear</button>
              </div>
            </div>
            <input type="hidden" name="signature_data" id="signature_data">

          </div>
        </div>

        {{-- Banking --}}
        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-bank2" style="color:var(--p);margin-right:7px"></i>Banking Details <span style="font-size:12px;font-weight:400;color:var(--muted)">— used on settlement quotations</span></span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">Bank Name</label>
                <input type="text" name="bank_name" class="fc" value="{{ \App\Models\SystemSetting::get('bank_name','Standard Lesotho Bank') }}">
              </div>
              <div class="fg">
                <label class="fl">Branch Name</label>
                <input type="text" name="bank_branch" class="fc" value="{{ \App\Models\SystemSetting::get('bank_branch','City Branch') }}">
              </div>
              <div class="fg">
                <label class="fl">Branch Code</label>
                <input type="text" name="bank_branch_code" class="fc" value="{{ \App\Models\SystemSetting::get('bank_branch_code') }}">
              </div>
              <div class="fg">
                <label class="fl">Account Type</label>
                <select name="bank_account_type" class="fc">
                  @foreach(['cheque'=>'Cheque / Current','savings'=>'Savings','business'=>'Business'] as $v=>$l)
                  <option value="{{ $v }}" {{ \App\Models\SystemSetting::get('bank_account_type')===$v?'selected':'' }}>{{ $l }}</option>
                  @endforeach
                </select>
              </div>
              <div class="fg">
                <label class="fl">Account Name</label>
                <input type="text" name="bank_account_name" class="fc" value="{{ \App\Models\SystemSetting::get('bank_account_name','Myloan Limited') }}">
              </div>
              <div class="fg">
                <label class="fl">Account Number</label>
                <input type="text" name="bank_account_number" class="fc" value="{{ \App\Models\SystemSetting::get('bank_account_number','9080006273560') }}">
              </div>
            </div>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p" onclick="saveSignature()"><i class="bi bi-floppy-fill"></i> Save Company &amp; Banking</button>
        </div>
      </form>
    </div>

    {{-- ═══════════ PAYMENT GATEWAY ═══════════ --}}
    <div id="sp-payment" class="sp">
      <form method="POST" action="{{ route('admin.settings.payment-gateway') }}">
        @csrf
        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-credit-card-fill" style="color:var(--p);margin-right:7px"></i>CPay Integration</span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">Gateway Name</label>
                <input type="text" name="gateway_name" class="fc" value="{{ \App\Models\SystemSetting::get('gateway_name','CPay') }}" placeholder="CPay">
              </div>
              <div class="fg">
                <label class="fl">Mode</label>
                <select name="gateway_mode" class="fc">
                  <option value="sandbox"    {{ \App\Models\SystemSetting::get('gateway_mode','sandbox')==='sandbox'?'selected':'' }}>Sandbox (testing)</option>
                  <option value="production" {{ \App\Models\SystemSetting::get('gateway_mode')==='production'?'selected':'' }}>Production (live)</option>
                </select>
              </div>
              <div class="fg">
                <label class="fl">Public Key</label>
                <input type="text" name="gateway_key" class="fc" value="{{ \App\Models\SystemSetting::get('gateway_key') }}" placeholder="Your CPay public key">
              </div>
              <div class="fg">
                <label class="fl">Private Key</label>
                <div class="input-addon" style="position:relative">
                  <input type="password" id="gwSecret" name="gateway_secret" class="fc has-suffix" placeholder="Your CPay private key" value="{{ \App\Models\SystemSetting::get('gateway_secret') }}">
                  <button type="button" class="eye-btn" onclick="toggleEye('gwSecret',this)"><i class="bi bi-eye"></i></button>
                </div>
              </div>
              <div class="fg">
                <label class="fl">Wallet ID <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                <input type="text" name="gateway_wallet_id" class="fc" value="{{ \App\Models\SystemSetting::get('gateway_wallet_id') }}" placeholder="CPay wallet ID">
              </div>
              <div class="fg">
                <label class="fl">Webhook URL <span style="font-weight:400;color:var(--muted)">(set in CPay dashboard)</span></label>
                <div style="display:flex;gap:8px">
                  <input type="text" class="fc" value="{{ url('/webhooks/payment') }}" readonly style="background:var(--bg);color:var(--muted);font-size:12.5px">
                  <button type="button" class="btn btn-o btn-sm" style="flex-shrink:0" onclick="copyText('{{ url('/webhooks/payment') }}')"><i class="bi bi-copy"></i></button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="bi bi-floppy-fill"></i> Save Gateway</button>
        </div>
      </form>
    </div>

    {{-- ═══════════ CREDIT BUREAU ═══════════ --}}
    <div id="sp-credit" class="sp">
      <form method="POST" action="{{ route('admin.settings.credit-bureau') }}">
        @csrf
        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-shield-check-fill" style="color:var(--p);margin-right:7px"></i>Experian / Credit Bureau</span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">Provider</label>
                <input type="text" name="bureau_provider" class="fc" value="{{ \App\Models\SystemSetting::get('bureau_provider','Experian') }}">
              </div>
              <div class="fg">
                <label class="fl">Mode</label>
                <select name="bureau_mode" class="fc">
                  <option value="sandbox"    {{ \App\Models\SystemSetting::get('bureau_mode','sandbox')==='sandbox'?'selected':'' }}>Sandbox</option>
                  <option value="production" {{ \App\Models\SystemSetting::get('bureau_mode')==='production'?'selected':'' }}>Production</option>
                </select>
              </div>
              <div class="fg">
                <label class="fl">API Key</label>
                <div style="position:relative">
                  <input type="password" id="bureauKey" name="bureau_api_key" class="fc" value="{{ \App\Models\SystemSetting::get('bureau_api_key') }}" placeholder="Experian API key" style="padding-right:42px">
                  <button type="button" class="eye-btn" onclick="toggleEye('bureauKey',this)"><i class="bi bi-eye"></i></button>
                </div>
              </div>
              <div class="fg">
                <label class="fl">API Endpoint URL</label>
                <input type="url" name="bureau_api_url" class="fc" value="{{ \App\Models\SystemSetting::get('bureau_api_url','https://sandbox.experian.com/api') }}">
              </div>
            </div>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="bi bi-floppy-fill"></i> Save Bureau</button>
        </div>
      </form>
    </div>

    {{-- ═══════════ NOTIFICATIONS ═══════════ --}}
    <div id="sp-notif" class="sp">
      <form method="POST" action="{{ route('admin.settings.notifications') }}">
        @csrf

        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-envelope-fill" style="color:var(--p);margin-right:7px"></i>Email Settings</span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">From Email *</label>
                <input type="email" name="email_from" class="fc" value="{{ \App\Models\SystemSetting::get('email_from','noreply@myloan.co.ls') }}" required>
              </div>
              <div class="fg">
                <label class="fl">From Name *</label>
                <input type="text" name="email_from_name" class="fc" value="{{ \App\Models\SystemSetting::get('email_from_name','MyLoan') }}" required>
              </div>
            </div>
          </div>
        </div>

        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr">
            <span class="card-title"><i class="bi bi-phone-fill" style="color:var(--p);margin-right:7px"></i>SMS Settings</span>
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;font-weight:600;margin-bottom:0">
              <input type="checkbox" name="sms_enabled" value="1" id="smsToggle" style="width:16px;height:16px;accent-color:var(--p)" {{ \App\Models\SystemSetting::get('sms_enabled')?'checked':'' }} onchange="document.getElementById('smsFields').style.display=this.checked?'grid':'none'">
              Enable SMS
            </label>
          </div>
          <div class="card-body">
            <div class="g2" id="smsFields" style="display:{{ \App\Models\SystemSetting::get('sms_enabled')?'grid':'none' }}">
              <div class="fg">
                <label class="fl">SMS API Key</label>
                <div style="position:relative">
                  <input type="password" id="smsKey" name="sms_api_key" class="fc" value="{{ \App\Models\SystemSetting::get('sms_api_key') }}" placeholder="Africa's Talking / Twilio" style="padding-right:42px">
                  <button type="button" class="eye-btn" onclick="toggleEye('smsKey',this)"><i class="bi bi-eye"></i></button>
                </div>
              </div>
              <div class="fg">
                <label class="fl">Sender Name <span style="font-weight:400;color:var(--muted)">(max 11 chars)</span></label>
                <input type="text" name="sms_sender" class="fc" value="{{ \App\Models\SystemSetting::get('sms_sender','MyLoan') }}" maxlength="11">
              </div>
            </div>
            @if(!\App\Models\SystemSetting::get('sms_enabled'))
            <div style="font-size:13px;color:var(--muted)">Enable SMS above to configure API settings.</div>
            @endif
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="bi bi-floppy-fill"></i> Save Notifications</button>
        </div>
      </form>
    </div>

    {{-- ═══════════ SECURITY ═══════════ --}}
    <div id="sp-security" class="sp">
      <form method="POST" action="{{ route('admin.settings.security') }}">
        @csrf
        <div class="card" style="margin-bottom:16px">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-lock-fill" style="color:var(--p);margin-right:7px"></i>Access &amp; Authentication</span></div>
          <div class="card-body">
            <div class="g2">
              <div class="fg">
                <label class="fl">Session Timeout *</label>
                <div class="input-addon">
                  <input type="number" name="session_timeout" class="fc has-suffix" value="{{ \App\Models\SystemSetting::get('session_timeout',60) }}" min="5" max="1440" required>
                  <span class="suffix" style="font-size:11px">min</span>
                </div>
                <div class="ft">5 min minimum · 1440 max (24 h)</div>
              </div>
              <div class="fg">
                <label class="fl">Max Login Attempts *</label>
                <input type="number" name="max_login_attempts" class="fc" value="{{ \App\Models\SystemSetting::get('max_login_attempts',5) }}" min="3" max="20" required>
                <div class="ft">Account locks after this many fails</div>
              </div>
              <div class="fg">
                <label class="fl">Password Expiry</label>
                <div class="input-addon">
                  <input type="number" name="password_expiry_days" class="fc has-suffix" value="{{ \App\Models\SystemSetting::get('password_expiry_days',0) }}" min="0">
                  <span class="suffix" style="font-size:11px">days</span>
                </div>
                <div class="ft">Set 0 to never expire</div>
              </div>
            </div>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="bi bi-floppy-fill"></i> Save Security</button>
        </div>
      </form>
    </div>

    {{-- ═══════════ TEST CONNECTIONS ═══════════ --}}
    <div id="sp-test" class="sp">
      <div class="test-grid">

        <div class="card">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-envelope" style="color:var(--p);margin-right:6px"></i>Test Email</span></div>
          <form method="POST" action="{{ route('admin.settings.test-email') }}">
            @csrf
            <div class="card-body">
              <div class="fg" style="margin-bottom:6px">
                <label class="fl">Send to</label>
                <input type="email" name="test_email" class="fc" value="{{ auth('admin')->user()->email }}" required>
              </div>
              <div style="font-size:12px;color:var(--muted)">Uses your current SMTP configuration.</div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--border)">
              <button type="submit" class="btn btn-p btn-sm" style="width:100%;justify-content:center"><i class="bi bi-send-fill"></i> Send Test Email</button>
            </div>
          </form>
        </div>

        <div class="card">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-phone" style="color:var(--p);margin-right:6px"></i>Test SMS</span></div>
          <form method="POST" action="{{ route('admin.settings.test-sms') }}">
            @csrf
            <div class="card-body">
              <div class="fg" style="margin-bottom:6px">
                <label class="fl">Phone Number</label>
                <input type="text" name="test_phone" class="fc" placeholder="+26658478799" required>
              </div>
              <div style="font-size:12px;color:var(--muted)">Uses your configured SMS provider.</div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--border)">
              <button type="submit" class="btn btn-p btn-sm" style="width:100%;justify-content:center"><i class="bi bi-send-fill"></i> Send Test SMS</button>
            </div>
          </form>
        </div>

        <div class="card">
          <div class="card-hdr"><span class="card-title"><i class="bi bi-arrow-repeat" style="color:var(--p);margin-right:6px"></i>Test Gateway</span></div>
          <form method="POST" action="{{ route('admin.settings.test-gateway') }}">
            @csrf
            <div class="card-body">
              <div style="font-size:13px;color:var(--muted);margin-bottom:6px">Ping CPay sandbox to verify your API keys.</div>
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--border)">
              <button type="submit" class="btn btn-p btn-sm" style="width:100%;justify-content:center"><i class="bi bi-wifi"></i> Test Connection</button>
            </div>
          </form>
        </div>

      </div>
    </div>

  </div>
</div>

{{-- Copy toast --}}
<div id="copy-toast">Copied!</div>

@push('scripts')
<script>
// ── Panel switcher ─────────────────────────────────────────────
function stab(name) {
  // Hide all panels
  document.querySelectorAll('.sp').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.st-btn').forEach(b => b.classList.remove('active'));
  // Show target
  document.getElementById('sp-' + name).style.display = 'block';
  // Activate sidebar button
  document.querySelector(`.st-btn[onclick*="'${name}'"]`).classList.add('active');
}
// Init — show first panel, hide rest
document.querySelectorAll('.sp').forEach((p, i) => p.style.display = i === 0 ? 'block' : 'none');

// ── Signature canvas ───────────────────────────────────────────
const canvas = document.getElementById('sig-canvas');
let ctx, isDrawing = false, hasSignature = false;

function setupCanvas() {
  if (!canvas) return;
  const rect = canvas.getBoundingClientRect();
  if (rect.width === 0) return;
  canvas.width  = Math.round(rect.width);
  canvas.height = 120;
  ctx = canvas.getContext('2d');
  ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#111';
}

function pos(e) {
  const r = canvas.getBoundingClientRect();
  const cx = e.touches ? e.touches[0].clientX : e.clientX;
  const cy = e.touches ? e.touches[0].clientY : e.clientY;
  return { x: cx - r.left, y: cy - r.top };
}

if (canvas) {
  setupCanvas();
  window.addEventListener('resize', setupCanvas);
  ['mousedown','touchstart'].forEach(ev => canvas.addEventListener(ev, e => {
    e.preventDefault(); isDrawing = true; hasSignature = true;
    ctx.beginPath(); const p = pos(e); ctx.moveTo(p.x, p.y);
  }, { passive: false }));
  ['mousemove','touchmove'].forEach(ev => canvas.addEventListener(ev, e => {
    if (!isDrawing) return; e.preventDefault();
    const p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke();
  }, { passive: false }));
  ['mouseup','mouseleave','touchend'].forEach(ev => canvas.addEventListener(ev, () => {
    isDrawing = false; ctx.closePath();
  }));
}

function clearCanvas() {
  if (ctx) { ctx.clearRect(0, 0, canvas.width, canvas.height); hasSignature = false; }
  document.getElementById('signature_data').value = '';
}
function saveSignature() {
  if (hasSignature && canvas) document.getElementById('signature_data').value = canvas.toDataURL('image/png');
}

// ── Eye toggle ─────────────────────────────────────────────────
function toggleEye(id, btn) {
  const f = document.getElementById(id);
  const show = f.type === 'password';
  f.type = show ? 'text' : 'password';
  btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
}

// ── Copy to clipboard ──────────────────────────────────────────
function copyText(t) {
  navigator.clipboard.writeText(t).then(() => {
    const el = document.getElementById('copy-toast');
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 2000);
  });
}
</script>
@endpush
@endsection