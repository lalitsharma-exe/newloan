@extends('borrower.layouts.app')
@section('title','Make a Payment')
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

  <div style="font-size:22px;font-weight:700;color:var(--navy);margin-bottom:4px">Make a Payment</div>
  <div style="font-size:13.5px;color:var(--muted);margin-bottom:24px">Select your loan and preferred payment method</div>

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

  @forelse($loans as $loan)
  @php
    $nextInst   = $loan->installments->whereIn('status',['pending','overdue','partial'])->sortBy('due_date')->first();
    $nextAmount = $nextInst?->outstanding_amount ?? $loan->monthly_installment;
    $isOverdue  = $loan->status === 'overdue';
    $userEmail  = auth('borrower')->user()->email ?? '';
  @endphp

  <div class="card" style="margin-bottom:24px;border:{{ $isOverdue ? '2px solid rgba(239,68,68,.4)' : '1px solid var(--border)' }}">

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
        <span class="badge {{ $isOverdue ? 'be' : 'bok' }}">{{ $isOverdue ? '⚠️ Overdue' : 'Active' }}</span>
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
              style="padding-left:30px;font-size:16px;font-weight:700" required
              oninput="updateSummary('{{ $loan->id }}',this.value)">
          </div>
          <div style="display:flex;gap:8px;margin-top:6px">
            <button type="button" onclick="setAmount('{{ number_format($nextAmount,2,'.','') }}','{{ $loan->id }}')" class="btn btn-xs btn-o">
              Next Instalment M{{ number_format($nextAmount,0) }}
            </button>
            <button type="button" onclick="setAmount('{{ number_format($loan->outstanding_balance,2,'.','') }}','{{ $loan->id }}')" class="btn btn-xs btn-o">
              Full Balance M{{ number_format($loan->outstanding_balance,0) }}
            </button>
          </div>
        </div>

        {{-- Payment Method --}}
        <div class="fg">
          <label class="fl">Payment Method</label>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px">

            {{-- M-Pesa --}}
            @if($mpesaConfigured)
            <label style="cursor:pointer;display:block">
              <input type="radio" name="method" value="mpesa" checked
                style="display:none" class="method-radio"
                onchange="switchMethod('mpesa','{{ $loan->id }}')">
              <div class="pay-method-btn active-mpesa" data-m="mpesa" data-loan="{{ $loan->id }}">
                <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.19.20%20AM.jpeg" class="method-logo" alt="M-Pesa">
                <div>
                  <div style="font-size:14px;font-weight:800;color:var(--dark)">M-Pesa</div>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px">STK Push Payment</div>
                </div>
              </div>
            </label>
            @endif

            {{-- Card --}}
            <label style="cursor:pointer;display:block">
              <input type="radio" name="method" value="card" {{ !$mpesaConfigured ? 'checked' : '' }}
                style="display:none" class="method-radio"
                onchange="switchMethod('card','{{ $loan->id }}')">
              <div class="pay-method-btn" data-m="card" data-loan="{{ $loan->id }}">
                <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.22.04%20AM.jpeg" class="method-logo" alt="Card">
                <div>
                  <div style="font-size:14px;font-weight:800;color:var(--dark)">Credit/Debit</div>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px">Visa / Mastercard</div>
                </div>
              </div>
            </label>

            {{-- CPay Wallet --}}
            <label style="cursor:pointer;display:block">
              <input type="radio" name="method" value="cpay_wallet"
                style="display:none" class="method-radio"
                onchange="switchMethod('cpay_wallet','{{ $loan->id }}')">
              <div class="pay-method-btn" data-m="cpay_wallet" data-loan="{{ $loan->id }}">
                <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.17.16%20AM.jpeg" class="method-logo" alt="CPay">
                <div>
                  <div style="font-size:14px;font-weight:800;color:var(--dark)">CPay Wallet</div>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px">Instant Checkout</div>
                </div>
              </div>
            </label>
          </div>
        </div>

        {{-- Card instructions --}}
        <div id="cardInfo{{ $loan->id }}" class="info-box" style="{{ $mpesaConfigured ? 'display:none;' : '' }}background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.2)">
          <div style="font-weight:700;color:var(--ok);font-size:13px;margin-bottom:10px">
            <i class="bi bi-credit-card-fill"></i> How Card Payment works
          </div>
          <ol>
            <li>Enter your email address or leave the email provided</li>
            <li>Click <strong>Pay Now</strong> — you'll be redirected to <strong>PayFast</strong></li>
            <li>Enter your Visa/Mastercard details on the page</li>
            <li>After confirmation you'll be returned here ✓</li>
          </ol>
          <div class="card-brand">
            <img src="https://img.icons8.com/color/28/visa.png" alt="Visa" class="card-brand-logo">
            <img src="https://img.icons8.com/color/28/mastercard.png" alt="MC" class="card-brand-logo">
            <span style="font-size:11px;color:var(--muted)">Secured by PayFast · SSL Encrypted</span>
          </div>
        </div>

        {{-- CPay Wallet instructions --}}
        <div id="cpayInfo{{ $loan->id }}" class="info-box" style="display:none;background:rgba(124,58,237,.04);border:1px solid rgba(124,58,237,.2)">
          <div style="font-weight:700;color:#7c3aed;font-size:13px;margin-bottom:10px">
            <i class="bi bi-wallet2"></i> How CPay Wallet works
          </div>
          <ol>
            <li>Enter your CPay-registered phone number below</li>
            <li>Click <strong>Pay Now</strong></li>
            <li>You'll receive a <strong>CPay OTP</strong> or app notification</li>
            <li>Confirm in the app or enter your OTP — payment applied ✓</li>
          </ol>
        </div>

        {{-- M-Pesa instructions --}}
        <div id="mpesaInfo{{ $loan->id }}" class="info-box" style="{{ $mpesaConfigured ? '' : 'display:none;' }}background:rgba(225,29,72,.04);border:1px solid rgba(225,29,72,.15)">
          <div style="font-weight:700;color:#e11d48;font-size:13px;margin-bottom:10px">
            <i class="bi bi-phone-fill"></i> How M-Pesa works
          </div>
          <ol>
            <li>Enter your M-Pesa registered phone number below</li>
            <li>Click <strong>Pay Now</strong></li>
            <li>Watch your phone for an <strong>M-Pesa STK Push</strong></li>
            <li>Enter your M-Pesa PIN — payment applied ✓</li>
          </ol>
        </div>

        {{-- Email field (card) / Phone field (CPay wallet) --}}
        <div id="emailField{{ $loan->id }}" class="fg" style="{{ $mpesaConfigured ? 'display:none;' : '' }}">
          <label class="fl">Your Email Address</label>
          <input type="email" name="email" id="emailInput{{ $loan->id }}" class="fc"
            value="{{ $userEmail }}"
            placeholder="Enter your working email address"
            style="font-size:14px">
          <div style="font-size:11.5px;color:var(--muted);margin-top:4px">
            <i class="bi bi-info-circle"></i> Enter your working email address — required for payment confirmation details
          </div>
        </div>

        <div id="phoneField{{ $loan->id }}" class="fg" style="{{ $mpesaConfigured ? '' : 'display:none;' }}">
          <label class="fl">Phone Number (M-Pesa / CPay)</label>
          <input type="tel" name="phone" id="phoneInput{{ $loan->id }}" class="fc"
            value="{{ auth('borrower')->user()->phone }}"
            placeholder="e.g. 2547XXXXXXXX"
            style="font-size:15px;font-weight:600">
          <div style="font-size:11.5px;color:var(--muted);margin-top:4px">
            <i class="bi bi-info-circle"></i> Use the phone number registered for the selected mobile payment
          </div>
        </div>

        {{-- Summary --}}
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy2,#2b4bad));border-radius:14px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="color:rgba(255,255,255,.6);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em">You will pay</div>
            <div style="color:#fff;font-size:28px;font-weight:900;margin-top:4px" id="summaryAmount{{ $loan->id }}">
              M{{ number_format($nextAmount,2) }}
            </div>
          </div>
          {{-- Summary Icons --}}
          <div id="summaryMethodCard{{ $loan->id }}" style="color:rgba(255,255,255,.85);font-size:13px;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:4px;{{ !$mpesaConfigured ? '' : 'display:none' }}">
            <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.22.04%20AM.jpeg" style="width:34px;height:34px;border-radius:8px;object-fit:cover" alt="Card">
            <span>Card Payment</span>
          </div>
          <div id="summaryMethodMpesa{{ $loan->id }}" style="color:rgba(255,255,255,.85);font-size:13px;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:4px;{{ $mpesaConfigured ? '' : 'display:none' }}">
            <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.19.20%20AM.jpeg" style="width:34px;height:34px;border-radius:8px;object-fit:cover" alt="M-Pesa">
            <span>M-Pesa</span>
          </div>
          <div id="summaryMethodCpay{{ $loan->id }}" style="color:rgba(255,255,255,.85);font-size:13px;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:4px;display:none">
            <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-04-12%20at%2011.17.16%20AM.jpeg" style="width:34px;height:34px;border-radius:8px;object-fit:cover" alt="CPay">
            <span>CPay Wallet</span>
          </div>
        </div>

        <button type="submit" class="btn btn-p" style="width:100%;justify-content:center;font-size:15px;padding:13px">
          <i class="bi bi-lock-fill" style="margin-right:6px"></i>
          Pay Now — Secure
        </button>

        <div style="text-align:center;margin-top:10px;font-size:11.5px;color:var(--muted)">
          <i class="bi bi-shield-lock-fill" style="color:var(--ok)"></i>
          Payments are SSL-encrypted and processed securely
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
  // Reset all method buttons for this loan
  document.querySelectorAll(`.pay-method-btn[data-loan="${loanId}"]`).forEach(el => {
    el.classList.remove('active-card','active-cpay','active-mpesa');
    el.style.borderColor = 'var(--border)';
    el.style.background  = '';
    el.style.boxShadow   = '';
  });

  // Activate selected
  const selected = document.querySelector(`.pay-method-btn[data-m="${method}"][data-loan="${loanId}"]`);
  if (selected) {
    if (method === 'card')       selected.classList.add('active-card');
    if (method === 'cpay_wallet') selected.classList.add('active-cpay');
    if (method === 'mpesa')       selected.classList.add('active-mpesa');
  }

  // Toggle info boxes
  document.getElementById(`cardInfo${loanId}`).style.display  = method === 'card' ? '' : 'none';
  document.getElementById(`cpayInfo${loanId}`).style.display  = method === 'cpay_wallet' ? '' : 'none';
  document.getElementById(`mpesaInfo${loanId}`).style.display = method === 'mpesa' ? '' : 'none';

  // Toggle email vs phone fields
  document.getElementById(`emailField${loanId}`).style.display = method === 'card' ? '' : 'none';
  document.getElementById(`phoneField${loanId}`).style.display = (method === 'cpay_wallet' || method === 'mpesa') ? '' : 'none';

  // Make email/phone required appropriately
  document.getElementById(`emailInput${loanId}`).required = (method === 'card');
  document.getElementById(`phoneInput${loanId}`).required = (method === 'cpay_wallet' || method === 'mpesa');

  // Update summary
  document.getElementById(`summaryMethodCard${loanId}`).style.display = method === 'card' ? '' : 'none';
  document.getElementById(`summaryMethodMpesa${loanId}`).style.display = method === 'mpesa' ? '' : 'none';
  document.getElementById(`summaryMethodCpay${loanId}`).style.display = method === 'cpay_wallet' ? '' : 'none';
}

function setAmount(amount, loanId) {
  const form = document.getElementById(`payForm${loanId}`);
  form.querySelector('input[name="amount"]').value = amount;
  updateSummary(loanId, amount);
}

function updateSummary(loanId, val) {
  const v = parseFloat(val)||0;
  document.getElementById(`summaryAmount${loanId}`).textContent = 'M' + v.toLocaleString('en',{minimumFractionDigits:2,maximumFractionDigits:2});
}
</script>
@endsection
