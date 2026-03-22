@extends('borrower.layouts.app')
@section('title','Payment Pending')
@section('content')

<div style="max-width:520px;margin:0 auto">

  {{-- Waiting animation --}}
  <div style="text-align:center;padding:40px 20px 30px">
    <div id="statusIcon" style="width:80px;height:80px;border-radius:50%;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px">
      ⏳
    </div>
    <div style="font-size:22px;font-weight:800;color:var(--dark);margin-bottom:8px" id="statusTitle">Awaiting Confirmation</div>
    <div style="font-size:14px;color:var(--muted);line-height:1.6" id="statusMessage">
      Please check your phone.<br>
      Confirm the payment via the <strong>USSD prompt</strong> or <strong>OTP</strong> sent to your number.
    </div>
  </div>

  {{-- Payment details card --}}
  <div class="card" style="margin-bottom:20px">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-receipt" style="color:var(--p)"></i> Payment Details</span></div>
    <div class="card-body">
      @foreach([
        'Reference'  => $payment->payment_reference,
        'Amount'     => 'M '.number_format($payment->amount, 2),
        'Loan'       => $payment->loan->loan_number ?? '—',
        'Method'     => ucfirst(str_replace('_', ' ', $payment->method)),
        'CPay TXN'   => $payment->gateway_reference ?? '—',
      ] as $label => $value)
      <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:13.5px">
        <span style="color:var(--muted)">{{ $label }}</span>
        <strong style="{{ $label==='Amount'?'color:var(--p);font-size:16px':'' }}">{{ $value }}</strong>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Progress indicator --}}
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:20px">
    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px">Status</div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div style="width:28px;height:28px;border-radius:50%;background:rgba(16,185,129,.1);display:flex;align-items:center;justify-content:center;font-size:13px;color:#10b981;flex-shrink:0"><i class="bi bi-check-lg"></i></div>
      <div style="font-size:13px;font-weight:600">Payment initiated</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <div id="step2icon" style="width:28px;height:28px;border-radius:50%;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;font-size:13px;color:#f59e0b;flex-shrink:0">
        <div class="spinner"></div>
      </div>
      <div style="font-size:13px;font-weight:600;color:var(--muted)" id="step2label">Waiting for your confirmation...</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <div id="step3icon" style="width:28px;height:28px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--muted);flex-shrink:0"><i class="bi bi-check-lg"></i></div>
      <div style="font-size:13px;color:var(--muted)" id="step3label">Payment verified & applied to loan</div>
    </div>
  </div>

  {{-- Timer --}}
  <div style="text-align:center;margin-bottom:20px;font-size:13px;color:var(--muted)">
    Session expires in <strong id="countdown" style="color:var(--warn)">10:00</strong>
  </div>

  {{-- Actions --}}
  <div style="display:flex;gap:10px">
    <a href="{{ route('borrower.payments.cancel') }}" class="btn btn-o" style="flex:1;justify-content:center">
      <i class="bi bi-x-circle"></i> Cancel
    </a>
    <button onclick="checkNow()" class="btn btn-p" style="flex:1;justify-content:center" id="checkBtn">
      <i class="bi bi-arrow-clockwise"></i> Check Status
    </button>
  </div>

</div>

<style>
.spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(245,158,11,.3);
  border-top-color: #f59e0b;
  border-radius: 50%;
  animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
const payRef = '{{ $payment->payment_reference }}';
const statusUrl = '{{ route("borrower.payments.status") }}';
let pollInterval;
let timeLeft = 600; // 10 minutes

// Countdown timer
const countdown = document.getElementById('countdown');
const timer = setInterval(() => {
  timeLeft--;
  const m = Math.floor(timeLeft / 60).toString().padStart(2,'0');
  const s = (timeLeft % 60).toString().padStart(2,'0');
  countdown.textContent = `${m}:${s}`;
  if (timeLeft <= 0) {
    clearInterval(timer);
    clearInterval(pollInterval);
    document.getElementById('statusTitle').textContent = 'Payment Expired';
    document.getElementById('statusMessage').textContent = 'This payment session has expired. Please try again.';
    document.getElementById('statusIcon').textContent = '⏰';
  }
}, 1000);

// Poll CPay for status every 5 seconds
async function checkNow() {
  document.getElementById('checkBtn').disabled = true;
  document.getElementById('checkBtn').innerHTML = '<i class="bi bi-hourglass-split"></i> Checking...';

  try {
    const res = await fetch(`${statusUrl}?ref=${payRef}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    const data = await res.json();

    if (data.status === 'verified') {
      clearInterval(pollInterval);
      clearInterval(timer);
      // Animate success
      document.getElementById('statusIcon').textContent = '✅';
      document.getElementById('statusTitle').textContent = 'Payment Successful!';
      document.getElementById('statusMessage').textContent = 'Your payment has been confirmed and applied to your loan.';
      document.getElementById('step2icon').innerHTML = '<i class="bi bi-check-lg" style="color:#10b981"></i>';
      document.getElementById('step2icon').style.background = 'rgba(16,185,129,.1)';
      document.getElementById('step2label').textContent = 'Payment confirmed';
      document.getElementById('step2label').style.color = '#10b981';
      document.getElementById('step3icon').innerHTML = '<i class="bi bi-check-lg" style="color:#10b981"></i>';
      document.getElementById('step3icon').style.background = 'rgba(16,185,129,.1)';
      document.getElementById('step3label').style.color = '#10b981';
      // Redirect to success page
      setTimeout(() => { window.location.href = data.redirect_url; }, 2000);

    } else if (data.status === 'failed') {
      clearInterval(pollInterval);
      document.getElementById('statusIcon').textContent = '❌';
      document.getElementById('statusTitle').textContent = 'Payment Failed';
      document.getElementById('statusMessage').textContent = 'Your payment was declined or cancelled. Please try again.';
    } else {
      // Still pending
      document.getElementById('checkBtn').disabled = false;
      document.getElementById('checkBtn').innerHTML = '<i class="bi bi-arrow-clockwise"></i> Check Status';
    }
  } catch(e) {
    document.getElementById('checkBtn').disabled = false;
    document.getElementById('checkBtn').innerHTML = '<i class="bi bi-arrow-clockwise"></i> Check Status';
  }
}

// Auto-poll every 5 seconds
pollInterval = setInterval(checkNow, 5000);
</script>
@endsection
