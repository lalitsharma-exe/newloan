<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Officer Login — MyLoan</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f2318 0%,#1a5c2e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,.3);overflow:hidden;width:100%;max-width:420px}
.top{background:linear-gradient(135deg,#0f2318,#1a5c2e);padding:32px 36px 28px;text-align:center}
.logo{width:56px;height:56px;background:rgba(255,255,255,.15);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#fff}
.body{padding:32px 36px}
.fc{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
.fc:focus{border-color:#1a5c2e;box-shadow:0 0 0 3px rgba(26,92,46,.1)}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a5c2e,#2d8a47);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:all .2s}
.btn:hover{background:linear-gradient(135deg,#134821,#1a5c2e)}
.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="logo"><i class="bi bi-person-badge-fill"></i></div>
    <div style="color:#fff;font-size:22px;font-weight:800">MyLoan</div>
    <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:4px">Loan Officer Portal</div>
  </div>
  <div class="body">
    <div style="font-size:20px;font-weight:800;margin-bottom:4px">Sign In</div>
    <div style="font-size:13px;color:#64748b;margin-bottom:24px">Enter your credentials to continue</div>

    @if($errors->any())
    <div class="err"><i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first() }}</div>
    @endif
    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#065f46;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="err"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('officer.login.post') }}">
      @csrf
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px">Email Address</label>
        <input type="email" name="email" class="fc" value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
      </div>
      <div style="margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <label style="font-size:12.5px;font-weight:600">Password</label>
          <a href="{{ route('officer.password.request') }}" style="font-size:12px;color:#1a5c2e;text-decoration:none">Forgot password?</a>
        </div>
        <div style="position:relative">
          <input type="password" name="password" id="pw" class="fc" placeholder="••••••••" required style="padding-right:44px">
          <button type="button" onclick="const p=document.getElementById('pw');p.type=p.type==='password'?'text':'password'" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#64748b;font-size:16px"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:22px">
        <input type="checkbox" name="remember" id="rem" style="width:15px;height:15px;accent-color:#1a5c2e">
        <label for="rem" style="font-size:13px;color:#374151;cursor:pointer">Remember me</label>
      </div>
      <button type="submit" class="btn"><i class="bi bi-box-arrow-in-right" style="margin-right:6px"></i>Sign In</button>
    </form>

    <div style="text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9">
      <a href="{{ route('admin.login') }}" style="font-size:12px;color:#64748b;text-decoration:none"><i class="bi bi-shield-lock" style="margin-right:4px"></i>Admin Portal →</a>
    </div>
  </div>
</div>
</body>
</html>
