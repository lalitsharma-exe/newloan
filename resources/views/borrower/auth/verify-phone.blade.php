<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Verify Phone — MyLoan Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Outfit',sans-serif;-webkit-font-smoothing:antialiased}
body{background:linear-gradient(160deg,#070e24 0%,#0d1b3e 40%,#162552 70%,#0d1b3e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 20px}

.card{background:#fff;border-radius:18px;box-shadow:0 24px 80px rgba(7,14,36,.5);width:100%;max-width:420px;overflow:hidden}
.card-top{background:linear-gradient(135deg,#0d1b3e,#1e3370);padding:28px 32px 24px;text-align:center}
.card-top img{height:36px;object-fit:contain;filter:brightness(0) invert(1);margin-bottom:14px}
.card-top h1{font-size:20px;font-weight:700;color:#fff}
.card-top p{font-size:12.5px;color:rgba(255,255,255,.5);margin-top:4px}

.card-body{padding:28px 32px 32px}

/* Icon badge */
.icon-badge{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,rgba(43,75,173,.12),rgba(43,75,173,.06));border:2px solid rgba(43,75,173,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;color:#2b4bad}

.phone-display{background:#f8faff;border:1.5px solid #dde3ef;border-radius:9px;padding:10px 14px;text-align:center;margin-bottom:22px}
.phone-display strong{font-size:15px;color:#1c2433;font-weight:700}
.phone-display small{display:block;font-size:11.5px;color:#9aaccf;margin-top:2px}

/* OTP input — large digits */
.otp-wrap{display:flex;gap:10px;justify-content:center;margin-bottom:22px}
.otp-digit{
  width:46px;height:58px;border:2px solid #dde3ef;border-radius:10px;
  font-size:24px;font-weight:700;text-align:center;color:#0d1b3e;
  font-family:'Outfit',sans-serif;background:#fff;outline:none;
  transition:all .2s;
}
.otp-digit:focus{border-color:#2b4bad;box-shadow:0 0 0 3px rgba(43,75,173,.1);background:#f8faff}
.otp-digit.filled{border-color:#2b4bad;background:#f0f4ff}

.alert-err{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px}
.alert-ok{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#065f46;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}

.btn-verify{width:100%;padding:13px;border-radius:9px;border:none;background:linear-gradient(135deg,#0d1b3e,#1e3370);color:#fff;font-size:15px;font-weight:700;font-family:'Outfit',sans-serif;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 16px rgba(13,27,62,.25)}
.btn-verify:hover{background:linear-gradient(135deg,#162552,#2b4bad);transform:translateY(-1px)}
.btn-verify:disabled{opacity:.6;cursor:not-allowed;transform:none}

.resend-row{text-align:center;margin-top:16px;font-size:13px;color:#9aaccf}
.resend-row button{background:none;border:none;color:#2b4bad;font-weight:700;font-size:13px;font-family:'Outfit',sans-serif;cursor:pointer;padding:0}
.resend-row button:hover{text-decoration:underline}

.timer{color:#9aaccf;font-size:12px}
.help{font-size:12px;color:#9aaccf;text-align:center;line-height:1.6;margin-top:16px;border-top:1px solid #f0f3fa;padding-top:16px}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <img src="https://ik.imagekit.io/ygydr1m84/png.webp" alt="MyLoan">
    <h1>Verify Your Phone</h1>
    <p>One-time code sent via SMS</p>
  </div>

  <div class="card-body">
    <div class="icon-badge"><i class="bi bi-phone-fill"></i></div>

    <div class="phone-display">
      <strong>{{ $phone }}</strong>
      <small>A 6-digit code was sent to this number</small>
    </div>

    @if(session('error'))
    <div class="alert-err"><i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:1px"></i> {{ session('error') }}</div>
    @endif

    @if(session('success'))
    <div class="alert-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="alert-err"><i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:1px"></i> {{ $errors->first() }}</div>
    @endif

    {{-- OTP form --}}
    <form method="POST" action="{{ route('borrower.phone.verify') }}" id="otpForm">
      @csrf
      {{-- Hidden input receives the concatenated OTP --}}
      <input type="hidden" name="otp" id="otpHidden">

      {{-- 6 individual digit inputs for UX --}}
      <div class="otp-wrap">
        @for($i = 1; $i <= 6; $i++)
        <input type="tel" maxlength="1" class="otp-digit" id="d{{ $i }}"
               inputmode="numeric" pattern="[0-9]" autocomplete="off"
               aria-label="Digit {{ $i }}">
        @endfor
      </div>

      <button type="submit" class="btn-verify" id="verifyBtn" disabled>
        <i class="bi bi-shield-check-fill"></i> Verify Phone Number
      </button>
    </form>

    <div class="resend-row">
      Didn't receive it?
      <span id="timerTxt" class="timer">Resend in <strong id="countdown">60</strong>s</span>
      <form method="POST" action="{{ route('borrower.phone.resend') }}" id="resendForm" style="display:inline">
        @csrf
        <button type="submit" id="resendBtn" style="display:none">Resend Code</button>
      </form>
    </div>

    <div class="help">
      <i class="bi bi-info-circle" style="margin-right:4px"></i>
      Check your SMS inbox. Code expires in <strong>10 minutes</strong>.<br>
      Wrong number? <a href="{{ route('borrower.logout') }}" style="color:#2b4bad">Sign out</a> and register again.
    </div>
  </div>
</div>

<script>
// ── OTP digit navigation ────────────────────────────────────────────────────
const digits   = Array.from({length:6}, (_, i) => document.getElementById(`d${i+1}`));
const hidden   = document.getElementById('otpHidden');
const verifyBtn = document.getElementById('verifyBtn');

function updateHidden() {
  const val = digits.map(d => d.value).join('');
  hidden.value = val;
  // Enable button only when all 6 digits entered
  verifyBtn.disabled = val.length < 6 || digits.some(d => !/^[0-9]$/.test(d.value));
  digits.forEach(d => d.classList.toggle('filled', /^[0-9]$/.test(d.value)));
}

digits.forEach((el, idx) => {
  el.addEventListener('keydown', e => {
    if (e.key === 'Backspace') {
      if (!el.value && idx > 0) { digits[idx-1].focus(); digits[idx-1].value=''; updateHidden(); }
    }
  });
  el.addEventListener('input', e => {
    // Accept only numeric
    el.value = el.value.replace(/[^0-9]/g,'').slice(-1);
    updateHidden();
    if (el.value && idx < 5) digits[idx+1].focus();
  });
  el.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g,'');
    pasted.split('').slice(0,6).forEach((ch, i) => { if(digits[i]) digits[i].value = ch; });
    digits[Math.min(pasted.length, 5)].focus();
    updateHidden();
  });
});

// Auto-focus first digit on load
digits[0].focus();

// ── Countdown + resend ──────────────────────────────────────────────────────
let secs = 60;
const countdown  = document.getElementById('countdown');
const timerTxt   = document.getElementById('timerTxt');
const resendBtn  = document.getElementById('resendBtn');

const timer = setInterval(() => {
  secs--;
  countdown.textContent = secs;
  if (secs <= 0) {
    clearInterval(timer);
    timerTxt.style.display = 'none';
    resendBtn.style.display = 'inline';
  }
}, 1000);
</script>
</body>
</html>
