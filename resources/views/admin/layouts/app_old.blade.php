<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Admin') — LoanPlatform</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root{--p:#4f46e5;--pd:#3730a3;--pl:#818cf8;--s:#0ea5e9;--ok:#10b981;--warn:#f59e0b;--err:#ef4444;--info:#06b6d4;--dark:#0f172a;--sb:260px;--th:64px;--bg:#f1f5f9;--card:#fff;--border:#e2e8f0;--muted:#64748b}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--dark);min-height:100vh;display:flex}
/* SIDEBAR */
.sb{width:var(--sb);min-height:100vh;background:var(--dark);position:fixed;left:0;top:0;z-index:1000;display:flex;flex-direction:column}
.sb-logo{padding:18px 22px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:12px}
.sb-logo .icon{width:38px;height:38px;background:linear-gradient(135deg,var(--p),var(--s));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff}
.sb-logo .txt{color:#fff;font-weight:700;font-size:15px}.sb-logo .sub{color:rgba(255,255,255,.4);font-size:11px}
.sb-nav{flex:1;padding:10px 0;overflow-y:auto}
.nav-lbl{color:rgba(255,255,255,.3);font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;padding:14px 22px 4px}
.nav-item a{display:flex;align-items:center;gap:11px;padding:10px 22px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13.5px;font-weight:500;transition:all .2s;position:relative}
.nav-item a:hover{color:#fff;background:rgba(255,255,255,.06)}
.nav-item a.active{color:#fff;background:rgba(79,70,229,.3)}
.nav-item a.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--pl);border-radius:0 3px 3px 0}
.nav-item a i{font-size:16px;width:18px;flex-shrink:0}
.sb-foot{padding:14px 22px;border-top:1px solid rgba(255,255,255,.08)}
.upill{display:flex;align-items:center;gap:10px}
.uav{width:34px;height:34px;background:linear-gradient(135deg,var(--p),var(--s));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0}
.uav .uname{color:#fff;font-size:13px;font-weight:600}.urole{color:rgba(255,255,255,.4);font-size:11px}
/* MAIN */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-height:100vh}
/* TOPBAR */
.topbar{height:var(--th);background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 26px;gap:14px;position:sticky;top:0;z-index:100}
.topbar-title{font-size:17px;font-weight:700}.topbar-bc{font-size:12px;color:var(--muted)}.topbar-bc a{color:var(--p);text-decoration:none}
.spacer{flex:1}
.tbtn{width:36px;height:36px;border:none;background:var(--bg);border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--muted);position:relative;transition:background .2s}
.tbtn:hover{background:var(--border);color:var(--dark)}
.ndot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--err);border-radius:50%;border:2px solid #fff}
/* CONTENT */
.pc{padding:26px;flex:1}
/* CARD */
.card{background:var(--card);border-radius:16px;border:1px solid var(--border);overflow:hidden}
.card-hdr{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:700}
.card-body{padding:22px}
/* STAT */
.sc{background:var(--card);border-radius:14px;border:1px solid var(--border);padding:20px;display:flex;align-items:flex-start;gap:14px}
.si{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.si.p{background:rgba(79,70,229,.1);color:var(--p)}.si.ok{background:rgba(16,185,129,.1);color:var(--ok)}
.si.w{background:rgba(245,158,11,.1);color:var(--warn)}.si.e{background:rgba(239,68,68,.1);color:var(--err)}
.si.i{background:rgba(6,182,212,.1);color:var(--info)}.si.s{background:rgba(14,165,233,.1);color:var(--s)}
.sv{font-size:24px;font-weight:800;line-height:1.2}.sl{font-size:12px;color:var(--muted);margin-top:2px}
/* TABLE */
.dt{width:100%;border-collapse:collapse}
.dt th{padding:11px 15px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--border)}
.dt td{padding:13px 15px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.dt tbody tr:hover{background:#f8fafc}.dt tbody tr:last-child td{border-bottom:none}
/* BADGE */
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.bp{background:rgba(79,70,229,.1);color:var(--p)}.bok{background:rgba(16,185,129,.1);color:var(--ok)}
.bw{background:rgba(245,158,11,.15);color:var(--warn)}.be{background:rgba(239,68,68,.1);color:var(--err)}
.bi{background:rgba(6,182,212,.1);color:var(--info)}.bs{background:rgba(100,116,139,.1);color:var(--muted)}
/* BUTTON */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 17px;border-radius:10px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;white-space:nowrap}
.btn-sm{padding:5px 12px;font-size:12px;border-radius:8px}.btn-xs{padding:3px 8px;font-size:11px;border-radius:6px}
.btn-p{background:var(--p);color:#fff}.btn-p:hover{background:var(--pd);color:#fff}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#059669;color:#fff}
.btn-e{background:var(--err);color:#fff}.btn-e:hover{background:#dc2626;color:#fff}
.btn-w{background:var(--warn);color:#fff}.btn-w:hover{background:#d97706;color:#fff}
.btn-i{background:var(--info);color:#fff}.btn-i:hover{background:#0891b2;color:#fff}
.btn-o{background:transparent;border:1.5px solid var(--border);color:var(--dark)}.btn-o:hover{border-color:var(--p);color:var(--p)}
/* FORM */
.fg{margin-bottom:18px}.fl{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
.fc{width:100%;padding:9px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;font-family:'Inter',sans-serif;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s}
.fc:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(79,70,229,.1)}
select.fc{cursor:pointer}.fc.err{border-color:var(--err)}
.ft{font-size:12px;color:var(--muted);margin-top:3px}.iv{font-size:12px;color:var(--err);margin-top:3px;display:block}
/* ALERT */
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:flex-start;gap:9px;margin-bottom:18px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
.a-w{background:rgba(245,158,11,.08);color:#92400e;border:1px solid rgba(245,158,11,.2)}
.a-i{background:rgba(6,182,212,.08);color:#0c4a6e;border:1px solid rgba(6,182,212,.2)}
/* GRID */
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
/* MODAL */
.mo{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.mo.open{display:flex}
.mb{background:#fff;border-radius:18px;width:100%;max-width:520px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:mIn .22s ease}
.mh{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.mt{font-size:15px;font-weight:700}
.mc{background:none;border:none;cursor:pointer;font-size:22px;color:var(--muted);width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px}
.mc:hover{background:var(--bg)}.mbody{padding:22px}
.mf{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:9px}
@keyframes mIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}
/* MISC */
.flex{display:flex}.aic{align-items:center}.jb{justify-content:space-between}
.gap2{gap:8px}.gap3{gap:12px}.mb4{margin-bottom:16px}.mb6{margin-bottom:24px}.mt4{margin-top:16px}
.tc{text-align:center}.tr{text-align:right}.muted{color:var(--muted);font-size:12.5px}
.av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0}
.av-sm{width:28px;height:28px;font-size:11px}.av-lg{width:52px;height:52px;font-size:18px}
.empty{text-align:center;padding:50px 20px;color:var(--muted)}.empty i{font-size:44px;opacity:.35;display:block;margin-bottom:10px}
.filter-bar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:18px;padding:16px;background:#fff;border-radius:13px;border:1px solid var(--border)}
.filter-bar .fg{margin-bottom:0;min-width:140px}
.tabs{display:flex;gap:3px;border-bottom:2px solid var(--border);margin-bottom:22px}
.tab{padding:9px 16px;border:none;background:none;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s}
.tab:hover{color:var(--p)}.tab.active{color:var(--p);border-bottom-color:var(--p)}
.tpanel{display:none}.tpanel.active{display:block}
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px}
.info-lbl{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)}
.info-val{font-size:13.5px;font-weight:600;margin-top:2px}
::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
@media(max-width:1024px){.g4{grid-template-columns:repeat(2,1fr)}.g3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){.sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}.main{margin-left:0}.g4,.g3,.g2{grid-template-columns:1fr}}
</style>
@stack('styles')
</head>
<body>
<aside class="sb" id="sb">
  <div class="sb-logo">
    <div class="icon"><i class="bi bi-bank2"></i></div>
    <div><div class="txt">LoanPlatform</div><div class="sub">Admin Portal</div></div>
  </div>
  <nav class="sb-nav">
    <div class="nav-lbl">Overview</div>
    <div class="nav-item"><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'active':'' }}"><i class="bi bi-grid-fill"></i> Dashboard</a></div>
    <div class="nav-lbl">Lending</div>
    <div class="nav-item"><a href="{{ route('admin.applications.index') }}" class="{{ request()->routeIs('admin.applications.*')?'active':'' }}"><i class="bi bi-file-earmark-text-fill"></i> Applications</a></div>
    <div class="nav-item"><a href="{{ route('admin.loans.index') }}" class="{{ request()->routeIs('admin.loans.*')?'active':'' }}"><i class="bi bi-currency-dollar"></i> Loan Management</a></div>
    <div class="nav-item"><a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*')?'active':'' }}"><i class="bi bi-credit-card-fill"></i> Payments</a></div>
    <div class="nav-item"><a href="{{ route('admin.credit.index') }}" class="{{ request()->routeIs('admin.credit.*')?'active':'' }}"><i class="bi bi-shield-check-fill"></i> Credit Bureau</a></div>
    <div class="nav-lbl">Reports</div>
    <div class="nav-item"><a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*')?'active':'' }}"><i class="bi bi-bar-chart-fill"></i> Reports</a></div>
    <div class="nav-lbl">Config</div>
    <div class="nav-item"><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*')?'active':'' }}"><i class="bi bi-people-fill"></i> Users</a></div>
    <div class="nav-item"><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*')?'active':'' }}"><i class="bi bi-box-fill"></i> Loan Products</a></div>
    <div class="nav-item"><a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*')?'active':'' }}"><i class="bi bi-gear-fill"></i> Settings</a></div>
    <div class="nav-lbl">System</div>
    <div class="nav-item">
      <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*')?'active':'' }}">
        <i class="bi bi-bell-fill"></i> Notifications
        @php try { $unreadNotifs = \App\Models\Notification::where('user_id', auth('admin')->id())->where('is_read',false)->count(); } catch(\Exception $e) { $unreadNotifs = 0; } @endphp
        @if($unreadNotifs > 0)
        <span style="margin-left:auto;background:var(--err);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;line-height:1.8">{{ $unreadNotifs }}</span>
        @endif
      </a>
    </div>
    <div class="nav-item"><a href="{{ route('admin.audit.index') }}" class="{{ request()->routeIs('admin.audit.*')?'active':'' }}"><i class="bi bi-journal-text"></i> Audit Log</a></div>
  </nav>
  <div class="sb-foot">
    <a href="{{ route('admin.profile.index') }}" style="text-decoration:none;display:block" title="My Profile">
    <div class="upill" style="cursor:pointer;transition:background .2s" onmouseover="this.style.background='rgba(79,70,229,.08)'" onmouseout="this.style.background=''">
      <div class="uav">{{ strtoupper(substr(auth('admin')->user()->name??'A',0,1)) }}</div>
      <div>
        <div class="uname">{{ auth('admin')->user()->name??'Admin' }}</div>
        <div class="urole">{{ ucfirst(str_replace('_',' ',auth('admin')->user()->role??'admin')) }}</div>
      </div>
      <i class="bi bi-chevron-right" style="margin-left:auto;font-size:11px;color:var(--muted)"></i>
    </div>
    </a>
  </div>
</aside>
<div class="main">
  <header class="topbar">
    <button class="tbtn" id="sbToggle"><i class="bi bi-list"></i></button>
    <div><div class="topbar-title">@yield('page-title','Dashboard')</div>@hasSection('bc')<div class="topbar-bc">@yield('bc')</div>@endif</div>
    <div class="spacer"></div>
    <div class="flex aic gap2">
      @php try { $bellUnread = \App\Models\Notification::where('user_id', auth('admin')->id())->where('is_read',false)->count(); } catch(\Exception $e) { $bellUnread = 0; } @endphp
      <a href="{{ route('admin.notifications.index') }}" class="tbtn" style="text-decoration:none;position:relative" title="Notifications">
        <i class="bi bi-bell{{ $bellUnread > 0 ? '-fill' : '' }}" style="{{ $bellUnread > 0 ? 'color:var(--p)' : '' }}"></i>
        @if($bellUnread > 0)<span class="ndot"></span>@endif
      </a>
      <form method="POST" action="{{ route('admin.logout') }}" style="margin:0">@csrf<button type="submit" class="tbtn" title="Logout"><i class="bi bi-box-arrow-right"></i></button></form>
    </div>
  </header>
  <div style="padding:0 26px;margin-top:14px">
    @if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
    @if(session('info'))<div class="alert a-i"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>@endif
    @if($errors->any())<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i><ul style="margin:0;padding-left:14px">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>@endif
  </div>
  <main class="pc">@yield('content')</main>
</div>
<script>
const sbToggle=document.getElementById('sbToggle'),sb=document.getElementById('sb');
sbToggle.addEventListener('click',()=>sb.classList.toggle('open'));
setTimeout(()=>document.querySelectorAll('.alert').forEach(el=>{el.style.transition='opacity .5s';el.style.opacity='0';setTimeout(()=>el.remove(),500)}),4500);
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.mo').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
function switchTab(g,id){document.querySelectorAll('[data-tg="'+g+'"]').forEach(e=>e.classList.remove('active'));document.querySelectorAll('[data-pg="'+g+'"]').forEach(e=>e.classList.remove('active'));document.querySelector('[data-tg="'+g+'"][data-t="'+id+'"]').classList.add('active');document.querySelector('[data-pg="'+g+'"][data-p="'+id+'"]').classList.add('active')}
</script>
@stack('scripts')
</body>
</html>
