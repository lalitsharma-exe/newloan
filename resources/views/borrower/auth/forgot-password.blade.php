<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reset Password — MyLoan</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Cormorant+Garamond:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Outfit',sans-serif;background:linear-gradient(160deg,#070e24,#162552);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(7,14,36,.5);width:100%;max-width:400px;overflow:hidden}
.card-top{background:linear-gradient(135deg,#0d1b3e,#1e3370);padding:28px 32px;text-align:center}
.card-top img{height:36px;object-fit:contain;display:block;margin:0 auto 16px;filter:brightness(0) invert(1)}
.card-top h1{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:#fff}
.card-top p{font-size:13px;color:rgba(255,255,255,.5);margin-top:4px}
.card-body{padding:28px 32px}
.fl{display:block;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:#5a6b85;margin-bottom:5px}
.field-wrap{position:relative}
.field-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9aaccf;font-size:15px}
.fc{width:100%;padding:11px 12px 11px 38px;border:1.5px solid #dde3ef;border-radius:7px;font-size:14px;font-family:'Outfit',sans-serif;background:#fff;outline:none;color:#1c2433;transition:all .2s}
.fc:focus{border-color:#2b4bad;box-shadow:0 0 0 3px rgba(43,75,173,.1)}
.btn{width:100%;padding:13px;border-radius:8px;border:none;background:linear-gradient(135deg,#0d1b3e,#1e3370);color:#fff;font-size:15px;font-weight:700;font-family:'Outfit',sans-serif;cursor:pointer;margin-top:20px;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn:hover{background:linear-gradient(135deg,#162552,#2b4bad);transform:translateY(-1px)}
.success{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#065f46;padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
.back{text-align:center;margin-top:18px;font-size:13px;color:#5a6b85}
.back a{color:#2b4bad;font-weight:600;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <img src="https://ik.imagekit.io/ygydr1m84/png.webp" alt="MyLoan">
    <h1>Forgot Password?</h1>
    <p>Enter your phone number and we'll send a reset code</p>
  </div>
  <div class="card-body">
    @if(session('success'))<div class="success"><i class="bi bi-check-circle-fill" style="margin-right:6px"></i>{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('borrower.password.email') }}">@csrf
      <div style="margin-bottom:18px">
        <label class="fl">Registered Phone Number</label>
        <div class="field-wrap">
          <i class="bi bi-phone-fill field-icon"></i>
          <input type="text" name="phone" class="fc" placeholder="+266 5X XXX XXX" required>
        </div>
      </div>
      <button type="submit" class="btn"><i class="bi bi-send-fill"></i> Send Reset Code</button>
    </form>
    <div class="back"><a href="{{ route('borrower.login') }}">← Back to Sign In</a></div>
  </div>
</div>
</body>
</html>
