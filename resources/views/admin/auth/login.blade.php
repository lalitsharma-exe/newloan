<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign In — MyLoan Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;background:#0a1a0f}

/* ── LEFT PANEL ── */
.left{flex:1;background:linear-gradient(160deg,#0f2318 0%,#1a3d22 55%,#1e5428 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px;position:relative;overflow:hidden}
.left::before{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(76,175,105,.12) 0%,transparent 70%);top:-120px;right:-120px;border-radius:50%}
.left::after{content:'';position:absolute;width:350px;height:350px;background:radial-gradient(circle,rgba(76,175,105,.08) 0%,transparent 70%);bottom:-80px;left:-60px;border-radius:50%}

/* Logo on left */
.brand{text-align:center;position:relative;z-index:1;margin-bottom:56px}
.brand-logo{display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.brand p{color:rgba(255,255,255,.45);font-size:13px;margin-top:8px;letter-spacing:.5px}

/* Feature list */
.features{position:relative;z-index:1;width:100%;max-width:380px}
.fi{display:flex;align-items:center;gap:16px;margin-bottom:22px;padding:16px 18px;background:rgba(255,255,255,.04);border:1px solid rgba(76,175,105,.15);border-radius:14px;transition:background .2s}
.fi:hover{background:rgba(76,175,105,.07)}
.ficon{width:44px;height:44px;background:rgba(76,175,105,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#4caf69;flex-shrink:0}
.ftext h4{color:#fff;font-size:13.5px;font-weight:600}
.ftext p{color:rgba(255,255,255,.45);font-size:12px;margin-top:2px}

/* ── RIGHT PANEL ── */
.right{width:480px;background:#fff;display:flex;align-items:center;justify-content:center;padding:52px 50px}

/* Form */
.lf{width:100%}
.lf-logo{display:flex;align-items:center;justify-content:center;margin-bottom:28px}
.lf h2{font-size:26px;font-weight:800;color:#0f172a;text-align:center}
.lf .sub{color:#64748b;font-size:13.5px;margin-top:5px;margin-bottom:32px;text-align:center}
.fg{margin-bottom:18px}
.fl{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px;color:#374151}
.iw{position:relative}
.ii{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px}
.fi2{width:100%;padding:11px 13px 11px 40px;border:1.5px solid #e2e8f0;border-radius:11px;font-size:13.5px;font-family:'Inter',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;background:#f8fafc;color:#0f172a}
.fi2:focus{border-color:#1a5c2e;box-shadow:0 0 0 3px rgba(26,92,46,.1);background:#fff}
.fi2.err{border-color:#ef4444}
.rr{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.cl{display:flex;align-items:center;gap:7px;font-size:12.5px;color:#475569;cursor:pointer}
.cl input{accent-color:#1a5c2e}
.fl-a{font-size:12.5px;color:#1a5c2e;text-decoration:none;font-weight:600}
.fl-a:hover{text-decoration:underline}
.btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#1a5c2e,#2d8a47);color:#fff;border:none;border-radius:11px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .2s,transform .1s}
.btn-login:hover{opacity:.92;transform:translateY(-1px)}
.btn-login:active{transform:translateY(0)}
.alert{padding:12px 15px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.a-e{background:#fef2f2;color:#991b1b;border:1px solid #fee2e2}
.a-ok{background:#f0fdf4;color:#065f46;border:1px solid #d1fae5}
.iv{font-size:12px;color:#ef4444;margin-top:4px;display:block}
.demo{margin-top:22px;padding:13px 16px;background:#f0fdf4;border-radius:10px;font-size:12px;color:#374151;text-align:center;border:1px solid #bbf7d0}
.demo strong{color:#1a5c2e}

@media(max-width:900px){.left{display:none}.right{width:100%;padding:40px 28px}}
</style>
</head>
<body>

<!-- LEFT: branding -->
<div class="left">
  <div class="brand">
    <div class="brand-logo">
      <!-- MyLoan SVG Logo — large version for left panel -->
      <svg width="200" height="64" viewBox="0 0 200 64" xmlns="http://www.w3.org/2000/svg">
        <!-- dot spray cluster -->
        <circle cx="112" cy="7"  r="5.5" fill="#4caf69" opacity=".95"/>
        <circle cx="128" cy="5"  r="4.5" fill="#4caf69" opacity=".85"/>
        <circle cx="143" cy="8"  r="3.8" fill="#4caf69" opacity=".75"/>
        <circle cx="156" cy="14" r="3.2" fill="#4caf69" opacity=".65"/>
        <circle cx="166" cy="22" r="2.7" fill="#4caf69" opacity=".55"/>
        <circle cx="172" cy="32" r="2.2" fill="#4caf69" opacity=".42"/>
        <circle cx="120" cy="14" r="4.2" fill="#4caf69" opacity=".8"/>
        <circle cx="134" cy="17" r="3.5" fill="#4caf69" opacity=".7"/>
        <circle cx="147" cy="22" r="3.0" fill="#4caf69" opacity=".6"/>
        <circle cx="158" cy="29" r="2.5" fill="#4caf69" opacity=".5"/>
        <circle cx="128" cy="25" r="3.2" fill="#4caf69" opacity=".65"/>
        <circle cx="141" cy="31" r="2.7" fill="#4caf69" opacity=".52"/>
        <circle cx="153" cy="37" r="2.2" fill="#4caf69" opacity=".42"/>
        <!-- MY in dark green -->
        <text x="4"  y="52" font-family="Inter,Arial,sans-serif" font-weight="800" font-size="40" fill="#ffffff" letter-spacing="1">MY</text>
        <!-- LOAN in bright green -->
        <text x="66" y="52" font-family="Inter,Arial,sans-serif" font-weight="800" font-size="40" fill="#4caf69" letter-spacing="1">LOAN</text>
      </svg>
    </div>
    <p>SECURE ADMIN PORTAL</p>
  </div>

  <div class="features">
    <div class="fi">
      <div class="ficon"><i class="bi bi-speedometer2"></i></div>
      <div class="ftext"><h4>Real-Time Dashboard</h4><p>Monitor loans, payments and applications live</p></div>
    </div>
    <div class="fi">
      <div class="ficon"><i class="bi bi-calculator-fill"></i></div>
      <div class="ftext"><h4>Flat Interest Engine</h4><p>Automatic 15% flat rate + initiation + admin fee</p></div>
    </div>
    <div class="fi">
      <div class="ficon"><i class="bi bi-shield-check-fill"></i></div>
      <div class="ftext"><h4>Secure & Compliant</h4><p>Role-based access with full audit logging</p></div>
    </div>
    <div class="fi">
      <div class="ficon"><i class="bi bi-bar-chart-fill"></i></div>
      <div class="ftext"><h4>Advanced Reporting</h4><p>Portfolio, arrears, collections and income reports</p></div>
    </div>
  </div>
</div>

<!-- RIGHT: login form -->
<div class="right">
  <div class="lf">

    <!-- Logo on right panel too -->
    <div class="lf-logo">
      <svg width="140" height="44" viewBox="0 0 140 44" xmlns="http://www.w3.org/2000/svg">
        <circle cx="78"  cy="5"  r="3.8" fill="#4caf69" opacity=".95"/>
        <circle cx="89"  cy="3"  r="3.1" fill="#4caf69" opacity=".85"/>
        <circle cx="99"  cy="6"  r="2.6" fill="#4caf69" opacity=".75"/>
        <circle cx="108" cy="10" r="2.1" fill="#4caf69" opacity=".65"/>
        <circle cx="115" cy="16" r="1.8" fill="#4caf69" opacity=".55"/>
        <circle cx="119" cy="23" r="1.5" fill="#4caf69" opacity=".42"/>
        <circle cx="84"  cy="10" r="2.9" fill="#4caf69" opacity=".8"/>
        <circle cx="94"  cy="13" r="2.3" fill="#4caf69" opacity=".68"/>
        <circle cx="103" cy="17" r="2.0" fill="#4caf69" opacity=".58"/>
        <circle cx="110" cy="23" r="1.7" fill="#4caf69" opacity=".48"/>
        <circle cx="90"  cy="18" r="2.1" fill="#4caf69" opacity=".62"/>
        <circle cx="99"  cy="22" r="1.8" fill="#4caf69" opacity=".5"/>
        <text x="2"  y="36" font-family="Inter,Arial,sans-serif" font-weight="800" font-size="28" fill="#0f2318" letter-spacing="1">MY</text>
        <text x="44" y="36" font-family="Inter,Arial,sans-serif" font-weight="800" font-size="28" fill="#1a5c2e" letter-spacing="1">LOAN</text>
      </svg>
    </div>

    <h2>Welcome back</h2>
    <p class="sub">Sign in to your admin account</p>

    @if(session('success'))
    <div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}">
      @csrf
      <div class="fg">
        <label class="fl">Email Address</label>
        <div class="iw">
          <i class="bi bi-envelope ii"></i>
          <input type="email" name="email" placeholder="admin@myloan.co.ls"
            class="fi2 {{ $errors->has('email') ? 'err' : '' }}"
            value="{{ old('email') }}" autofocus>
        </div>
        @error('email')<span class="iv">{{ $message }}</span>@enderror
      </div>

      <div class="fg">
        <label class="fl">Password</label>
        <div class="iw">
          <i class="bi bi-lock ii"></i>
          <input type="password" name="password" placeholder="••••••••"
            class="fi2 {{ $errors->has('password') ? 'err' : '' }}"
            id="pwField">
          <button type="button" onclick="togglePw()"
            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;font-size:16px;padding:0" id="pwToggle">
            <i class="bi bi-eye" id="pwIcon"></i>
          </button>
        </div>
        @error('password')<span class="iv">{{ $message }}</span>@enderror
      </div>

      <div class="rr">
        <label class="cl">
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
          Remember me
        </label>
        <a href="{{ route('admin.password.request') }}" class="fl-a">Forgot password?</a>
      </div>

      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right"></i> Sign In to MyLoan
      </button>
    </form>

    <div class="demo">
      <strong>Demo credentials:</strong><br>
      admin@loanplatform.com &nbsp;/&nbsp; Admin@12345
    </div>

  </div>
</div>

<script>
function togglePw() {
  const f = document.getElementById('pwField');
  const i = document.getElementById('pwIcon');
  if (f.type === 'password') {
    f.type = 'text';
    i.className = 'bi bi-eye-slash';
  } else {
    f.type = 'password';
    i.className = 'bi bi-eye';
  }
}
</script>
</body>
</html>