<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Agent Sign In — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Outfit',sans-serif;-webkit-font-smoothing:antialiased}
.auth-wrap { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }
.auth-brand {
  background: linear-gradient(160deg, #0d1b3e 0%, #162552 40%, #1e3370 70%, #2b4bad 100%);
  position: relative; overflow: hidden;
  display: flex; flex-direction: column;
  justify-content: space-between; padding: 48px;
}
.auth-brand::before {
  content: ''; position: absolute;
  width: 500px; height: 500px; border-radius: 50%;
  background: radial-gradient(circle, rgba(43,75,173,.25) 0%, transparent 70%);
  top: -100px; right: -150px;
}
.auth-brand::after {
  content: ''; position: absolute;
  width: 400px; height: 400px; border-radius: 50%;
  background: radial-gradient(circle, rgba(43,75,173,.15) 0%, transparent 70%);
  bottom: -80px; left: -80px;
}
.brand-logo { position: relative; z-index: 1; }
.brand-logo img { height: 48px; width: auto; object-fit: contain; display: block; }
.brand-body { position: relative; z-index: 1; }
.brand-body h2 {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(32px, 3.5vw, 48px); font-weight: 700;
  color: #fff; line-height: 1.15; letter-spacing: -.02em; margin-bottom: 16px;
}
.brand-body h2 em { font-style: italic; color: #7c9ff5; }
.brand-body p { font-size: 15px; color: rgba(255,255,255,.55); line-height: 1.7; max-width: 340px; margin-bottom: 36px; }
.brand-pills { display: flex; flex-direction: column; gap: 12px; }
.brand-pill {
  display: flex; align-items: center; gap: 12px;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
  border-radius: 8px; padding: 12px 16px;
}
.brand-pill-icon {
  width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
  background: rgba(43,75,173,.2); display: flex; align-items: center;
  justify-content: center; color: #7c9ff5; font-size: 15px;
}
.brand-pill-text strong { display: block; font-size: 13px; color: #fff; font-weight: 600; margin-bottom: 1px; }
.brand-pill-text span { font-size: 12px; color: rgba(255,255,255,.45); }
.brand-foot { position: relative; z-index: 1; font-size: 12px; color: rgba(255,255,255,.25); }
.auth-form-wrap {
  background: #f5f7ff;
  display: flex; align-items: center; justify-content: center; padding: 40px 32px;
}
.auth-form { width: 100%; max-width: 400px; }
.auth-form-logo { display: none; margin-bottom: 28px; }
.auth-head { margin-bottom: 30px; }
.auth-head h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 32px; font-weight: 700; color: #0f172a;
  letter-spacing: -.02em; margin-bottom: 6px;
}
.auth-head p { font-size: 14px; color: #5a6b85; }
.fg { margin-bottom: 18px; }
.fl { display: block; font-size: 11px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; color: #5a6b85; margin-bottom: 6px; }
.field-wrap { position: relative; }
.field-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #2b4bad; font-size: 16px; pointer-events: none; }
.fc {
  width: 100%; padding: 12px 13px 12px 40px;
  border: 1.5px solid #dde3ef; border-radius: 8px;
  font-size: 14px; font-family: 'Outfit', sans-serif;
  background: #fff; outline: none; color: #1c2433; transition: all .2s;
}
.fc:focus { border-color: #2b4bad; box-shadow: 0 0 0 3px rgba(43,75,173,.1); }
.forgot-link { display: block; text-align: right; font-size: 12px; color: #2b4bad; text-decoration: none; margin-top: -10px; margin-bottom: 14px; }
.forgot-link:hover { text-decoration: underline; }
.check-row { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; }
.check-row input { width: 16px; height: 16px; accent-color: #2b4bad; cursor: pointer; }
.check-row label { font-size: 13px; color: #5a6b85; cursor: pointer; }
.btn-submit {
  width: 100%; padding: 14px; border-radius: 8px; border: none;
  background: linear-gradient(135deg, #0d1b3e, #2b4bad);
  color: #fff; font-size: 15px; font-weight: 700;
  font-family: 'Outfit', sans-serif; cursor: pointer;
  transition: all .3s; display: flex; align-items: center;
  justify-content: center; gap: 8px;
  box-shadow: 0 4px 16px rgba(43,75,173,.25);
}
.btn-submit:hover { background: linear-gradient(135deg, #2b4bad, #3d60d4); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(43,75,173,.35); }
.auth-error {
  background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2);
  color: #991b1b; padding: 11px 14px; border-radius: 8px;
  font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
}
.auth-success {
  background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2);
  color: #065f46; padding: 11px 14px; border-radius: 8px;
  font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
}
.portal-switch { display: flex; gap: 8px; margin-top: 22px; }
.portal-link {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
  padding: 10px 12px; border-radius: 8px; text-decoration: none;
  font-size: 12px; font-weight: 600; transition: all .2s;
  border: 1.5px solid #dde3ef; color: #5a6b85; background: #fff;
}
.portal-link:hover { border-color: #2b4bad; color: #2b4bad; background: rgba(43,75,173,.04); }
.portal-link.active { border-color: #2b4bad; color: #2b4bad; background: rgba(43,75,173,.06); }
.register-link { display: block; text-align: center; margin-top: 18px; padding: 12px; background: rgba(43,75,173,.08); border: 1px solid rgba(43,75,173,.2); border-radius: 8px; text-decoration: none; color: #2b4bad; font-size: 13px; font-weight: 600; transition: all .2s; }
.register-link:hover { background: rgba(43,75,173,.15); }
@media(max-width: 768px) {
  .auth-wrap { grid-template-columns: 1fr; }
  .auth-brand { display: none; }
  .auth-form-logo { display: block; text-align: center; }
  .auth-form-wrap { background: linear-gradient(160deg, #0d1b3e, #162552); padding: 40px 24px; }
  .auth-head h1 { color: #fff; } .auth-head p { color: rgba(255,255,255,.55); }
  .fl { color: rgba(255,255,255,.55); }
  .fc { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.15); color: #fff; }
  .fc::placeholder { color: rgba(255,255,255,.3); }
  .fc:focus { border-color: rgba(255,255,255,.4); box-shadow: 0 0 0 3px rgba(255,255,255,.1); }
  .field-icon { color: rgba(255,255,255,.35); }
  .forgot-link { color: #7c9ff5; }
  .check-row label { color: rgba(255,255,255,.55); }
  .portal-link { border-color: rgba(255,255,255,.15); color: rgba(255,255,255,.55); background: rgba(255,255,255,.05); }
  .register-link { border-color: rgba(255,255,255,.15); color: #7c9ff5; background: rgba(255,255,255,.05); }
}
</style>
</head>
<body>

<div class="auth-wrap">
  <div class="auth-brand">
    <div class="brand-logo">
      <img src="{{ config('app.logo', 'https://ik.imagekit.io/ygydr1m84/png.webp') }}" alt="MyLoan">
    </div>
    <div class="brand-body">
      <h2>Community <em>Agent</em><br>Portal</h2>
      <p>Earn M50 per qualifying loan application. Serve your community as a MyLoan agent from your shop or anywhere.</p>
      <div class="brand-pills">
        <div class="brand-pill">
          <div class="brand-pill-icon"><i class="bi bi-phone-fill"></i></div>
          <div class="brand-pill-text"><strong>Mobile-First</strong><span>Works on any smartphone browser</span></div>
        </div>
        <div class="brand-pill">
          <div class="brand-pill-icon"><i class="bi bi-wallet-fill"></i></div>
          <div class="brand-pill-text"><strong>M50 Commission</strong><span>Earn on client's first repayment</span></div>
        </div>
        <div class="brand-pill">
          <div class="brand-pill-icon"><i class="bi bi-shield-check"></i></div>
          <div class="brand-pill-text"><strong>No Risk to You</strong><span>No fees, no collection, no liability</span></div>
        </div>
      </div>
    </div>
    <div class="brand-foot">© {{ date('Y') }} MyLoan Limited · Licensed by the Central Bank of Lesotho</div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-form">
      <div class="auth-form-logo">
        <img src="{{ config('app.logo', 'https://ik.imagekit.io/ygydr1m84/png.webp') }}" alt="MyLoan" style="filter:brightness(0) invert(1)">
      </div>
      <div class="auth-head">
        <h1>Agent Sign In</h1>
        <p>Sign in to the community agent portal.</p>
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

      <form method="POST" action="{{ route('agent.login.post') }}">
        @csrf
        <div class="fg">
          <label class="fl">Email Address</label>
          <div class="field-wrap">
            <i class="bi bi-envelope-fill field-icon"></i>
            <input type="email" name="email" class="fc" value="{{ old('email') }}" placeholder="agent@myloan.co.ls" required autofocus>
          </div>
        </div>
        <div class="fg">
          <label class="fl">Password</label>
          <div class="field-wrap">
            <i class="bi bi-lock-fill field-icon"></i>
            <input type="password" name="password" class="fc" placeholder="••••••••" required id="pwField">
            <button type="button" onclick="togglePw()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#86c7bd;font-size:15px" id="pwToggle">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <a href="{{ route('agent.password.request') }}" class="forgot-link">Forgot password?</a>
        <div class="check-row">
          <input type="checkbox" name="remember" id="rem">
          <label for="rem">Remember me</label>
        </div>
        <button type="submit" class="btn-submit">
          <i class="bi bi-shop"></i> Sign In as Agent
        </button>
      </form>

      <a href="{{ route('agent.register') }}" class="register-link">
        <i class="bi bi-person-plus-fill"></i> Not an agent yet? Apply to become one →
      </a>

      <div class="portal-switch">
        <a href="{{ route('borrower.login') }}" class="portal-link"><i class="bi bi-person-fill"></i> Borrower</a>
        <a href="{{ route('agent.login') }}" class="portal-link active"><i class="bi bi-shop"></i> Agent</a>
        <a href="{{ route('admin.login') }}" class="portal-link"><i class="bi bi-shield-lock-fill"></i> Admin</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePw() {
  const f = document.getElementById('pwField');
  const btn = document.getElementById('pwToggle').querySelector('i');
  f.type = f.type === 'password' ? 'text' : 'password';
  btn.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
