<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name') }}</title>
<link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ═══════════════════════════════════════════════════
   PROSPERITY LOANS — BORROWER PORTAL THEME
   Brand: Forest Green · Royal Blue · Gold
═══════════════════════════════════════════════════ */
:root {
  --green:   #1a6b3c;
  --greend:  #0f4527;
  --greenl:  #22894e;
  --green2:  #2eaa62;
  --blue:    #1a3a8f;
  --bluel:   #2b55c9;
  --gold:    #c9943a;
  --goldl:   #e0a843;
  --ok:      #16a34a;
  --warn:    #d97706;
  --err:     #dc2626;
  --bg:      #f4f7f4;
  --card:    #ffffff;
  --border:  #d4e0d4;
  --ink:     #0f2a1a;
  --muted:   #5a6e5a;
  --dark:    #0f2a1a;
  --p:       var(--green);
  --pd:      var(--greend);
  --pl:      var(--greenl);
  --s:       var(--gold);
  --navy:    var(--green);
  --navy2:   var(--greend);
  --accent:  var(--gold);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg); color: var(--ink);
  min-height: 100vh; -webkit-font-smoothing: antialiased;
}

/* ── TOP NAV ────────────────────────────────────────────── */
.topnav {
  background: var(--greend);
  height: 64px;
  display: flex; align-items: center;
  padding: 0 24px; gap: 6px;
  position: sticky; top: 0; z-index: 200;
  box-shadow: 0 2px 16px rgba(15,69,39,.3);
}
.nav-logo {
  display: flex; align-items: center;
  text-decoration: none; margin-right: 20px; flex-shrink: 0; gap: 10px;
}
.nav-logo .logo-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, #1a6b3c, #22894e);
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 8px rgba(0,0,0,.25); flex-shrink: 0;
}
.nav-logo .logo-text {
  font-family: 'Playfair Display', serif;
  font-size: 15px; font-weight: 700; color: #fff; line-height: 1.15;
}
.nav-logo .logo-sub { font-size: 9.5px; color: rgba(255,255,255,.45); font-weight: 500; letter-spacing:.05em; text-transform:uppercase; }
.nav-divider { width: 1px; height: 22px; background: rgba(255,255,255,.12); margin: 0 6px; flex-shrink: 0; }
.nav-sp { flex: 1; }
.navbtn {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 13px; border-radius: 6px; border: none;
  font-size: 13px; font-weight: 500; cursor: pointer;
  text-decoration: none; transition: all .2s;
  white-space: nowrap; background: none;
  color: rgba(255,255,255,.55); font-family: 'DM Sans', sans-serif;
}
.navbtn:hover { color: #fff; background: rgba(255,255,255,.08); }
.navbtn.active { color: #fff; background: rgba(255,255,255,.1); }
.navbtn.apply {
  background: var(--gold); color: #fff; font-weight: 700;
  padding: 7px 18px; border-radius: 7px;
  box-shadow: 0 2px 8px rgba(201,148,58,.35);
}
.navbtn.apply:hover { background: var(--goldl); color: #fff; transform: translateY(-1px); }
.navbtn.icon-btn { padding: 7px 10px; position: relative; }
.nav-badge {
  position: absolute; top: 3px; right: 3px;
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--err); border: 2px solid var(--greend);
}

/* User chip */
.nav-user {
  display: flex; align-items: center; gap: 8px;
  padding: 5px 8px 5px 5px; border-radius: 8px;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  cursor: pointer; transition: all .2s;
}
.nav-user:hover { background: rgba(255,255,255,.14); }
.nav-user-av {
  width: 28px; height: 28px; border-radius: 6px;
  background: linear-gradient(135deg, var(--green2), var(--gold));
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.nav-user-name { font-size: 13px; color: rgba(255,255,255,.75); font-weight: 500; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.nav-logout { color: rgba(255,255,255,.45); font-size: 14px; margin-left: 2px; }

/* ── PAGE WRAP ──────────────────────────────────────────── */
.wrap {
  max-width: 960px; margin: 0 auto;
  padding: 28px 24px;
}

/* ── PAGE HEADER ────────────────────────────────────────── */
.page-hdr { margin-bottom: 22px; }
.page-hdr h1 {
  font-family: 'Playfair Display', serif;
  font-size: 28px; font-weight: 700; color: var(--dark);
  letter-spacing: -.02em; line-height: 1.2;
}
.page-hdr p { font-size: 14px; color: var(--muted); margin-top: 4px; }

/* ── CARDS ──────────────────────────────────────────────── */
.card {
  background: var(--card); border-radius: 12px;
  border: 1px solid var(--border);
  overflow: hidden; margin-bottom: 18px;
  box-shadow: 0 1px 4px rgba(15,69,39,.06);
}
.card-hdr {
  padding: 15px 20px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  background: #fafcfa;
}
.card-title { font-size: 14px; font-weight: 600; color: var(--dark); }
.card-body { padding: 20px; }

/* ── STAT GRID ──────────────────────────────────────────── */
.stat-grid {
  display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr));
  gap: 14px; margin-bottom: 20px;
}
.stat {
  background: var(--card); border-radius: 10px;
  border: 1px solid var(--border); padding: 18px 16px;
  box-shadow: 0 1px 4px rgba(15,69,39,.05);
}
.stat-val { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: var(--dark); }
.stat-lbl { font-size: 11px; color: var(--muted); margin-top: 4px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }

/* ── TABLES ─────────────────────────────────────────────── */
.dt { width: 100%; border-collapse: collapse; }
.dt th {
  padding: 10px 14px; text-align: left;
  font-size: 10.5px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--muted);
  background: #f6faf6; border-bottom: 1px solid var(--border);
}
.dt td {
  padding: 12px 14px; font-size: 13.5px;
  border-bottom: 1px solid var(--border); vertical-align: middle;
}
.dt tbody tr:last-child td { border-bottom: none; }
.dt tbody tr:hover { background: #f6faf6; }

/* ── BADGES ─────────────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center;
  padding: 3px 10px; border-radius: 20px;
  font-size: 11px; font-weight: 700;
}
.bp  { background: rgba(26,107,60,.1);  color: var(--green); }
.bok { background: rgba(22,163,74,.1);  color: var(--ok); }
.bw  { background: rgba(217,119,6,.12); color: var(--warn); }
.be  { background: rgba(220,38,38,.1);  color: var(--err); }
.bi  { background: rgba(2,132,199,.1);  color: var(--blue); }
.bs  { background: rgba(90,110,90,.1);  color: var(--muted); }
.ba  { background: rgba(201,148,58,.12); color: var(--gold); }

/* ── BUTTONS ─────────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 18px; border-radius: 8px; border: none;
  font-size: 13.5px; font-weight: 600; cursor: pointer;
  text-decoration: none; transition: all .2s; white-space: nowrap;
  font-family: 'DM Sans', sans-serif;
}
.btn-sm  { padding: 6px 13px; font-size: 12px; border-radius: 6px; }
.btn-xs  { padding: 4px 10px; font-size: 11px; border-radius: 5px; }
.btn-lg  { padding: 12px 26px; font-size: 15px; }
.btn-p   { background: var(--green); color: #fff; }
.btn-p:hover   { background: var(--greend); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(15,69,39,.25); }
.btn-accent { background: var(--gold); color: #fff; }
.btn-accent:hover { background: var(--goldl); color: #fff; }
.btn-ok  { background: var(--ok); color: #fff; }
.btn-ok:hover { background: #15803d; color: #fff; }
.btn-e   { background: var(--err); color: #fff; }
.btn-e:hover { background: #b91c1c; color: #fff; }
.btn-w   { background: var(--warn); color: #fff; }
.btn-o   {
  background: transparent; border: 1.5px solid var(--border);
  color: var(--ink);
}
.btn-o:hover { border-color: var(--green); color: var(--green); }

/* ── FORMS ──────────────────────────────────────────────── */
.fg  { margin-bottom: 16px; }
.fl  { display: block; font-size: 11.5px; font-weight: 700; margin-bottom: 5px; color: var(--muted); letter-spacing: .05em; text-transform: uppercase; }
.fc  {
  width: 100%; padding: 10px 13px;
  border: 1.5px solid var(--border); border-radius: 7px;
  font-size: 13.5px; font-family: 'DM Sans', sans-serif;
  background: #fff; outline: none; transition: all .2s; color: var(--ink);
}
.fc:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(26,107,60,.1); }
select.fc { cursor: pointer; }
.fc.err { border-color: var(--err); }
.ft  { font-size: 12px; color: var(--muted); margin-top: 3px; }
.iv  { font-size: 12px; color: var(--err); margin-top: 3px; display: block; }

/* ── ALERTS ─────────────────────────────────────────────── */
.alert {
  padding: 12px 16px; border-radius: 9px; font-size: 13px;
  display: flex; align-items: flex-start; gap: 9px; margin-bottom: 16px;
}
.a-ok { background: rgba(22,163,74,.08);  color: #15803d; border: 1px solid rgba(22,163,74,.2); }
.a-e  { background: rgba(220,38,38,.07);  color: #991b1b; border: 1px solid rgba(220,38,38,.2); }
.a-w  { background: rgba(217,119,6,.08);  color: #92400e; border: 1px solid rgba(217,119,6,.2); }
.a-i  { background: rgba(26,107,60,.07);  color: var(--greend); border: 1px solid rgba(26,107,60,.18); }

/* ── GRIDS ──────────────────────────────────────────────── */
.g2 { display: grid; grid-template-columns: repeat(2,1fr); gap: 16px; }
.g3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }

/* ── INFO PAIRS ─────────────────────────────────────────── */
.info-lbl { font-size: 11px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.info-val  { font-size: 13.5px; font-weight: 500; color: var(--ink); }

/* ── PROGRESS STEPS ─────────────────────────────────────── */
.step-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
.step-dot.done   { background: var(--green); color: #fff; }
.step-dot.active { background: var(--green); color: #fff; box-shadow: 0 0 0 4px rgba(26,107,60,.2); }
.step-dot.todo   { background: #eef0f7; color: var(--muted); border: 2px solid var(--border); }
.step-line       { height: 2px; background: var(--border); flex: 1; }
.step-line.done  { background: var(--green); }

/* ── TABS ───────────────────────────────────────────────── */
.tabs { display: flex; gap: 2px; border-bottom: 2px solid var(--border); margin-bottom: 20px; overflow-x: auto; }
.tab {
  padding: 10px 16px; font-size: 13px; font-weight: 500;
  color: var(--muted); border: none; background: none; cursor: pointer;
  border-bottom: 2px solid transparent; margin-bottom: -2px;
  white-space: nowrap; transition: all .2s; font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; gap: 6px;
}
.tab:hover { color: var(--dark); }
.tab.active { color: var(--green); border-bottom-color: var(--green); font-weight: 700; }

/* ── MOBILE BOTTOM NAV ───────────────────────────────────── */
.bottomnav {
  display: none; position: fixed; bottom: 0; left: 0; right: 0;
  background: var(--greend);
  border-top: 1px solid rgba(255,255,255,.1);
  padding: 6px 0 env(safe-area-inset-bottom); z-index: 200;
}
.bottomnav a {
  display: flex; flex-direction: column; align-items: center; gap: 3px;
  padding: 6px 0; flex: 1; text-decoration: none;
  color: rgba(255,255,255,.4); font-size: 10px; font-weight: 500;
  transition: color .2s;
}
.bottomnav a.active, .bottomnav a:hover { color: #fff; }
.bottomnav a i { font-size: 19px; }
.bottomnav .apply-tab {
  background: var(--gold); border-radius: 10px;
  margin: 3px; padding: 4px 0; color: #fff !important;
}

@media (max-width: 640px) {
  .bottomnav { display: flex; }
  body { padding-bottom: 70px; }
  .wrap { padding: 16px 14px; }
  .g2, .g3 { grid-template-columns: 1fr; }
  .topnav .navbtn:not(.icon-btn):not(.mobile-show):not(.nav-user) { display: none; }
  .mobile-show { display: inline-flex !important; }
  .nav-user-name { display: none; }
  .nav-divider { display: none; }
}
</style>
@stack('styles')
</head>
<body>

<!-- TOP NAV -->
<nav class="topnav">
  <a href="{{ route('borrower.dashboard') }}" class="nav-logo">
    <div class="logo-icon" style="background:#fff;overflow:hidden">
      <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="height:32px;width:32px;object-fit:contain">
    </div>
    <div>
      <div class="logo-text">{{ config('app.name') }}</div>
      <div class="logo-sub">Member Portal</div>
    </div>
  </a>
  <div class="nav-divider"></div>

  <a href="{{ route('borrower.dashboard') }}"  class="navbtn {{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="{{ route('borrower.loans.index') }}" class="navbtn {{ request()->routeIs('borrower.loans.*') ? 'active' : '' }}"><i class="bi bi-bank"></i> Loans</a>
  <a href="{{ route('borrower.payments.index') }}" class="navbtn {{ request()->routeIs('borrower.payments.*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i> Payments</a>
  <a href="{{ route('borrower.documents.index') }}" class="navbtn {{ request()->routeIs('borrower.documents.*') ? 'active' : '' }}"><i class="bi bi-folder2"></i> Documents</a>
  <a href="{{ route('borrower.referrals.index') }}" class="navbtn {{ request()->routeIs('borrower.referrals.*') ? 'active' : '' }}"><i class="bi bi-gift"></i> Refer & Earn</a>
  <a href="{{ route('borrower.mybill.index') }}" class="navbtn {{ request()->routeIs('borrower.mybill.*') ? 'active' : '' }}"><i class="bi bi-lightning-charge-fill"></i> MyBill</a>
  <a href="{{ route('borrower.float.index') }}" class="navbtn {{ request()->routeIs('borrower.float.*') ? 'active' : '' }}"><i class="bi bi-lightning-fill" style="color:var(--goldl)"></i> MyFloat</a>

  <div class="nav-sp"></div>

  <a href="{{ route('borrower.apply.start') }}" class="navbtn apply"><i class="bi bi-plus-circle-fill"></i> Apply Now</a>
  <a href="{{ route('borrower.referrals.index') }}" class="navbtn mobile-show" style="display:none;background:rgba(255,255,255,.1);color:#fff;border-radius:8px;font-weight:600"><i class="bi bi-gift"></i> Refer</a>
  <a href="{{ route('borrower.mybill.index') }}" class="navbtn mobile-show" style="display:none;background:rgba(201,148,58,.2);color:#e0a843;border-radius:8px;font-weight:700"><i class="bi bi-lightning-charge-fill"></i> MyBill</a>

  @php
    try { $bUnread = \App\Models\Notification::where('user_id', auth('borrower')->id())->where('is_read',false)->count(); } catch(\Exception $e){ $bUnread=0; }
  @endphp
  <a href="{{ route('borrower.notifications.index') }}" class="navbtn icon-btn" title="Notifications">
    <i class="bi bi-bell{{ $bUnread ? '-fill' : '' }}" style="{{ $bUnread ? 'color:var(--goldl)' : 'color:rgba(255,255,255,.5)' }}"></i>
    @if($bUnread)<div class="nav-badge"></div>@endif
  </a>

  <a href="{{ route('borrower.profile.index') }}" class="nav-user" title="Profile">
    <div class="nav-user-av">{{ strtoupper(substr(auth('borrower')->user()->name ?? 'U', 0, 1)) }}</div>
    <span class="nav-user-name">{{ explode(' ', auth('borrower')->user()->name ?? '')[0] }}</span>
    <form method="POST" action="{{ route('borrower.logout') }}" style="display:inline">
      @csrf
      <button type="submit" class="nav-logout" title="Sign out" style="background:none;border:none;cursor:pointer">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </form>
  </a>
</nav>

<!-- PAGE CONTENT -->
<div class="wrap">
  @if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
  @if(session('info'))<div class="alert a-i"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>@endif
  @if($errors->any())<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i><ul style="margin:0;padding-left:14px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  @yield('content')
</div>

<!-- MOBILE BOTTOM NAV -->
<nav class="bottomnav">
  <a href="{{ route('borrower.dashboard') }}"   class="{{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}"><i class="bi bi-house-fill"></i>Home</a>
  <a href="{{ route('borrower.float.index') }}"    class="{{ request()->routeIs('borrower.float.*') ? 'active' : '' }}"><i class="bi bi-lightning-fill"></i>Float</a>
  <a href="{{ route('borrower.apply.start') }}"  class="apply-tab"><i class="bi bi-plus-circle-fill"></i>Apply</a>
  <a href="{{ route('borrower.payments.index') }}" class="{{ request()->routeIs('borrower.payments.*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i>Pay</a>
  <a href="{{ route('borrower.profile.index') }}"  class="{{ request()->routeIs('borrower.profile.*') ? 'active' : '' }}"><i class="bi bi-person-circle"></i>Me</a>
</nav>

<!-- SESSION IDLE MODAL -->
<div id="idleModal" style="position:fixed;inset:0;background:rgba(10,30,18,.6);backdrop-filter:blur(4px);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
    <div style="padding:24px;text-align:center">
      <div style="width:60px;height:60px;background:rgba(201,148,58,.12);color:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px">
        <i class="bi bi-clock-history"></i>
      </div>
      <h3 style="font-size:18px;font-weight:700;color:var(--dark);margin-bottom:8px;font-family:'Playfair Display',serif">Session Expiring Soon</h3>
      <p style="font-size:14px;color:var(--muted);margin-bottom:24px;line-height:1.5">You have been inactive for a while. For your security, you will be logged out in <strong id="idleCounter" style="color:var(--err)">60</strong> seconds.</p>
      <div style="display:flex;gap:12px">
        <button type="button" onclick="stayLoggedIn()" class="btn btn-p" style="flex:1;justify-content:center">Stay Logged In</button>
        <form action="{{ route('borrower.logout') }}" method="POST" style="display:none" id="idleLogoutForm">@csrf</form>
      </div>
    </div>
  </div>
</div>

<script>
// ── IDLE AUTO-LOGOUT (30 min idle → 60 s warning → logout) ──────────
(function(){
  const IDLE_MS    = 30 * 60 * 1000;
  const WARN_SECS  = 60;
  const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const LOGOUT_URL = "{{ route('borrower.logout') }}";
  let idleTimer, countdownTimer, warnActive = false, secs = WARN_SECS;
  const modal   = document.getElementById('idleModal');
  const counter = document.getElementById('idleCounter');
  function doLogout() {
    const f = document.createElement('form');
    f.method = 'POST'; f.action = LOGOUT_URL;
    const t = document.createElement('input');
    t.type = 'hidden'; t.name = '_token'; t.value = CSRF;
    f.appendChild(t); document.body.appendChild(f); f.submit();
  }
  function showWarning() {
    warnActive = true; secs = WARN_SECS; counter.textContent = secs;
    modal.style.display = 'flex';
    countdownTimer = setInterval(() => {
      secs--; counter.textContent = secs;
      if (secs <= 0) { clearInterval(countdownTimer); doLogout(); }
    }, 1000);
  }
  window.stayLoggedIn = function() {
    clearInterval(countdownTimer);
    modal.style.display = 'none';
    warnActive = false;
    resetIdle();
  };
  function resetIdle() {
    if (warnActive) return;
    clearTimeout(idleTimer);
    idleTimer = setTimeout(showWarning, IDLE_MS);
  }
  ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(ev =>
    document.addEventListener(ev, resetIdle, { passive: true })
  );
  resetIdle();
})();
</script>

@stack('scripts')
</body>
</html>
