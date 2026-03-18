<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Forgot Password — MyLoan Officer</title>
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
.alert-ok{background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.alert-e{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.back{display:block;text-align:center;margin-top:18px;font-size:13px;color:#1a5c2e;text-decoration:none;font-weight:500}
.back:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="logo"><i class="bi bi-bank2"></i></div>
    <div style="color:#fff;font-size:20px;font-weight:800">MyLoan</div>
    <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:4px">Loan Officer Portal — Password Reset</div>
  </div>
  <div class="body">
    @if(session('success'))
    <div class="alert-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert-e"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}</div>
    @endif

    <p style="font-size:13px;color:#64748b;margin-bottom:22px;line-height:1.6">
      Enter your officer email address and we'll send you a password reset link.
    </p>

    <form method="POST" action="{{ route('officer.password.email') }}">
      @csrf
      <div class="fg">
        <label class="fl">Email Address</label>
        <input type="email" name="email" class="fc" value="{{ old('email') }}" placeholder="officer@myloan.co.ls" required autofocus>
      </div>
      <button type="submit" class="btn"><i class="bi bi-envelope"></i> Send Reset Link</button>
    </form>

    <a href="{{ route('officer.login') }}" class="back"><i class="bi bi-arrow-left"></i> Back to login</a>
  </div>
</div>
</body>
</html>
