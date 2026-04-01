@extends('borrower.layouts.app')
@section('title','Make a Payment')
@section('content')

<div style="max-width:580px;margin:0 auto">

  <div style="font-size:22px;font-weight:700;color:var(--navy);margin-bottom:4px">Make a Payment</div>
  <div style="font-size:13.5px;color:var(--muted);margin-bottom:24px">Choose your loan and preferred payment method</div>

  @if(session('error'))
  <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:11px;padding:13px 16px;margin-bottom:20px;font-size:13.5px;color:#991b1b">
    <div style="display:flex;align-items:flex-start;gap:10px">
      <i class="bi bi-exclamation-circle-fill" style="margin-top:2px;flex-shrink:0"></i>
      <div>
        {{ session('error') }}
        @if(str_contains(session('error'), 'login') || str_contains(session('error'), 'does not exist'))
        <div style="margin-top:8px;font-size:12px;opacity:.85">
          <strong>Sandbox tip:</strong> Use test number <code style="background:rgba(0,0,0,.1);padding:1px 5px;border-radius:3px">22000001</code> (or contact CPay support to register your number in UAT).
        </div>
        @endif
      </div>
    </div>
  </div>
  @endif

  @if(!$cpayConfigured)
  <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:11px;padding:13px 16px;display:flex;align-items:center;gap:10px;margin-bottom:20px;font-size:13px;color:#92400e">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div><strong>Demo Mode</strong> — Payment gateway not configured. Payments will be auto-approved for testing.</div>
  </div>
  @else
  @if($cpayIsSandbox)
  <div style="background:rgba(79,70,229,.06);border:1px solid rgba(79,70,229,.2);border-radius:11px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#4338ca">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
      <i class="bi bi-shield-check"></i>
      <strong>Sandbox Mode Active</strong>
    </div>
    <div style="font-size:12px;opacity:0.8;line-height:1.5">
      CPay UAT is active. Use these <strong>test numbers</strong> for Mobile Money:<br>
      • <code style="background:rgba(0,0,0,0.1);padding:1px 4px;border-radius:3px">22000001</code> (Success)<br>
      • <code style="background:rgba(0,0,0,0.1);padding:1px 4px;border-radius:3px">22000002</code> (Insufficient Funds)<br>
      Note: Real phone numbers will return <em>"Wallet account does not exist"</em> in Sandbox.
    </div>
  </div>
  @endif
  @endif

  @forelse($loans as $loan)
  @php
    $nextInst = $loan->installments->whereIn('status',['pending','overdue','partial'])->sortBy('due_date')->first();
    $nextAmount = $nextInst?->outstanding_amount ?? $loan->monthly_installment;
    $isOverdue = $loan->status === 'overdue';
  @endphp

  <div class="card" style="margin-bottom:20px;border:{{ $isOverdue ? '2px solid rgba(239,68,68,.4)' : '1px solid var(--border)' }}">

    {{-- Loan header --}}
    <div style="padding:18px 22px 14px;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-family:monospace;font-size:12px;font-weight:700;color:var(--blue)">{{ $loan->loan_number }}</div>
          <div style="font-size:18px;font-weight:800;color:var(--navy);margin-top:2px">
            M{{ number_format($loan->outstanding_balance,2) }}
            <span style="font-size:12px;font-weight:400;color:var(--muted)">outstanding</span>
          </div>
          <div style="font-size:12.5px;color:var(--muted);margin-top:3px">
            {{ $loan->loanProduct->name ?? '—' }} &nbsp;·&nbsp;
            Next instalment: <strong style="color:{{ $isOverdue ? 'var(--err)' : 'var(--navy)' }}">M{{ number_format($nextAmount,2) }}</strong>
            @if($nextInst?->due_date) <span style="color:{{ $isOverdue ? 'var(--err)' : 'var(--muted)' }}">due {{ $nextInst->due_date->format('d M Y') }}</span> @endif
          </div>
        </div>
        <span class="badge {{ $isOverdue ? 'be' : 'bok' }}">
          {{ $isOverdue ? '⚠️ Overdue' : 'Active' }}
        </span>
      </div>
    </div>

    <div style="padding:20px 22px">

      <form method="POST" action="{{ route('borrower.payments.initiate') }}" id="payForm{{ $loan->id }}">
        @csrf
        <input type="hidden" name="loan_id" value="{{ $loan->id }}">

        {{-- Amount --}}
        <div class="fg">
          <label class="fl">Amount (M)</label>
          <div style="position:relative">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted);font-size:14px">M</span>
            <input type="number" name="amount" class="fc" step="0.01" min="1"
              max="{{ $loan->outstanding_balance }}"
              value="{{ number_format($nextAmount, 2, '.', '') }}"
              style="padding-left:30px;font-size:16px;font-weight:700"
              required>
          </div>
          <div style="display:flex;gap:8px;margin-top:6px">
            <button type="button" onclick="setAmount(this,'{{ number_format($nextAmount,2,'.','') }}','{{ $loan->id }}')" class="btn btn-xs btn-o">
              Pay Instalment (M{{ number_format($nextAmount,0) }})
            </button>
            <button type="button" onclick="setAmount(this,'{{ number_format($loan->outstanding_balance,2,'.','') }}','{{ $loan->id }}')" class="btn btn-xs btn-o">
              Clear Full Balance (M{{ number_format($loan->outstanding_balance,0) }})
            </button>
          </div>
        </div>

        {{-- Payment Method selector --}}
        <div class="fg">
          <label class="fl">Payment Method</label>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px" id="methods{{ $loan->id }}">

            {{-- Mobile Money --}}
            <label style="cursor:pointer">
              <input type="radio" name="method" value="mobile_money" checked
                style="display:none" class="method-radio"
                onchange="switchMethod('mobile_money','{{ $loan->id }}')">
              <div class="method-opt active-method" data-m="mobile_money" data-loan="{{ $loan->id }}"
                style="border:2px solid var(--blue);background:rgba(43,75,173,.06);border-radius:12px;padding:14px 10px;text-align:center;transition:all .2s">
                <div style="font-size:24px;margin-bottom:6px">📱</div>
                <div style="font-size:12px;font-weight:700;color:var(--navy)">Mobile Money</div>
                <div style="font-size:10.5px;color:var(--muted);margin-top:2px">M-Pesa / CPay</div>
              </div>
            </label>

            {{-- Card --}}
            <label style="cursor:pointer">
              <input type="radio" name="method" value="card"
                style="display:none" class="method-radio"
                onchange="switchMethod('card','{{ $loan->id }}')">
              <div class="method-opt" data-m="card" data-loan="{{ $loan->id }}"
                style="border:2px solid var(--border);border-radius:12px;padding:14px 10px;text-align:center;transition:all .2s">
                <div style="font-size:24px;margin-bottom:6px">💳</div>
                <div style="font-size:12px;font-weight:700;color:var(--navy)">Card</div>
                <div style="font-size:10.5px;color:var(--muted);margin-top:2px">Visa / Mastercard</div>
              </div>
            </label>

            {{-- CPay Wallet --}}
            <label style="cursor:pointer">
              <input type="radio" name="method" value="cpay_wallet"
                style="display:none" class="method-radio"
                onchange="switchMethod('cpay_wallet','{{ $loan->id }}')">
              <div class="method-opt" data-m="cpay_wallet" data-loan="{{ $loan->id }}"
                style="border:2px solid var(--border);border-radius:12px;padding:14px 10px;text-align:center;transition:all .2s">
                <div style="font-size:24px;margin-bottom:6px">👛</div>
                <div style="font-size:12px;font-weight:700;color:var(--navy)">CPay Wallet</div>
                <div style="font-size:10.5px;color:var(--muted);margin-top:2px">Chaperone wallet</div>
              </div>
            </label>
          </div>
        </div>

        {{-- Method-specific instructions --}}

        {{-- Mobile Money — explains OTP flow --}}
        <div id="mmInfo{{ $loan->id }}" style="background:rgba(43,75,173,.04);border:1px solid rgba(43,75,173,.15);border-radius:11px;padding:14px 16px;margin-bottom:16px">
          <div style="font-size:12.5px;font-weight:700;color:var(--blue);margin-bottom:8px">
            <i class="bi bi-shield-lock-fill"></i> How CPay Mobile Payment works
          </div>
          <div style="font-size:12.5px;color:var(--muted);line-height:1.7">
            1. Click <strong>Pay Now</strong><br>
            2. You'll receive an <strong>OTP via SMS</strong> on your phone<br>
            3. Enter the <strong>OTP</strong> on the next screen to confirm<br>
            4. Payment is processed and applied ✓
          </div>
        </div>

        {{-- Card — explains hosted page redirect --}}
        <div id="cardInfo{{ $loan->id }}" style="display:none;background:rgba(16,185,129,.04);border:1px solid rgba(16,185,129,.2);border-radius:11px;padding:14px 16px;margin-bottom:16px">
          <div style="font-size:12.5px;font-weight:700;color:var(--ok);margin-bottom:8px">
            <i class="bi bi-credit-card-fill"></i> How Card payment works
          </div>
          <div style="font-size:12.5px;color:var(--muted);line-height:1.7">
            1. Click <strong>Pay Now</strong><br>
            2. You'll be <strong>redirected to the CPay secure payment page</strong><br>
            3. Enter your <strong>Visa/Mastercard details</strong> on that page<br>
            4. After confirmation you'll be returned here ✓
          </div>
          <div style="margin-top:10px;display:flex;align-items:center;gap:8px">
            <img src="https://img.icons8.com/color/28/visa.png" alt="Visa" style="border-radius:4px">
            <img src="https://img.icons8.com/color/28/mastercard.png" alt="MC" style="border-radius:4px">
            <span style="font-size:11px;color:var(--muted)">Secured by CPay / 3D Secure</span>
          </div>
        </div>

        {{-- CPay Wallet --}}
        <div id="walletInfo{{ $loan->id }}" style="display:none;background:rgba(139,92,246,.04);border:1px solid rgba(139,92,246,.2);border-radius:11px;padding:14px 16px;margin-bottom:16px">
          <div style="font-size:12.5px;font-weight:700;color:#7c3aed;margin-bottom:8px">
            <i class="bi bi-wallet2"></i> How CPay Wallet works
          </div>
          <div style="font-size:12.5px;color:var(--muted);line-height:1.7">
            1. Click <strong>Pay Now</strong><br>
            2. You'll receive a <strong>notification in the CPay app</strong> or USSD prompt<br>
            3. Approve the payment in the app or enter your CPay PIN<br>
            4. Payment applied automatically ✓
          </div>
        </div>

        {{-- SINGLE shared phone input (card payments hide it entirely) --}}
        <div class="fg" id="phoneField{{ $loan->id }}">
          <label class="fl" id="phoneLabel{{ $loan->id }}">Mobile Phone Number (for OTP)</label>
          <input type="tel" name="phone" id="phoneInput{{ $loan->id }}" class="fc"
            value="{{ auth('borrower')->user()->phone }}"
            placeholder="e.g. 22000001 or +26622000001"
            style="font-size:15px;font-weight:600">
          <div style="font-size:11.5px;color:var(--muted);margin-top:4px" id="phoneHint{{ $loan->id }}">
            <i class="bi bi-info-circle"></i> OTP will be sent to this number via SMS
          </div>
        </div>

        {{-- Summary box --}}
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy2));border-radius:13px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="color:rgba(255,255,255,.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em">You will pay</div>
            <div style="color:#fff;font-size:28px;font-weight:900;margin-top:4px" id="summaryAmount{{ $loan->id }}">
              M{{ number_format($nextAmount,2) }}
            </div>
          </div>
          <div id="summaryMethod{{ $loan->id }}" style="color:rgba(255,255,255,.7);font-size:13px;text-align:right">
            <i class="bi bi-phone-fill" style="font-size:20px;display:block;margin-bottom:4px"></i>
            Mobile Money
          </div>
        </div>

        <button type="submit" class="btn btn-p btn-lg" style="width:100%;justify-content:center;font-size:15px">
          <i class="bi bi-lock-fill" style="margin-right:6px"></i>
          Pay Now — Secure
        </button>

        <div style="text-align:center;margin-top:10px;font-size:11.5px;color:var(--muted)">
          <i class="bi bi-shield-lock-fill" style="color:var(--ok)"></i>
          Secured by CPay (Chaperone Payments) &nbsp;·&nbsp; Payments are encrypted
        </div>
      </form>
    </div>
  </div>
  @empty
  <div class="card" style="text-align:center;padding:60px 30px">
    <i class="bi bi-check-circle-fill" style="font-size:48px;color:var(--ok);display:block;margin-bottom:14px"></i>
    <div style="font-size:16px;font-weight:700;color:var(--navy);margin-bottom:6px">No active loans</div>
    <div style="font-size:13.5px;color:var(--muted)">You have no loans requiring payment right now.</div>
  </div>
  @endforelse

