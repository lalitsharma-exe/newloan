<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Login — LoanPlatform</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;background:#0f172a}
.left{flex:1;background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4f46e5 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px;position:relative;overflow:hidden}
.left::before{content:'';position:absolute;width:400px;height:400px;background:rgba(255,255,255,.04);border-radius:50%;top:-100px;right:-100px}
.left::after{content:'';position:absolute;width:300px;height:300px;background:rgba(255,255,255,.04);border-radius:50%;bottom:-80px;left:-80px}
.brand{text-align:center;position:relative;z-index:1}
.brand-icon{width:72px;height:72px;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:32px;color:#fff;margin:0 auto 18px;border:1px solid rgba(255,255,255,.2)}
.brand h1{color:#fff;font-size:28px;font-weight:800}.brand p{color:rgba(255,255,255,.6);font-size:14px;margin-top:6px}
.features{margin-top:52px;position:relative;z-index:1}
.fi{display:flex;align-items:center;gap:14px;margin-bottom:20px}
.ficon{width:42px;height:42px;background:rgba(255,255,255,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#a5b4fc;flex-shrink:0}
.ftext h4{color:#fff;font-size:13.5px;font-weight:600}.ftext p{color:rgba(255,255,255,.5);font-size:12px;margin-top:1px}
.right{width:460px;background:#fff;display:flex;align-items:center;justify-content:center;padding:48px 50px}
.lf{width:100%}.lf h2{font-size:26px;font-weight:800;color:#0f172a}.lf .sub{color:#64748b;font-size:13.5px;margin-top:4px;margin-bottom:34px}
.fg{margin-bottom:18px}.fl{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
.iw{position:relative}.ii{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px}
.fi2{width:100%;padding:10px 13px 10px 40px;border:1.5px solid #e2e8f0;border-radius:11px;font-size:13.5px;font-family:'Inter',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;background:#f8fafc}
.fi2:focus{border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.1);background:#fff}.fi2.err{border-color:#ef4444}
.rr{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.cl{display:flex;align-items:center;gap:7px;font-size:12.5px;color:#475569;cursor:pointer}
.fl-a{font-size:12.5px;color:#4f46e5;text-decoration:none;font-weight:500}.fl-a:hover{text-decoration:underline}
.btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border:none;border-radius:11px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .2s}
.btn-login:hover{opacity:.9}
.alert{padding:12px 15px;border-radius:10px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
.a-e{background:#fef2f2;color:#991b1b;border:1px solid #fee2e2}.a-ok{background:#f0fdf4;color:#065f46;border:1px solid #d1fae5}
.iv{font-size:12px;color:#ef4444;margin-top:3px}
.demo{margin-top:20px;padding:12px 14px;background:#f8fafc;border-radius:9px;font-size:11.5px;color:#64748b;text-align:center;border:1px solid #e2e8f0}
@media(max-width:900px){.left{display:none}.right{width:100%}}
</style></head><body>
<div class="left">
  <div class="brand">
    <div class="brand-icon"><i class="bi bi-bank2"></i></div>
    <h1>LoanPlatform</h1><p>Secure Admin Portal</p>
  </div>
  <div class="features">
    <div class="fi"><div class="ficon"><i class="bi bi-speedometer2"></i></div><div class="ftext"><h4>Real-Time Dashboard</h4><p>Monitor loans, payments and applications</p></div></div>
    <div class="fi"><div class="ficon"><i class="bi bi-shield-check"></i></div><div class="ftext"><h4>Secure & Compliant</h4><p>Role-based access with full audit logging</p></div></div>
    <div class="fi"><div class="ficon"><i class="bi bi-graph-up-arrow"></i></div><div class="ftext"><h4>Advanced Reporting</h4><p>Export portfolio, arrears and collection reports</p></div></div>
  </div>
</div>
<div class="right">
  <div class="lf">
    <h2>Welcome back</h2><p class="sub">Sign in to your admin account</p>
    @if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
    <form method="POST" action="{{ route('admin.login.post') }}">
      @csrf
      <div class="fg"><label class="fl">Email Address</label><div class="iw"><i class="bi bi-envelope ii"></i><input type="email" name="email" placeholder="admin@example.com" class="fi2 {{ $errors->has('email')?'err':'' }}" value="{{ old('email') }}" autofocus></div>@error('email')<div class="iv">{{ $message }}</div>@enderror</div>
      <div class="fg"><label class="fl">Password</label><div class="iw"><i class="bi bi-lock ii"></i><input type="password" name="password" placeholder="••••••••" class="fi2 {{ $errors->has('password')?'err':'' }}"></div>@error('password')<div class="iv">{{ $message }}</div>@enderror</div>
      <div class="rr"><label class="cl"><input type="checkbox" name="remember" {{ old('remember')?'checked':'' }}> Remember me</label><a href="#" class="fl-a">Forgot password?</a></div>
      <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i> Sign In</button>
    </form>
    <div class="demo"><strong>Demo:</strong> admin@loanplatform.com / Admin@12345</div>
  </div>
</div>
</body></html>
