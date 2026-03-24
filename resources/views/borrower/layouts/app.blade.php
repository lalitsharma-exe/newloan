<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Portal') — MyLoan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ─────────────────────────────────────────────────────────
   NAVY BLUE PORTAL THEME
───────────────────────────────────────────────────────── */
:root {
  --navy:    #0d1b3e;
  --navy2:   #162552;
  --navy3:   #1e3370;
  --blue:    #2b4bad;
  --blue2:   #3d60d4;
  --light:   #7c9ff5;
  --accent:  #8cc63f;
  --accent2: #7ab237;
  --ok:      #10b981;
  --warn:    #f59e0b;
  --err:     #ef4444;
  --bg:      #f0f3fa;
  --card:    #ffffff;
  --border:  #dde3ef;
  --ink:     #1c2433;
  --muted:   #5a6b85;
  --dark:    #0d1b3e;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Outfit', sans-serif;
  background: var(--bg); color: var(--ink);
  min-height: 100vh; -webkit-font-smoothing: antialiased;
}

/* ── TOP NAV ────────────────────────────────────────────── */
.topnav {
  background: var(--navy);
  height: 62px;
  display: flex; align-items: center;
  padding: 0 24px; gap: 6px;
  position: sticky; top: 0; z-index: 200;
  box-shadow: 0 2px 16px rgba(13,27,62,.35);
}
.nav-logo {
  display: flex; align-items: center;
  text-decoration: none; margin-right: 24px; flex-shrink: 0;
}
.nav-logo img { height: 32px; width: auto; object-fit: contain; display: block; }
.nav-divider { width: 1px; height: 22px; background: rgba(255,255,255,.12); margin: 0 6px; flex-shrink: 0; }
.nav-sp { flex: 1; }
.navbtn {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 13px; border-radius: 6px; border: none;
  font-size: 13px; font-weight: 500; cursor: pointer;
  text-decoration: none; transition: all .2s;
  white-space: nowrap; background: none;
  color: rgba(255,255,255,.55); font-family: 'Outfit', sans-serif;
}
.navbtn:hover { color: #fff; background: rgba(255,255,255,.08); }
.navbtn.active { color: #fff; background: rgba(255,255,255,.1); }
.navbtn.apply {
  background: var(--accent); color: #fff; font-weight: 600;
  padding: 7px 18px;
}
.navbtn.apply:hover { background: #a88030; color: #fff; }
.navbtn.icon-btn { padding: 7px 10px; position: relative; }
.nav-badge {
  position: absolute; top: 3px; right: 3px;
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--err); border: 2px solid var(--navy);
}

/* User chip */
.nav-user {
  display: flex; align-items: center; gap: 8px;
  padding: 5px 8px 5px 5px; border-radius: 8px;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.1);
  cursor: pointer; transition: all .2s;
}
.nav-user:hover { background: rgba(255,255,255,.12); }
.nav-user-av {
  width: 28px; height: 28px; border-radius: 6px;
  background: linear-gradient(135deg, var(--blue2), var(--blue));
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
  font-family: 'Cormorant Garamond', serif;
  font-size: 28px; font-weight: 700; color: var(--navy);
  letter-spacing: -.02em; line-height: 1.2;
}
.page-hdr p { font-size: 14px; color: var(--muted); margin-top: 4px; }

/* ── CARDS ──────────────────────────────────────────────── */
.card {
  background: var(--card); border-radius: 12px;
  border: 1px solid var(--border);
  overflow: hidden; margin-bottom: 18px;
  box-shadow: 0 1px 4px rgba(13,27,62,.05);
}
.card-hdr {
  padding: 15px 20px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  background: #fafbff;
}
.card-title { font-size: 14px; font-weight: 600; color: var(--navy); }
.card-body { padding: 20px; }

/* ── STAT GRID ──────────────────────────────────────────── */
.stat-grid {
  display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr));
  gap: 14px; margin-bottom: 20px;
}
.stat {
  background: var(--card); border-radius: 10px;
  border: 1px solid var(--border); padding: 18px 16px;
  box-shadow: 0 1px 4px rgba(13,27,62,.04);
}
.stat-val { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 700; color: var(--navy); }
.stat-lbl { font-size: 11px; color: var(--muted); margin-top: 4px; font-weight: 500; letter-spacing: .04em; text-transform: uppercase; }

