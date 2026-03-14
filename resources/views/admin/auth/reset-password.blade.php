<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password — MyLoan Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:linear-gradient(135deg,#1e3a5f 0%,#4f46e5 100%);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',Arial,sans-serif}
.card{background:#fff;border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,.18)}
.logo{text-align:center;margin-bottom:32px}
.logo h1{font-size:26px;font-weight:800;color:#1e3a5f}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
.form-control{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;transition:border .2s}
.form-control:focus{border-color:#4f46e5}
.btn-primary{width:100%;padding:13px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer}
.err{color:#ef4444;font-size:12px;margin-top:4px}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <h1><i class="bi bi-shield-lock" style="color:#4f46e5"></i> MyLoan</h1>
    <p style="font-size:13px;color:#64748b;margin-top:4px">Set a new password</p>
  </div>

  @if($errors->any())
  <div style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:18px">
    {{ $errors->first() }}
  </div>
  @endif

  <form method="POST" action="{{ route('admin.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" value="{{ $email ?? old('email') }}" required>
    </div>
    <div class="form-group">
      <label class="form-label">New Password</label>
      <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required>
    </div>
    <div class="form-group">
      <label class="form-label">Confirm New Password</label>
      <input type="password" name="password_confirmation" class="form-control" required>
    </div>
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> Reset Password</button>
  </form>
</div>
</body>
</html>
