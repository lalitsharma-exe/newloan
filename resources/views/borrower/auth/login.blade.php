<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — MyLoan Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f2318,#1a5c2e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,.3);width:100%;max-width:420px;overflow:hidden}
.top{background:linear-gradient(135deg,#0f2318,#1a5c2e);padding:32px 36px;text-align:center}
.body{padding:32px 36px}
.fc{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
.fc:focus{border-color:#1a5c2e;box-shadow:0 0 0 3px rgba(26,92,46,.1)}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a5c2e,#2d8a47);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif}
.btn:hover{background:linear-gradient(135deg,#134821,#1a5c2e)}
.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div style="font-size:28px;font-weight:900;color:#fff">MyLoan</div>
    <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:4px">Borrower Portal</div>
  </div>
  <div class="body">
    <div style="font-size:20px;font-weight:800;margin-bottom:20px">Sign In</div>
    @if($errors->any())<div class="err">⚠ {{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="err">⚠ {{ session('error') }}</div>@endif
    @if(session('success'))<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#065f46;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:18px">✓ {{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('borrower.login.post') }}">
      @csrf
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px">Phone Number or Email</label>
        <input type="text" name="login" class="fc" value="{{ old('login') }}" placeholder="+26653797734 or email" required autofocus>
      </div>
      <div style="margin-bottom:22px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <label style="font-size:12.5px;font-weight:600">Password</label>
          <a href="{{ route('borrower.password.request') }}" style="font-size:12px;color:#1a5c2e;text-decoration:none">Forgot password?</a>
        </div>
        <input type="password" name="password" class="fc" placeholder="••••••••" required>
      </div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:22px">
        <input type="checkbox" name="remember" id="rem" style="width:15px;height:15px;accent-color:#1a5c2e">
        <label for="rem" style="font-size:13px;color:#374151;cursor:pointer">Keep me signed in</label>
      </div>
      <button type="submit" class="btn">Sign In</button>
    </form>
    <div style="text-align:center;margin-top:20px;font-size:13px;color:#64748b">
      Don't have an account? <a href="{{ route('borrower.register') }}" style="color:#1a5c2e;font-weight:700">Register</a>
    </div>
    <div style="text-align:center;margin-top:10px">
      <a href="{{ route('home') }}" style="font-size:12px;color:#94a3b8;text-decoration:none">← Back to Home</a>
    </div>
  </div>
</div>
</body>
</html>