/* ── TABLES ─────────────────────────────────────────────── */
.dt { width: 100%; border-collapse: collapse; }
.dt th {
  padding: 10px 14px; text-align: left;
  font-size: 11px; font-weight: 600; text-transform: uppercase;
  letter-spacing: .06em; color: var(--muted);
  background: #f5f7fd; border-bottom: 1px solid var(--border);
}
.dt td {
  padding: 12px 14px; font-size: 13.5px;
  border-bottom: 1px solid var(--border); vertical-align: middle;
}
.dt tbody tr:last-child td { border-bottom: none; }
.dt tbody tr:hover { background: #fafbff; }

/* ── BADGES ─────────────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center;
  padding: 3px 10px; border-radius: 20px;
  font-size: 11.5px; font-weight: 600;
}
.bp  { background: rgba(43,75,173,.1);  color: var(--blue); }
.bok { background: rgba(16,185,129,.1); color: #059669; }
.bw  { background: rgba(245,158,11,.15); color: #d97706; }
.be  { background: rgba(239,68,68,.1);  color: #dc2626; }
.bi  { background: rgba(124,159,245,.12); color: var(--blue2); }
.bs  { background: rgba(90,107,133,.1); color: var(--muted); }
.ba  { background: rgba(201,168,76,.12); color: var(--accent); }

/* ── BUTTONS ─────────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 18px; border-radius: 8px; border: none;
  font-size: 13.5px; font-weight: 600; cursor: pointer;
  text-decoration: none; transition: all .2s; white-space: nowrap;
  font-family: 'Outfit', sans-serif;
}
.btn-sm  { padding: 6px 13px; font-size: 12px; border-radius: 6px; }
.btn-xs  { padding: 4px 10px; font-size: 11px; border-radius: 5px; }
.btn-lg  { padding: 12px 26px; font-size: 15px; }
.btn-p   { background: var(--navy); color: #fff; }
.btn-p:hover   { background: var(--navy2); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(13,27,62,.25); }
.btn-accent { background: var(--accent); color: #fff; }
.btn-accent:hover { background: #a88030; color: #fff; }
.btn-ok  { background: var(--ok); color: #fff; }
.btn-ok:hover { background: #059669; color: #fff; }
.btn-e   { background: var(--err); color: #fff; }
.btn-e:hover { background: #b91c1c; color: #fff; }
.btn-w   { background: var(--warn); color: #fff; }
.btn-o   {
  background: transparent; border: 1.5px solid var(--border);
  color: var(--ink);
}
.btn-o:hover { border-color: var(--blue); color: var(--blue); }

/* ── FORMS ──────────────────────────────────────────────── */
.fg  { margin-bottom: 16px; }
.fl  { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: var(--muted); letter-spacing: .04em; text-transform: uppercase; }
.fc  {
  width: 100%; padding: 10px 13px;
  border: 1.5px solid var(--border); border-radius: 7px;
  font-size: 13.5px; font-family: 'Outfit', sans-serif;
  background: #fff; outline: none; transition: all .2s; color: var(--ink);
}
.fc:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(43,75,173,.1); }
select.fc { cursor: pointer; }
.fc.err { border-color: var(--err); }
.ft  { font-size: 12px; color: var(--muted); margin-top: 3px; }
.iv  { font-size: 12px; color: var(--err); margin-top: 3px; display: block; }

