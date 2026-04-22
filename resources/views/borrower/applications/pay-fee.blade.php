@extends('borrower.layouts.app')
@section('title', 'Application Fee')

@section('content')

<style>
.pay-method-btn {
  cursor:pointer;
  border:2px solid var(--border);
  border-radius:20px;
  padding:20px 16px;
  text-align:center;
  transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);
  background:#fff;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:12px;
  position:relative;
  overflow:hidden;
}
.pay-method-btn:hover { border-color:var(--blue); box-shadow:0 10px 25px rgba(0,0,0,.05); transform:translateY(-2px); }
.pay-method-btn.active-card   { border-color:#10b981; background:rgba(16,185,129,.02); box-shadow:0 8px 20px rgba(16,185,129,.1); }
.pay-method-btn.active-cpay   { border-color:#7c3aed; background:rgba(124,58,237,.02); box-shadow:0 8px 20px rgba(124,58,237,.1); }
.pay-method-btn.active-mpesa  { border-color:#e11d48; background:rgba(225,29,72,.02); box-shadow:0 8px 20px rgba(225,29,72,.1); }

.method-logo { width:56px; height:56px; border-radius:14px; object-fit:contain; }
.info-box   { border-radius:16px;padding:18px 20px;margin-bottom:20px;font-size:13.5px;line-height:1.7 }
.info-box ol { margin:0;padding-left:22px }
.info-box li { margin-bottom:6px }
.card-brand { display:flex;align-items:center;gap:8px;margin-top:12px }
.card-brand-logo { height:24px;border-radius:4px; }
</style>

<div style="max-width:580px;margin:0 auto">

  <div style="font-size:22px;font-weight:700;color:var(--navy);margin-bottom:4px">Application Fee</div>
  <div style="font-size:13px;color:var(--muted);margin-bottom:24px;line-height:1.5">Please pay a non-refundable application fee of M{{ number_format($fee, 2) }} to submit your application. Kindly note that payment of this fee does not guarantee loan approval, as all applications are subject to review and verification.</div>

  @if(session('error'))
  <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:11px;padding:13px 16px;margin-bottom:20px;font-size:13.5px;color:#991b1b;display:flex;gap:10px;align-items:flex-start">
    <i class="bi bi-exclamation-circle-fill" style="margin-top:2px;flex-shrink:0"></i>
    <div>{{ session('error') }}</div>
  </div>
  @endif

  @if(!$cpayConfigured)
  <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:11px;padding:13px 16px;display:flex;align-items:center;gap:10px;margin-bottom:20px;font-size:13px;color:#92400e">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div><strong>Demo Mode</strong> — Payment gateway not configured. Payments will be auto-approved for testing.</div>
  </div>
  @elseif($cpayIsSandbox)
  <div style="background:rgba(79,70,229,.06);border:1px solid rgba(79,70,229,.2);border-radius:11px;padding:12px 16px;margin-bottom:20px;font-size:12.5px;color:#4338ca">
    <i class="bi bi-shield-check"></i> <strong>Sandbox Mode</strong> — Connected to CPay UAT environment
  </div>
  @endif

  <div class="card" style="margin-bottom:24px;">
    <div style="padding:18px 22px 14px;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-family:monospace;font-size:11px;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.05em">Application #{{ $application->id }}</div>
          <div style="font-size:18px;font-weight:800;color:var(--navy);margin-top:2px">
            {{ $application->loanProduct->name ?? 'Loan Application' }}
          </div>
        </div>
        <span class="badge be" style="background:#fef2f2;color:#dc2626">Fee Pending</span>
      </div>
    </div>

    <div style="padding:20px 22px">
      <form method="POST" action="{{ route('borrower.apply.pay-fee.initiate', $application) }}" id="payForm">
        @csrf

        {{-- Payment Method --}}
        <div class="fg">
          <label class="fl">Select Payment Method</label>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px">

            {{-- M-Pesa --}}
            @if($mpesaConfigured)
            <label style="cursor:pointer;display:block">
              <input type="radio" name="method" value="mpesa" {{ $defaultMethod == 'mpesa' ? 'checked' : '' }} 
                style="display:none" class="method-radio"
                onchange="switchMethod('mpesa')">
              <div class="pay-method-btn {{ $defaultMethod == 'mpesa' ? 'active-mpesa' : '' }}" data-m="mpesa">
                <span class="badge" style="position:absolute;top:10px;right:-25px;background:#e11d48;color:#fff;font-size:9px;padding:3px 30px;transform:rotate(45deg);font-weight:800">COMING SOON</span>
                <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.19.20%20AM.jpeg" class="method-logo" alt="M-Pesa">
                <div>
                  <div style="font-size:13px;font-weight:800;color:var(--dark)">M-Pesa</div>
                </div>
              </div>
            </label>
            @endif

            {{-- Card --}}
            @if($cardEnabled)
            <label style="cursor:pointer;display:block">
              <input type="radio" name="method" value="card" {{ $defaultMethod == 'card' ? 'checked' : '' }}
                style="display:none" class="method-radio"
                onchange="switchMethod('card')">
              <div class="pay-method-btn {{ $defaultMethod == 'card' ? 'active-card' : '' }}" data-m="card">
                <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.22.04%20AM.jpeg" class="method-logo" alt="Card">
                <div>
                  <div style="font-size:13px;font-weight:800;color:var(--dark)">Credit/Debit</div>
                </div>
              </div>
            </label>
            @endif

            {{-- CPay Wallet --}}
            @if($cpayConfigured && $walletEnabled)
            <label style="cursor:pointer;display:block">
              <input type="radio" name="method" value="cpay_wallet"
                style="display:none" class="method-radio"
                onchange="switchMethod('cpay_wallet')">
              <div class="pay-method-btn" data-m="cpay_wallet">
                <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.17.16%20AM.jpeg" class="method-logo" alt="CPay">
                <div>
                  <div style="font-size:13px;font-weight:800;color:var(--dark)">CPay Wallet</div>
                </div>
              </div>
            </label>
            @endif
          </div>
        </div>

        {{-- Method Info Boxes --}}
        <div id="cardInfo" class="info-box" style="{{ $mpesaConfigured ? 'display:none;' : '' }}background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.2)">
          <div style="font-weight:700;color:var(--ok);font-size:13px;margin-bottom:10px">
            <i class="bi bi-credit-card-fill"></i> How Card Payment works
          </div>
          <ol>
            <li>Click <strong>Pay & Continue</strong></li>
            <li>You will be redirected to the secure payment portal</li>
            <li>Enter your Visa/Mastercard details securely</li>
            <li>Once confirmed, you'll return here to finalise your application ✓</li>
          </ol>
          <div class="card-brand">
            <img src="https://img.icons8.com/color/28/visa.png" alt="Visa" class="card-brand-logo">
            <img src="https://img.icons8.com/color/28/mastercard.png" alt="MC" class="card-brand-logo">
            <span style="font-size:11px;color:var(--muted)">Secured via SSL Encryption</span>
          </div>
        </div>

        <div id="cpayInfo" class="info-box" style="display:none;background:rgba(124,58,237,.04);border:1px solid rgba(124,58,237,.2)">
          <div style="font-weight:700;color:#7c3aed;font-size:13px;margin-bottom:10px">
            <i class="bi bi-wallet2"></i> How CPay Wallet works
          </div>
          <ol>
            <li>Enter your CPay-registered phone number</li>
            <li>Click <strong>Pay & Continue</strong></li>
            <li>You'll receive a CPay OTP or app notification</li>
            <li>Confirm transaction on your device ✓</li>
          </ol>
        </div>

        <div id="mpesaInfo" class="info-box" style="{{ $mpesaConfigured ? '' : 'display:none;' }}background:rgba(225,29,72,.04);border:1px solid rgba(225,29,72,.15)">
          <div style="font-weight:700;color:#e11d48;font-size:13px;margin-bottom:10px">
            <i class="bi bi-phone-fill"></i> How M-Pesa works
          </div>
          <ol>
            <li>Enter your M-Pesa phone number</li>
            <li>Click <strong>Pay & Continue</strong></li>
            <li>Watch your phone for an M-Pesa PIN prompt (STK Push)</li>
            <li>Enter your PIN to complete the fee payment ✓</li>
          </ol>
        </div>

        {{-- Email / Phone Inputs --}}
        <div id="emailField" class="fg" style="{{ $mpesaConfigured ? 'display:none;' : '' }}">
          <label class="fl">Email for Receipt</label>
          <input type="email" name="email" id="emailInput" class="fc" value="{{ auth('borrower')->user()->email }}" placeholder="Enter your email" style="font-size:14px">
        </div>

        <div id="phoneField" class="fg" style="{{ $mpesaConfigured ? '' : 'display:none;' }}">
          <label class="fl">Mobile Phone Number</label>
          <input type="tel" name="phone" id="phoneInput" class="fc" value="{{ auth('borrower')->user()->phone }}" placeholder="e.g. 2547XXXXXXXX" style="font-size:15px;font-weight:600">
        </div>

        {{-- Pay Summary --}}
        <div style="background:linear-gradient(135deg,var(--navy),#2b4bad);border-radius:14px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="color:rgba(255,255,255,.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em">Application Fee</div>
            <div style="color:#fff;font-size:28px;font-weight:900;margin-top:2px">
              M{{ number_format($fee,2) }}
            </div>
          </div>
          <div id="summaryMethodCard" style="color:rgba(255,255,255,.85);font-size:13px;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:4px;{{ !$mpesaConfigured ? '' : 'display:none' }}">
            <i class="bi bi-credit-card" style="font-size:24px"></i>
            <span>Card Payment</span>
          </div>
          <div id="summaryMethodMpesa" style="color:rgba(255,255,255,.85);font-size:13px;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:4px;{{ $mpesaConfigured ? '' : 'display:none' }}">
            <i class="bi bi-phone" style="font-size:24px"></i>
            <span>M-Pesa Selection</span>
          </div>
          <div id="summaryMethodCpay" style="color:rgba(255,255,255,.85);font-size:13px;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:4px;display:none">
            <i class="bi bi-wallet2" style="font-size:24px"></i>
            <span>CPay Wallet</span>
          </div>
        </div>

        <button type="submit" class="btn btn-p" style="width:100%;justify-content:center;font-size:16px;padding:14px;border-radius:12px">
          Pay & Continue <i class="bi bi-arrow-right" style="margin-left:8px"></i>
        </button>

        <div style="text-align:center;margin-top:14px;font-size:12px;color:var(--muted)">
          <i class="bi bi-shield-lock-fill" style="color:var(--ok)"></i>
          Payments are secure and encrypted.
        </div>
      </form>
    </div>
  </div>

  <div style="text-align:center">
     <a href="{{ route('borrower.apply.step.show', [$application, 9]) }}" style="font-size:13px;color:var(--muted);text-decoration:none">
        <i class="bi bi-chevron-left"></i> Back to Step 9
     </a>
  </div>

</div>

<script>
function switchMethod(method) {
  // Reset all
  document.querySelectorAll(`.pay-method-btn`).forEach(el => {
    el.classList.remove('active-card','active-cpay','active-mpesa');
  });

  // Activate selected
  const selected = document.querySelector(`.pay-method-btn[data-m="${method}"]`);
  if (selected) {
    if (method === 'card')       selected.classList.add('active-card');
    if (method === 'cpay_wallet') selected.classList.add('active-cpay');
    if (method === 'mpesa')       selected.classList.add('active-mpesa');
  }

  // Toggle info boxes
  document.getElementById(`cardInfo`).style.display  = method === 'card' ? '' : 'none';
  document.getElementById(`cpayInfo`).style.display  = method === 'cpay_wallet' ? '' : 'none';
  document.getElementById(`mpesaInfo`).style.display = method === 'mpesa' ? '' : 'none';

  // Toggle inputs
  document.getElementById(`emailField`).style.display = method === 'card' ? '' : 'none';
  document.getElementById(`phoneField`).style.display = (method === 'cpay_wallet' || method === 'mpesa') ? '' : 'none';

  // Summary selection
  document.getElementById(`summaryMethodCard`).style.display = method === 'card' ? '' : 'none';
  document.getElementById(`summaryMethodMpesa`).style.display = method === 'mpesa' ? '' : 'none';
  document.getElementById(`summaryMethodCpay`).style.display = method === 'cpay_wallet' ? '' : 'none';
}
</script>

@endsection
