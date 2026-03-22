@extends('borrower.layouts.app')
@section('title','Enter OTP — CPay Payment')
@section('content')

<div style="max-width:480px;margin:0 auto;padding:20px 0">

  {{-- Page title --}}
  <div style="font-size:22px;font-weight:700;color:var(--navy);margin-bottom:4px">
    <i class="bi bi-shield-lock" style="color:#4f46e5"></i> Confirm Payment
  </div>
  <div style="font-size:13.5px;color:var(--muted);margin-bottom:24px">
    Enter the OTP sent to your phone to complete the payment.
  </div>

  {{-- Errors --}}
  @if(session('error'))
  <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:11px;padding:13px 16px;display:flex;align-items:center;gap:10px;margin-bottom:20px;font-size:13.5px;color:#991b1b">
    <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
  </div>
  @endif

  {{-- Info box --}}
  <div style="background:rgba(79,70,229,.06);border:1px solid rgba(79,70,229,.2);border-radius:14px;padding:20px 22px;margin-bottom:24px">
    <div style="font-size:13px;color:var(--muted);margin-bottom:6px">Payment Reference</div>
    <div style="font-size:15px;font-weight:700;color:var(--navy);margin-bottom:14px;font-family:monospace">{{ $payment->payment_reference }}</div>

    <div style="display:flex;gap:24px;flex-wrap:wrap">
      <div>
        <div style="font-size:12px;color:var(--muted)">Amount</div>
        <div style="font-size:20px;font-weight:800;color:var(--navy)">M {{ number_format($payment->amount, 2) }}</div>
      </div>
      <div>
        <div style="font-size:12px;color:var(--muted)">OTP sent to</div>
        <div style="font-size:16px;font-weight:600;color:var(--navy)">{{ $phone }}</div>
      </div>
    </div>

    @if($message ?? false)
    <div style="margin-top:14px;font-size:13px;color:#4338ca;background:rgba(79,70,229,.08);border-radius:8px;padding:10px 14px">
      <i class="bi bi-info-circle"></i> {{ $message }}
    </div>
    @endif
  </div>

  {{-- OTP Form --}}
  <form method="POST" action="{{ route('borrower.payments.confirm-otp') }}" id="otpForm">
    @csrf
    <input type="hidden" name="payment_reference" value="{{ $payment->payment_reference }}">

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:13px;font-weight:600;color:var(--navy);margin-bottom:8px">
        Enter OTP <span style="color:#ef4444">*</span>
      </label>
      <input
        type="text"
        name="otp"
        id="otp"
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="8"
        autofocus
        required
        placeholder="e.g. 123456"
        style="width:100%;padding:14px 16px;font-size:22px;font-weight:700;letter-spacing:8px;text-align:center;border:2px solid #e2e8f0;border-radius:12px;outline:none;font-family:monospace;box-sizing:border-box;transition:border-color .2s"
        onfocus="this.style.borderColor='#4f46e5'"
        onblur="this.style.borderColor='#e2e8f0'"
        value="{{ old('otp') }}"
      >
      @error('otp')
        <div style="color:#ef4444;font-size:12px;margin-top:5px">{{ $message }}</div>
      @enderror
    </div>

    <button
      type="submit"
      id="submitBtn"
      style="width:100%;padding:14px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:opacity .2s"
    >
      <span id="btnText"><i class="bi bi-check-circle"></i> Confirm Payment</span>
      <span id="btnLoading" style="display:none"><i class="bi bi-hourglass-split"></i> Confirming…</span>
    </button>
  </form>

  {{-- Timer --}}
  <div style="text-align:center;margin-top:16px;font-size:13px;color:var(--muted)" id="timerBox">
    OTP expires in <strong id="countdown">05:00</strong>
  </div>

  {{-- Resend (goes back to initiation page) --}}
  <div style="text-align:center;margin-top:10px;font-size:13px">
    Didn't receive OTP?
    <a href="{{ route('borrower.payments.make') }}" style="color:#4f46e5;font-weight:600;text-decoration:none">
      Start Over
    </a>
  </div>

</div>

<script>
// Countdown timer 5 min
let secs = 300;
const cd = document.getElementById('countdown');
const timer = setInterval(() => {
  secs--;
  if (secs <= 0) {
    clearInterval(timer);
    cd.textContent = '00:00';
    document.getElementById('timerBox').innerHTML = '<span style="color:#ef4444;font-weight:600">OTP has expired. Please start over.</span>';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').style.opacity = '0.5';
    return;
  }
  const m = String(Math.floor(secs / 60)).padStart(2,'0');
  const s = String(secs % 60).padStart(2,'0');
  cd.textContent = `${m}:${s}`;
}, 1000);

// Loading state on submit
document.getElementById('otpForm').addEventListener('submit', function() {
  document.getElementById('btnText').style.display = 'none';
  document.getElementById('btnLoading').style.display = 'inline';
  document.getElementById('submitBtn').disabled = true;
});
</script>

@endsection
