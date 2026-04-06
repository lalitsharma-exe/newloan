<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Account — MyLoan Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Outfit',sans-serif;-webkit-font-smoothing:antialiased}
body{background:linear-gradient(160deg,#070e24 0%,#0d1b3e 40%,#162552 70%,#0d1b3e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 20px}

.auth-card {
  background: #fff; border-radius: 16px;
  box-shadow: 0 24px 80px rgba(7,14,36,.5);
  width: 100%; max-width: 520px; overflow: hidden;
}
.auth-top {
  background: linear-gradient(135deg, #0d1b3e, #1e3370);
  padding: 28px 36px 24px;
  display: flex; align-items: center; justify-content: space-between;
}
.auth-top img { height: 36px; object-fit: contain; display: block; }
.auth-top-text { text-align: right; }
.auth-top-text h1 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #fff; }
.auth-top-text p { font-size: 12px; color: rgba(255,255,255,.45); margin-top: 2px; }

.auth-body { padding: 28px 36px 32px; }

.fg { margin-bottom: 16px; }
.fl { display: block; font-size: 11px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; color: #5a6b85; margin-bottom: 5px; }
.field-wrap { position: relative; }
.field-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9aaccf; font-size: 15px; }
.fc {
  width: 100%; padding: 10px 12px 10px 38px;
  border: 1.5px solid #dde3ef; border-radius: 7px;
  font-size: 13.5px; font-family: 'Outfit', sans-serif;
  background: #fff; outline: none; color: #1c2433; transition: all .2s;
}
.fc:focus { border-color: #2b4bad; box-shadow: 0 0 0 3px rgba(43,75,173,.1); }
.fc.no-icon { padding-left: 12px; }
.iv { font-size: 12px; color: #dc2626; margin-top: 3px; display: block; }
.g2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* Phone prefix */
.phone-wrap { display: flex; }
.phone-prefix {
  background: #f0f3fa; border: 1.5px solid #dde3ef; border-right: none;
  border-radius: 7px 0 0 7px; padding: 10px 12px;
  font-size: 13.5px; font-weight: 600; color: #5a6b85; white-space: nowrap;
  display: flex; align-items: center;
}
.phone-input { border-radius: 0 7px 7px 0 !important; }

.btn-submit {
  width: 100%; padding: 13px; border-radius: 8px; border: none;
  background: linear-gradient(135deg, #0d1b3e, #1e3370);
  color: #fff; font-size: 15px; font-weight: 700;
  font-family: 'Outfit', sans-serif; cursor: pointer;
  transition: all .3s; display: flex; align-items: center;
  justify-content: center; gap: 8px; margin-top: 6px;
  box-shadow: 0 4px 16px rgba(13,27,62,.25);
}
.btn-submit:hover { background: linear-gradient(135deg, #162552, #2b4bad); transform: translateY(-1px); }

.auth-error { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #991b1b; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }
.auth-footer { text-align: center; margin-top: 18px; font-size: 13px; color: #5a6b85; }
.auth-footer a { color: #2b4bad; font-weight: 600; text-decoration: none; }
.auth-footer a:hover { text-decoration: underline; }
.notice { font-size: 12px; color: #9aaccf; line-height: 1.6; margin-top: 14px; }

@media(max-width:480px){ .g2{grid-template-columns:1fr} .auth-top{flex-direction:column;gap:12px;text-align:center} .auth-top-text{text-align:center} .auth-body{padding:24px 20px} }
</style>
</head>
<body>
<div class="auth-card">
  <div class="auth-top">
    <img src="https://ik.imagekit.io/ygydr1m84/png.webp" alt="MyLoan" style="filter:brightness(0) invert(1)">
    <div class="auth-top-text">
      <h1>Create Account</h1>
      <p>Join the MyLoan portal</p>
    </div>
  </div>

  <div class="auth-body">
    @if($errors->any())
    <div class="auth-error"><i class="bi bi-exclamation-triangle-fill" style="margin-right:7px"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('borrower.register.post') }}">
      @csrf
      <div class="g2">
        <div class="fg">
          <label class="fl">Full Name *</label>
          <div class="field-wrap">
            <i class="bi bi-person-fill field-icon"></i>
            <input type="text" name="name" class="fc" value="{{ old('name') }}" placeholder="Your full name" required>
          </div>
          @error('name')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Maiden Name <span style="color:#9aaccf;text-transform:none;letter-spacing:0">(if any)</span></label>
          <div class="field-wrap">
            <i class="bi bi-person-heart field-icon"></i>
            <input type="text" name="maiden_name" class="fc" value="{{ old('maiden_name') }}" placeholder="Name before marriage">
          </div>
          @error('maiden_name')<span class="iv">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="fg">
        <label class="fl">National ID *</label>
        <div class="field-wrap">
          <i class="bi bi-card-text field-icon"></i>
          <input type="text" name="national_id" class="fc" value="{{ old('national_id') }}" placeholder="ID number" required>
        </div>
        @error('national_id')<span class="iv">{{ $message }}</span>@enderror
      </div>

      <div class="fg">
        <label class="fl">Phone Number * <span style="color:#9aaccf;text-transform:none;letter-spacing:0">— used to log in</span></label>
        <div class="phone-wrap">
          <div class="phone-prefix"><i class="bi bi-phone" style="margin-right:5px"></i>+266</div>
          <input type="tel" name="phone" class="fc phone-input no-icon" value="{{ old('phone') }}" placeholder="53797734" required>
        </div>
        @error('phone')<span class="iv">{{ $message }}</span>@enderror
      </div>

      <div class="fg">
        <label class="fl">Email <span style="color:#9aaccf;text-transform:none;letter-spacing:0">(optional)</span></label>
        <div class="field-wrap">
          <i class="bi bi-envelope-fill field-icon"></i>
          <input type="email" name="email" class="fc" value="{{ old('email') }}" placeholder="Leave blank if none">
        </div>
      </div>

      <div class="g2">
        <div class="fg">
          <label class="fl">Password *</label>
          <div class="field-wrap">
            <i class="bi bi-lock-fill field-icon"></i>
            <input type="password" name="password" class="fc" placeholder="Min 8 chars" required>
          </div>
          @error('password')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Confirm Password *</label>
          <div class="field-wrap">
            <i class="bi bi-lock-fill field-icon"></i>
            <input type="password" name="password_confirmation" class="fc" placeholder="Repeat" required>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        <i class="bi bi-person-plus-fill"></i> Create My Account
      </button>

      <p class="notice">
        <i class="bi bi-info-circle" style="margin-right:4px"></i>
        By registering you agree to our <a href="{{ route('terms') ?? '#' }}" style="color:#2b4bad">Terms</a> and <a href="{{ route('privacy') ?? '#' }}" style="color:#2b4bad">Privacy Policy</a>.
      </p>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="{{ route('borrower.login') }}">Sign In</a>
    </div>
  </div>
</div>
</body>
</html>
