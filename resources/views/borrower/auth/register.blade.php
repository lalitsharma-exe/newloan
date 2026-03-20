<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Register — MyLoan Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f2318,#1a5c2e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,.3);width:100%;max-width:480px;overflow:hidden}
.top{background:linear-gradient(135deg,#0f2318,#1a5c2e);padding:26px 36px;text-align:center}
.body{padding:28px 36px}
.fc{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
.fc:focus{border-color:#1a5c2e;box-shadow:0 0 0 3px rgba(26,92,46,.1)}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a5c2e,#2d8a47);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif}
.fg{margin-bottom:14px}
.fl{display:block;font-size:12.5px;font-weight:600;margin-bottom:4px}
.iv{font-size:12px;color:#ef4444;margin-top:2px;display:block}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div style="font-size:22px;font-weight:900;color:#fff">Create Account</div>
    <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:3px">MyLoan Borrower Portal</div>
  </div>
  <div class="body">
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:16px">
      <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    <form method="POST" action="{{ route('borrower.register.post') }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="fg">
          <label class="fl">Full Name *</label>
          <input type="text" name="name" class="fc" value="{{ old('name') }}" required>
          @error('name')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">National ID *</label>
          <input type="text" name="national_id" class="fc" value="{{ old('national_id') }}" placeholder="9001015009087" required>
          @error('national_id')<span class="iv">{{ $message }}</span>@enderror
        </div>
      </div>
      <div class="fg">
        <label class="fl">Phone Number * <span style="font-size:11px;color:#94a3b8">(+266XXXXXXXX)</span></label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;font-weight:600">+266</span>
          <input type="tel" name="phone" class="fc" value="{{ old('phone') }}" placeholder="53797734" style="padding-left:50px" required>
        </div>
        @error('phone')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Email <span style="font-size:11px;color:#94a3b8">(optional)</span></label>
        <input type="email" name="email" class="fc" value="{{ old('email') }}" placeholder="Leave blank if you don't have one">
        @error('email')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="fg">
          <label class="fl">Password *</label>
          <input type="password" name="password" class="fc" placeholder="Min 8 characters" required>
          @error('password')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Confirm Password *</label>
          <input type="password" name="password_confirmation" class="fc" required>
        </div>
      </div>
      <div style="font-size:12px;color:#64748b;line-height:1.6;margin-bottom:16px">
        By registering you agree to our <a href="#" style="color:#1a5c2e">Terms of Service</a> and <a href="#" style="color:#1a5c2e">Privacy Policy</a>.
      </div>
      <button type="submit" class="btn">Create Account</button>
    </form>
    <div style="text-align:center;margin-top:18px;font-size:13px;color:#64748b">
      Already have an account? <a href="{{ route('borrower.login') }}" style="color:#1a5c2e;font-weight:700">Sign In</a>
    </div>
  </div>
</div>
</body>
</html>
