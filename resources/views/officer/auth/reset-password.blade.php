<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reset Password — Prosperity Loans Officer</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f2318 0%,#1a5c2e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,.3);overflow:hidden;width:100%;max-width:420px}
.top{background:linear-gradient(135deg,#0f2318,#1a5c2e);padding:32px 36px 28px;text-align:center}
.logo{width:56px;height:56px;background:rgba(255,255,255,.15);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#fff}
.body{padding:32px 36px}
.fg{margin-bottom:18px}
.fl{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
.fc{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
.fc:focus{border-color:#1a5c2e;box-shadow:0 0 0 3px rgba(26,92,46,.1)}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a5c2e,#2d8a47);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:all .2s}
.btn:hover{background:linear-gradient(135deg,#134821,#1a5c2e)}
.alert-e{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.iv{font-size:12px;color:#ef4444;display:block;margin-top:4px}
.back{display:block;text-align:center;margin-top:18px;font-size:13px;color:#1a5c2e;text-decoration:none;font-weight:500}
.back:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="logo"><i class="bi bi-key-fill"></i></div>
    <div style="color:#fff;font-size:20px;font-weight:800">Prosperity Loans</div>
    <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:4px">Loan Officer Portal — Set New Password</div>
  </div>
  <div class="body">
    @if($errors->any())
    <div class="alert-e"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('officer.password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div class="fg">
        <label class="fl">Email Address</label>
        <input type="email" name="email" class="fc" value="{{ old('email', $email ?? '') }}" required autofocus>
        @error('email')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">New Password</label>
        <input type="password" name="password" class="fc" required autocomplete="new-password" placeholder="Min 8 characters">
        @error('password')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Confirm New Password</label>
        <input type="password" name="password_confirmation" class="fc" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn"><i class="bi bi-check-circle-fill"></i> Reset Password</button>
    </form>

    <a href="{{ route('officer.login') }}" class="back"><i class="bi bi-arrow-left"></i> Back to login</a>
  </div>
</div>
</body>
</html>
