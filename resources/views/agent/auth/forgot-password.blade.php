<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Forgot Password — Agent Portal</title>
<link href="https://fonts.googleapis.com/css2?family=DM Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(160deg,#042f2e,#134e4a)}
.box{background:#fff;border-radius:18px;max-width:420px;width:100%;margin:20px;padding:36px;box-shadow:0 20px 50px rgba(0,0,0,.2)}
h1{font-size:22px;font-weight:700;margin-bottom:6px}p{font-size:13px;color:#64748b;margin-bottom:22px}
.fg{margin-bottom:18px}.fl{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#5a6b85;margin-bottom:6px}
.fc{width:100%;padding:11px 13px;border:1.5px solid #d1e7dd;border-radius:8px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none}
.fc:focus{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.1)}
.btn-p{width:100%;padding:13px;border-radius:8px;border:none;background:#0f766e;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif}
.btn-p:hover{background:#0d5b55}
.auth-success{background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);color:#065f46;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
a{color:#0f766e;font-size:13px;text-decoration:none}a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="box">
  <h1>Forgot Password</h1>
  <p>Enter your email and we'll send a reset link.</p>
  @if(session('success'))<div class="auth-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
  <form method="POST" action="{{ route('agent.password.email') }}">
    @csrf
    <div class="fg"><label class="fl">Email</label><input type="email" name="email" class="fc" required autofocus></div>
    <button type="submit" class="btn-p">Send Reset Link</button>
  </form>
  <div style="margin-top:16px;text-align:center"><a href="{{ route('agent.login') }}"><i class="bi bi-arrow-left"></i> Back to login</a></div>
</div>
</body>
</html>
