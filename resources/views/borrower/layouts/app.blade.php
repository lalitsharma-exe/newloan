<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Portal') — MyLoan</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root{--p:#1a5c2e;--pd:#134821;--pl:#2d8a47;--s:#4caf69;--ok:#10b981;--warn:#f59e0b;--err:#ef4444;--dark:#0f172a;--bg:#f1f5f9;--card:#fff;--border:#e2e8f0;--muted:#64748b}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--dark);min-height:100vh}
/* TOP NAV */
.topnav{background:#fff;border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 20px;gap:14px;position:sticky;top:0;z-index:100}
.tnlogo{font-size:18px;font-weight:900;color:var(--p);text-decoration:none;display:flex;align-items:center;gap:8px}
.tnlogo span{font-weight:300;color:var(--muted);font-size:13px}
.tnspc{flex:1}
.tnbtn{display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;white-space:nowrap;background:none;color:var(--muted)}
.tnbtn:hover,.tnbtn.active{color:var(--p);background:rgba(26,92,46,.06)}
.tnbtn.primary{background:var(--p);color:#fff}.tnbtn.primary:hover{background:var(--pd)}
/* MAIN */
.wrap{max-width:900px;margin:0 auto;padding:24px 20px}
/* CARD */
.card{background:var(--card);border-radius:16px;border:1px solid var(--border);overflow:hidden;margin-bottom:18px}
.card-hdr{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:700}
.card-body{padding:20px}
/* STAT CARDS */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px}
.stat{background:var(--card);border-radius:14px;border:1px solid var(--border);padding:18px;text-align:center}
.stat-val{font-size:24px;font-weight:800;color:var(--p)}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:3px}
/* TABLE */
.dt{width:100%;border-collapse:collapse}
.dt th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--border)}
.dt td{padding:12px 14px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.dt tbody tr:last-child td{border-bottom:none}
/* BADGE */
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.bp{background:rgba(26,92,46,.1);color:var(--p)}.bok{background:rgba(16,185,129,.1);color:var(--ok)}
.bw{background:rgba(245,158,11,.15);color:var(--warn)}.be{background:rgba(239,68,68,.1);color:var(--err)}
.bi{background:rgba(6,182,212,.1);color:#0891b2}.bs{background:rgba(100,116,139,.1);color:var(--muted)}
/* BTN */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:10px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;white-space:nowrap}
.btn-sm{padding:5px 12px;font-size:12px;border-radius:8px}
.btn-p{background:var(--p);color:#fff}.btn-p:hover{background:var(--pd);color:#fff}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#059669;color:#fff}
.btn-e{background:var(--err);color:#fff}
.btn-o{background:transparent;border:1.5px solid var(--border);color:var(--dark)}.btn-o:hover{border-color:var(--p);color:var(--p)}
.btn-w{background:var(--warn);color:#fff}
/* FORM */
.fg{margin-bottom:16px}.fl{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
.fc{width:100%;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;font-family:'Inter',sans-serif;background:#fff;outline:none;transition:all .2s}
.fc:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(26,92,46,.1)}
select.fc{cursor:pointer}.fc.err{border-color:var(--err)}
.ft{font-size:12px;color:var(--muted);margin-top:3px}.iv{font-size:12px;color:var(--err);margin-top:3px;display:block}
/* ALERT */
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:flex-start;gap:9px;margin-bottom:16px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
.a-w{background:rgba(245,158,11,.08);color:#92400e;border:1px solid rgba(245,158,11,.2)}
.a-i{background:rgba(6,182,212,.08);color:#0c4a6e;border:1px solid rgba(6,182,212,.2)}
/* GRID */
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
/* INFO */
.info-lbl{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px}
.info-val{font-size:13.5px;font-weight:500}
/* PROGRESS STEPS */
.steps{display:flex;align-items:center;gap:0;margin-bottom:24px;overflow-x:auto;padding-bottom:4px}
.step-item{display:flex;align-items:center;flex-shrink:0}
.step-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
.step-dot.done{background:var(--p);color:#fff}
.step-dot.active{background:var(--p);color:#fff;box-shadow:0 0 0 4px rgba(26,92,46,.2)}
.step-dot.todo{background:#f1f5f9;color:var(--muted);border:2px solid var(--border)}
.step-line{width:30px;height:2px;background:var(--border);flex-shrink:0}
.step-line.done{background:var(--p)}
/* MOBILE BOTTOM NAV */
.bottomnav{display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--border);padding:6px 0 env(safe-area-inset-bottom);z-index:200}
.bottomnav a{display:flex;flex-direction:column;align-items:center;gap:2px;padding:6px 0;flex:1;text-decoration:none;color:var(--muted);font-size:10px;font-weight:500}
.bottomnav a.active,.bottomnav a:hover{color:var(--p)}
.bottomnav a i{font-size:20px}
@media(max-width:600px){
  .bottomnav{display:flex}
  body{padding-bottom:70px}
  .wrap{padding:16px 14px}
  .g2,.g3{grid-template-columns:1fr}
  .topnav .tnbtn:not(.primary):not(.icon-btn){display:none}
}
</style>
@stack('styles')
</head>
<body>

{{-- TOP NAV --}}
<nav class="topnav">
  <a href="{{ route('borrower.dashboard') }}" class="tnlogo">
    <i class="bi bi-bank2"></i> MyLoan <span>Portal</span>
  </a>
  <div class="tnspc"></div>
  <a href="{{ route('borrower.dashboard') }}" class="tnbtn {{ request()->routeIs('borrower.dashboard')?'active':'' }}"><i class="bi bi-speedometer2"></i> Home</a>
  <a href="{{ route('borrower.loans.index') }}" class="tnbtn {{ request()->routeIs('borrower.loans.*')?'active':'' }}"><i class="bi bi-bank"></i> Loans</a>
  <a href="{{ route('borrower.apply.start') }}" class="tnbtn primary"><i class="bi bi-plus-circle-fill"></i> Apply</a>
  @php try { $bUnread = \App\Models\Notification::where('user_id', auth('borrower')->id())->where('is_read',false)->count(); } catch(\Exception $e){ $bUnread=0; } @endphp
  <a href="{{ route('borrower.notifications.index') }}" class="tnbtn icon-btn" style="position:relative">
    <i class="bi bi-bell{{ $bUnread?'-fill':'' }}" style="{{ $bUnread?'color:var(--p)':'' }}"></i>
    @if($bUnread)<span style="position:absolute;top:4px;right:4px;width:7px;height:7px;background:var(--err);border-radius:50%;border:2px solid #fff"></span>@endif
  </a>
  <a href="{{ route('borrower.profile.index') }}" class="tnbtn icon-btn"><i class="bi bi-person-circle"></i></a>
  <form method="POST" action="{{ route('borrower.logout') }}" style="display:inline">@csrf
    <button type="submit" class="tnbtn icon-btn" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
  </form>
</nav>

{{-- CONTENT --}}
<div class="wrap">
  @if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
  @if(session('info'))<div class="alert a-i"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>@endif
  @if($errors->any())<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i><ul style="margin:0;padding-left:14px">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>@endif
  @yield('content')
</div>

{{-- MOBILE BOTTOM NAV --}}
<nav class="bottomnav">
  <a href="{{ route('borrower.dashboard') }}" class="{{ request()->routeIs('borrower.dashboard')?'active':'' }}"><i class="bi bi-house-fill"></i>Home</a>
  <a href="{{ route('borrower.loans.index') }}" class="{{ request()->routeIs('borrower.loans.*')?'active':'' }}"><i class="bi bi-bank"></i>Loans</a>
  <a href="{{ route('borrower.apply.start') }}" style="color:var(--p)"><i class="bi bi-plus-circle-fill"></i>Apply</a>
  <a href="{{ route('borrower.payments.index') }}" class="{{ request()->routeIs('borrower.payments.*')?'active':'' }}"><i class="bi bi-credit-card"></i>Pay</a>
  <a href="{{ route('borrower.profile.index') }}" class="{{ request()->routeIs('borrower.profile.*')?'active':'' }}"><i class="bi bi-person-circle"></i>Profile</a>
</nav>

@stack('scripts')
</body>
</html>
