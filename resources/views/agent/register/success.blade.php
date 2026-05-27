<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Application Submitted — MyLoan Agent</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Inter',sans-serif;background:linear-gradient(160deg,#042f2e,#115e59);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.success-card{background:#fff;border-radius:20px;max-width:480px;width:100%;padding:40px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.check-circle{width:72px;height:72px;background:rgba(16,185,129,.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:32px;color:#10b981}
h1{font-size:22px;font-weight:800;margin-bottom:8px;color:#0f172a}
p{font-size:14px;color:#64748b;line-height:1.6;margin-bottom:20px}
.ref-badge{display:inline-block;background:#f0fdf4;border:2px solid #10b981;border-radius:12px;padding:12px 24px;font-size:20px;font-weight:800;color:#0f766e;letter-spacing:.05em;margin-bottom:20px}
.info-box{background:#f8fafc;border-radius:10px;padding:14px;font-size:13px;color:#475569;text-align:left;margin-bottom:20px;line-height:1.6}
a.btn{display:inline-flex;align-items:center;gap:6px;padding:12px 24px;border-radius:10px;text-decoration:none;font-size:14px;font-weight:600;background:#0f766e;color:#fff;transition:all .2s}
a.btn:hover{background:#0d5b55}
</style>
</head>
<body>
<div class="success-card">
  <div class="check-circle"><i class="bi bi-check-lg"></i></div>
  <h1>Application Submitted!</h1>
  <p>Your agent application has been received. We will review it within 1–2 business days.</p>
  <div class="ref-badge">{{ $ref }}</div>
  <div class="info-box">
    <strong>What happens next?</strong><br>
    • We'll review your documents and details.<br>
    • You'll receive an SMS notification with the result.<br>
    • If approved, you'll get an activation link to sign your agent agreement and start using the portal.
  </div>
  <a href="{{ route('agent.login') }}" class="btn"><i class="bi bi-box-arrow-in-right"></i> Go to Agent Login</a>
</div>
</body>
</html>
