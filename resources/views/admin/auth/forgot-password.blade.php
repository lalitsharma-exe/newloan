<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password — MyLoan Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:linear-gradient(135deg,#1e3a5f 0%,#4f46e5 100%);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',Arial,sans-serif}
.card{background:#fff;border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,.18)}
.logo{text-align:center;margin-bottom:32px}
.logo h1{font-size:26px;font-weight:800;color:#1e3a5f}
.logo p{font-size:13px;color:#64748b;margin-top:4px}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
.form-control{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;transition:border .2s;outline:none}
.form-control:focus{border-color:#4f46e5}
.btn-primary{width:100%;padding:13px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer}
.alert{padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:18px}
.alert-ok{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
.back-link{display:block;text-align:center;margin-top:18px;font-size:13px;color:#4f46e5;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <h1><i class="bi bi-bank2" style="color:#4f46e5"></i> MyLoan</h1>
    <p>Admin Portal — Password Reset</p>
  </div>

  @if(session('success'))
  <div class="alert alert-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
  @endif

  @if($errors->any())
  <div class="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca">
    {{ $errors->first() }}
  </div>
  @endif

  <p style="font-size:13px;color:#64748b;margin-bottom:22px">Enter your admin email address and we'll send you a password reset link.</p>

  <form method="POST" action="{{ route('admin.password.email') }}">
    @csrf
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
    </div>
    <button type="submit" class="btn-primary"><i class="bi bi-envelope"></i> Send Reset Link</button>
  </form>

  <a href="{{ route('admin.login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to login</a>
</div>
</body>
</html>
