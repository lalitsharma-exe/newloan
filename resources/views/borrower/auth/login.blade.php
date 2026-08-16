<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign In — MyLoan Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'DM Sans',sans-serif;-webkit-font-smoothing:antialiased}

/* Full-screen split layout */
.auth-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

/* LEFT PANEL — brand */
.auth-brand {
  background: linear-gradient(160deg, #050f06 0%, #0f4527 40%, #1a6b3c 70%, #0f4527 100%);
  position: relative; overflow: hidden;
  display: flex; flex-direction: column;
  justify-content: space-between; padding: 48px;
}
/* Orbs */
.auth-brand::before {
  content: ''; position: absolute;
  width: 500px; height: 500px; border-radius: 50%;
  background: radial-gradient(circle, rgba(26,107,60,.4) 0%, transparent 70%);
  top: -100px; right: -150px;
}
.auth-brand::after {
  content: ''; position: absolute;
  width: 400px; height: 400px; border-radius: 50%;
  background: radial-gradient(circle, rgba(201,148,58,.15) 0%, transparent 70%);
  bottom: -80px; left: -80px;
}
.brand-logo { position: relative; z-index: 1; }
.brand-logo img { height: 48px; width: auto; object-fit: contain; display: block; }

.brand-body { position: relative; z-index: 1; }
.brand-body h2 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(30px, 3.2vw, 44px); font-weight: 700;
  color: #fff; line-height: 1.2; letter-spacing: -.02em;
  margin-bottom: 16px;
}
.brand-body h2 em { font-style: italic; color: #e0a843; }
.brand-body p { font-size: 15px; color: rgba(255,255,255,.55); line-height: 1.7; max-width: 340px; margin-bottom: 36px; }

.brand-pills { display: flex; flex-direction: column; gap: 12px; }
.brand-pill {
  display: flex; align-items: center; gap: 12px;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
  border-radius: 8px; padding: 12px 16px;
}
.brand-pill-icon {
  width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
  background: rgba(201,148,58,.2); display: flex; align-items: center;
  justify-content: center; color: #e0a843; font-size: 15px;
}
.brand-pill-text strong { display: block; font-size: 13px; color: #fff; font-weight: 600; margin-bottom: 1px; }
.brand-pill-text span { font-size: 12px; color: rgba(255,255,255,.45); }

.brand-foot { position: relative; z-index: 1; font-size: 12px; color: rgba(255,255,255,.25); }

/* RIGHT PANEL — form */
.auth-form-wrap {
  background: #f4f7f4;
  display: flex; align-items: center; justify-content: center;
  padding: 40px 32px;
}
.auth-form {
  width: 100%; max-width: 400px;
}
.auth-form-logo { display: none; margin-bottom: 28px; text-align: center; }
.auth-form-logo img { height: 40px; }

.auth-head { margin-bottom: 30px; }
.auth-head h1 {
  font-family: 'Playfair Display', serif;
  font-size: 30px; font-weight: 700; color: #0f4527;
  letter-spacing: -.02em; margin-bottom: 6px;
}
.auth-head p { font-size: 14px; color: #5a6e5a; }

/* Form fields */
.fg { margin-bottom: 18px; }
.fl { display: block; font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #5a6e5a; margin-bottom: 6px; }
.field-wrap { position: relative; }
.field-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #8aaa8a; font-size: 16px; pointer-events: none; }
.fc {
  width: 100%; padding: 12px 13px 12px 40px;
  border: 1.5px solid #d4e0d4; border-radius: 8px;
  font-size: 14px; font-family: 'DM Sans', sans-serif;
  background: #fff; outline: none; color: #0f2a1a;
  transition: all .2s;
}
.fc:focus { border-color: #1a6b3c; box-shadow: 0 0 0 3px rgba(26,107,60,.12); }
.fc.no-icon { padding-left: 13px; }

.forgot-link { display: block; text-align: right; font-size: 12px; color: #1a6b3c; text-decoration: none; margin-top: -10px; margin-bottom: 14px; font-weight: 600; }
.forgot-link:hover { text-decoration: underline; }

/* Checkbox */
.check-row { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; }
.check-row input { width: 16px; height: 16px; accent-color: #1a6b3c; cursor: pointer; }
.check-row label { font-size: 13px; color: #5a6e5a; cursor: pointer; }

/* Submit */
.btn-submit {
  width: 100%; padding: 14px; border-radius: 9px; border: none;
  background: linear-gradient(135deg, #0f4527, #1a6b3c);
  color: #fff; font-size: 15px; font-weight: 700;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: all .3s; display: flex; align-items: center;
  justify-content: center; gap: 8px;
  box-shadow: 0 4px 16px rgba(15,69,39,.3);
}
.btn-submit:hover { background: linear-gradient(135deg, #162552, #1a6b3c); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(15,69,39,.4); }

/* Divider */
.auth-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
.auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #d4e0d4; }
.auth-divider span { font-size: 12px; color: #8aaa8a; }

/* Register link */
.auth-switch { text-align: center; font-size: 13px; color: #5a6e5a; margin-top: 20px; }
.auth-switch a { color: #1a6b3c; font-weight: 600; text-decoration: none; }
.auth-switch a:hover { text-decoration: underline; }

/* Error / success */
.auth-error {
  background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2);
  color: #991b1b; padding: 11px 14px; border-radius: 8px;
  font-size: 13px; margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}
.auth-success {
  background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2);
  color: #065f46; padding: 11px 14px; border-radius: 8px;
  font-size: 13px; margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}

/* Mobile */
@media(max-width: 768px) {
  .auth-wrap { grid-template-columns: 1fr; }
  .auth-brand { display: none; }
  .auth-form-logo { display: block; text-align: center; }
  .auth-form-wrap { background: linear-gradient(160deg, #050f06, #1a6b3c); padding: 40px 24px; }
  .auth-form { max-width: 100%; }
  .auth-head h1 { color: #fff; }
  .auth-head p { color: rgba(255,255,255,.55); }
  .fl { color: rgba(255,255,255,.55); }
  .fc { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.15); color: #fff; }
  .fc::placeholder { color: rgba(255,255,255,.3); }
  .fc:focus { border-color: rgba(255,255,255,.4); box-shadow: 0 0 0 3px rgba(255,255,255,.1); }
  .field-icon { color: rgba(255,255,255,.35); }
  .forgot-link { color: #e0a843; }
  .check-row label { color: rgba(255,255,255,.55); }
  .auth-switch { color: rgba(255,255,255,.55); }
  .auth-switch a { color: #e0a843; }
  .auth-divider { display: none; }
  .auth-error { background: rgba(220,38,38,.2); }
}
</style>
</head>
<body>

<div class="auth-wrap">

  <!-- LEFT — Brand panel -->
  <div class="auth-brand">
    <div class="brand-logo" style="display:flex;align-items:center;gap:12px">
      <div style="width:44px;height:44px;background:linear-gradient(135deg,#1a6b3c,#22894e);border-radius:11px;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 12px rgba(0,0,0,.3);flex-shrink:0">
        <svg width="22" height="22" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg">
          <rect x="3" y="14" width="3" height="5" rx="1" fill="#fff" opacity=".85"/>
          <rect x="8" y="9" width="3" height="10" rx="1" fill="#fff"/>
          <rect x="13" y="5" width="3" height="14" rx="1" fill="#c9943a"/>
          <rect x="18" y="3" width="2" height="16" rx="1" fill="#e0a843" opacity=".9"/>
        </svg>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#fff">{{ config('app.name') }}</div>
        <div style="font-size:10px;color:rgba(255,255,255,.45);letter-spacing:.06em;text-transform:uppercase">Member Portal</div>
      </div>
    </div>

    <div class="brand-body">
      <h2>Your Money,<br>When You <em>Need</em> It.</h2>
      <p>Simple, transparent loans for government employees, private sector workers and pensioners across Lesotho.</p>
      <div class="brand-pills">
        <div class="brand-pill">
          <div class="brand-pill-icon"><i class="bi bi-lightning-charge-fill"></i></div>
          <div class="brand-pill-text"><strong>Fast Approval</strong><span>Apply online, decision within hours</span></div>
        </div>
        <div class="brand-pill">
          <div class="brand-pill-icon"><i class="bi bi-shield-check-fill"></i></div>
          <div class="brand-pill-text"><strong>Secure & Licensed</strong><span>Regulated by Central Bank of Lesotho</span></div>
        </div>
        <div class="brand-pill">
          <div class="brand-pill-icon"><i class="bi bi-eye-slash-fill"></i></div>
          <div class="brand-pill-text"><strong>No Hidden Fees</strong><span>All costs disclosed upfront</span></div>
        </div>
      </div>
    </div>

    <div class="brand-foot">
      © {{ date('Y') }} {{ config('app.name') }} · Licensed by the Central Bank of Lesotho
    </div>
  </div>

  <!-- RIGHT — Form -->
  <div class="auth-form-wrap">
    <div class="auth-form">

      <div class="auth-form-logo">
        <span style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#fff">{{ config('app.name') }}</span>
      </div>

      <div class="auth-head">
        <h1>Welcome Back</h1>
        <p>Sign in to your borrower account to manage your loans.</p>
      </div>

      @if($errors->any())
      <div class="auth-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first() }}</div>
      @endif
      @if(session('error'))
      <div class="auth-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
      @endif
      @if(session('success'))
      <div class="auth-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
      @endif

      <form method="POST" action="{{ route('borrower.login.post') }}">
        @csrf

        <div class="fg">
          <label class="fl">Phone Number or Email</label>
          <div class="field-wrap">
            <i class="bi bi-person-fill field-icon"></i>
            <input type="text" name="login" class="fc" value="{{ old('login') }}" placeholder="+26653797734 or email" required autofocus>
          </div>
        </div>

        <div class="fg">
          <label class="fl">Password</label>
          <div class="field-wrap">
            <i class="bi bi-lock-fill field-icon"></i>
            <input type="password" name="password" class="fc" placeholder="••••••••" required id="pwdField">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#8aaa8a;font-size:15px" id="pwdToggle">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <a href="{{ route('borrower.password.request') }}" class="forgot-link">Forgot password?</a>

        <div class="check-row">
          <input type="checkbox" name="remember" id="rem">
          <label for="rem">Keep me signed in for 30 days</label>
        </div>

        <button type="submit" class="btn-submit">
          <i class="bi bi-arrow-right-circle-fill"></i> Sign In to Portal
        </button>
      </form>

      <div class="auth-divider"><span>New to {{ config('app.name') }}?</span></div>

      <div class="auth-switch">
        Don't have an account? <a href="{{ route('borrower.register') }}">Create one — it's free</a>
      </div>

      <!-- Portal Switcher -->
      <div style="display:flex;gap:8px;margin-top:22px">
        <a href="{{ route('borrower.login') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;transition:all .2s;border:1.5px solid #1a6b3c;color:#1a6b3c;background:rgba(26,107,60,.06)">
          <i class="bi bi-person-fill"></i> Borrower
        </a>
        <a href="{{ route('agent.login') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;transition:all .2s;border:1.5px solid #d4e0d4;color:#5a6e5a;background:#fff">
          <i class="bi bi-people-fill"></i> Agent
        </a>
        <a href="{{ route('admin.login') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;transition:all .2s;border:1.5px solid #d4e0d4;color:#5a6e5a;background:#fff">
          <i class="bi bi-shield-lock-fill"></i> Admin
        </a>
      </div>

      <div style="text-align:center;margin-top:18px">
        <a href="{{ route('home') }}" style="font-size:12px;color:#8aaa8a;text-decoration:none">
          <i class="bi bi-house"></i> Back to Homepage
        </a>
      </div>

    </div>
  </div>

</div>

<script>
function togglePwd() {
  const f = document.getElementById('pwdField');
  const btn = document.getElementById('pwdToggle').querySelector('i');
  f.type = f.type === 'password' ? 'text' : 'password';
  btn.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