</div>

<script>
function switchMethod(method, loanId) {
  // Deactivate all method option buttons
  document.querySelectorAll(`.method-opt[data-loan="${loanId}"]`).forEach(el => {
    el.style.border = '2px solid var(--border)';
    el.style.background = '';
  });

  // Activate selected
  const selected = document.querySelector(`.method-opt[data-m="${method}"][data-loan="${loanId}"]`);
  if (selected) {
    const colors = { mobile_money:'var(--blue)', card:'var(--ok)', cpay_wallet:'#7c3aed' };
    const bgs    = { mobile_money:'rgba(43,75,173,.06)', card:'rgba(16,185,129,.06)', cpay_wallet:'rgba(139,92,246,.06)' };
    selected.style.border = `2px solid ${colors[method]}`;
    selected.style.background = bgs[method];
  }

  // Show/hide info boxes
  document.getElementById(`mmInfo${loanId}`).style.display     = method === 'mobile_money' ? '' : 'none';
  document.getElementById(`cardInfo${loanId}`).style.display   = method === 'card' ? '' : 'none';
  document.getElementById(`walletInfo${loanId}`).style.display = method === 'cpay_wallet' ? '' : 'none';

  // Shared phone field — hide for card (no phone needed), show + relabel for others
  const phoneField = document.getElementById(`phoneField${loanId}`);
  const phoneLabel = document.getElementById(`phoneLabel${loanId}`);
  const phoneHint  = document.getElementById(`phoneHint${loanId}`);
  const phoneInput = document.getElementById(`phoneInput${loanId}`);

  if (method === 'card') {
    phoneField.style.display = 'none';
    phoneInput.disabled = true;   // don't submit for card
  } else {
    phoneField.style.display = '';
    phoneInput.disabled = false;
    if (method === 'cpay_wallet') {
      phoneLabel.textContent = 'CPay Wallet Phone Number';
      phoneHint.innerHTML = '<i class="bi bi-info-circle"></i> Your CPay wallet must be linked to this number';
    } else {
      phoneLabel.textContent = 'Mobile Phone Number (for OTP)';
      phoneHint.innerHTML = '<i class="bi bi-info-circle"></i> OTP will be sent to this number via SMS';
    }
  }

  // Update summary icon/label
  const icons  = { mobile_money:'bi-phone-fill', card:'bi-credit-card-fill', cpay_wallet:'bi-wallet2' };
  const labels = { mobile_money:'Mobile Money',  card:'Card Payment',        cpay_wallet:'CPay Wallet' };
  document.getElementById(`summaryMethod${loanId}`).innerHTML =
    `<i class="bi ${icons[method]}" style="font-size:20px;display:block;margin-bottom:4px"></i>${labels[method]}`;
}

function setAmount(btn, amount, loanId) {
  const form = document.getElementById(`payForm${loanId}`);
  form.querySelector('input[name="amount"]').value = amount;
  document.getElementById(`summaryAmount${loanId}`).textContent = 'M' + parseFloat(amount).toLocaleString('en',{minimumFractionDigits:2,maximumFractionDigits:2});
}

// Update summary when amount changes
document.querySelectorAll('input[name="amount"]').forEach(input => {
  input.addEventListener('input', function() {
    const loanId = this.closest('form').id.replace('payForm','');
    const v = parseFloat(this.value)||0;
    document.getElementById(`summaryAmount${loanId}`).textContent = 'M' + v.toLocaleString('en',{minimumFractionDigits:2,maximumFractionDigits:2});
  });
});
</script>
@endsection
