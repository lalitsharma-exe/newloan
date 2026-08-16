<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Verify Email — Prosperity Loans</title>
<link href="https://fonts.googleapis.com/css2?family=DM Sans:wght@400;500;600;700&family=Cormorant+Garamond:wght@700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:linear-gradient(160deg,#050f06,#1a6b3c);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(7,14,36,.5);width:100%;max-width:420px;padding:48px 40px;text-align:center}
.icon{width:72px;height:72px;background:rgba(26,107,60,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:32px;color:#1a6b3c}
h1{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#0f4527;margin-bottom:10px}
p{font-size:14px;color:#5a6b85;line-height:1.7;margin-bottom:28px}
.btn{display:inline-block;padding:13px 32px;border-radius:8px;background:linear-gradient(135deg,#0f4527,#22894e);color:#fff;font-size:14px;font-weight:700;text-decoration:none;transition:all .3s}
.btn:hover{background:linear-gradient(135deg,#1a6b3c,#1a6b3c);transform:translateY(-1px)}
</style>
</head>
<body>
<div class="card">
  <div class="icon">✉️</div>
  <h1>Check Your Email</h1>
  <p>We've sent a verification link to your email address. Click the link to activate your account, then sign in.</p>
  <a href="{{ route('borrower.login') }}" class="btn">Back to Sign In</a>
</div>
</body>
</html>