/* ── ALERTS ─────────────────────────────────────────────── */
.alert {
  padding: 12px 16px; border-radius: 9px; font-size: 13px;
  display: flex; align-items: flex-start; gap: 9px; margin-bottom: 16px;
}
.a-ok { background: rgba(16,185,129,.08);  color: #065f46; border: 1px solid rgba(16,185,129,.2); }
.a-e  { background: rgba(239,68,68,.08);   color: #991b1b; border: 1px solid rgba(239,68,68,.2); }
.a-w  { background: rgba(245,158,11,.08);  color: #92400e; border: 1px solid rgba(245,158,11,.2); }
.a-i  { background: rgba(43,75,173,.07);   color: var(--navy2); border: 1px solid rgba(43,75,173,.18); }

/* ── GRIDS ──────────────────────────────────────────────── */
.g2 { display: grid; grid-template-columns: repeat(2,1fr); gap: 16px; }
.g3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }

/* ── INFO PAIRS ─────────────────────────────────────────── */
.info-lbl { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.info-val  { font-size: 13.5px; font-weight: 500; color: var(--ink); }

/* ── PROGRESS STEPS ─────────────────────────────────────── */
.step-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
.step-dot.done   { background: var(--blue); color: #fff; }
.step-dot.active { background: var(--blue); color: #fff; box-shadow: 0 0 0 4px rgba(43,75,173,.2); }
.step-dot.todo   { background: #eef0f7; color: var(--muted); border: 2px solid var(--border); }
.step-line       { height: 2px; background: var(--border); flex: 1; }
.step-line.done  { background: var(--blue); }

/* ── TABS ───────────────────────────────────────────────── */
.tabs { display: flex; gap: 2px; border-bottom: 2px solid var(--border); margin-bottom: 20px; overflow-x: auto; }
.tab {
  padding: 10px 16px; font-size: 13px; font-weight: 500;
  color: var(--muted); border: none; background: none; cursor: pointer;
  border-bottom: 2px solid transparent; margin-bottom: -2px;
  white-space: nowrap; transition: all .2s; font-family: 'Outfit', sans-serif;
  display: flex; align-items: center; gap: 6px;
}
.tab:hover { color: var(--navy); }
.tab.active { color: var(--blue); border-bottom-color: var(--blue); font-weight: 600; }

/* ── MOBILE BOTTOM NAV ───────────────────────────────────── */
.bottomnav {
  display: none; position: fixed; bottom: 0; left: 0; right: 0;
  background: var(--navy);
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
  background: var(--accent); border-radius: 10px;
  margin: 3px; padding: 4px 0; color: #fff !important;
}

@media (max-width: 640px) {
  .bottomnav { display: flex; }
  body { padding-bottom: 70px; }
  .wrap { padding: 16px 14px; }
  .g2, .g3 { grid-template-columns: 1fr; }
  .topnav .navbtn:not(.icon-btn):not(.apply):not(.nav-user) { display: none; }
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
    <img src="https://ik.imagekit.io/ygydr1m84/png.webp" alt="MyLoan">
  </a>
  <div class="nav-divider"></div>

  <a href="{{ route('borrower.dashboard') }}"  class="navbtn {{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="{{ route('borrower.loans.index') }}" class="navbtn {{ request()->routeIs('borrower.loans.*') ? 'active' : '' }}"><i class="bi bi-bank"></i> Loans</a>
  <a href="{{ route('borrower.payments.index') }}" class="navbtn {{ request()->routeIs('borrower.payments.*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i> Payments</a>
  <a href="{{ route('borrower.documents.index') }}" class="navbtn {{ request()->routeIs('borrower.documents.*') ? 'active' : '' }}"><i class="bi bi-folder2"></i> Documents</a>

  <div class="nav-sp"></div>

  <a href="{{ route('borrower.apply.start') }}" class="navbtn apply"><i class="bi bi-plus-circle-fill"></i> Apply Now</a>

  @php
    try { $bUnread = \App\Models\Notification::where('user_id', auth('borrower')->id())->where('is_read',false)->count(); } catch(\Exception $e){ $bUnread=0; }
  @endphp
  <a href="{{ route('borrower.notifications.index') }}" class="navbtn icon-btn" title="Notifications">
    <i class="bi bi-bell{{ $bUnread ? '-fill' : '' }}" style="{{ $bUnread ? 'color:var(--accent2)' : 'color:rgba(255,255,255,.5)' }}"></i>
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
  <a href="{{ route('borrower.loans.index') }}"  class="{{ request()->routeIs('borrower.loans.*') ? 'active' : '' }}"><i class="bi bi-bank"></i>Loans</a>
  <a href="{{ route('borrower.apply.start') }}"  class="apply-tab"><i class="bi bi-plus-circle-fill"></i>Apply</a>
  <a href="{{ route('borrower.payments.index') }}" class="{{ request()->routeIs('borrower.payments.*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i>Pay</a>
  <a href="{{ route('borrower.profile.index') }}"  class="{{ request()->routeIs('borrower.profile.*') ? 'active' : '' }}"><i class="bi bi-person-circle"></i>Me</a>
</nav>

@stack('scripts')
</body>
</html>
