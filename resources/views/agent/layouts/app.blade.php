<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name') }} — Agent Portal</title>
<link rel="shortcut icon" href="https://ik.imagekit.io/ygydr1m84/2699f0f4-26da-41ec-92ff-ce4aa8ac0f79.jpeg" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── AGENT MODULE DESIGN SYSTEM ── Mobile-first, fresh teal/green accent ── */
:root{--p:#0f766e;--pd:#0d5b55;--pl:#14b8a6;--s:#2dd4bf;--ok:#10b981;--warn:#f59e0b;--err:#ef4444;--info:#06b6d4;--accent:#c9a84c;--dark:#0f172a;--sb:260px;--th:64px;--bg:#f0fdf4;--card:#fff;--border:#d1e7dd;--muted:#64748b}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--dark);min-height:100vh;display:flex}
.sb{width:var(--sb);min-height:100vh;background:linear-gradient(180deg,#0f3433 0%,#134e4a 100%);position:fixed;left:0;top:0;z-index:1000;display:flex;flex-direction:column;transition:transform .3s}
.sb-logo{padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:12px}
.sb-logo .sub{color:rgba(255,255,255,.4);font-size:11px;margin-top:1px}
.sb-nav{flex:1;padding:10px 0;overflow-y:auto}
.nav-lbl{color:rgba(255,255,255,.3);font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;padding:14px 22px 4px}
.nav-item a{display:flex;align-items:center;gap:11px;padding:10px 22px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13.5px;font-weight:500;transition:all .2s;position:relative}
.nav-item a:hover{color:#fff;background:rgba(255,255,255,.06)}
.nav-item a.active{color:#fff;background:rgba(45,212,191,.2)}
.nav-item a.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:#2dd4bf;border-radius:0 3px 3px 0}
.nav-item a i{font-size:16px;width:18px;flex-shrink:0}
.sb-foot{padding:14px 22px;border-top:1px solid rgba(255,255,255,.08)}
.upill{display:flex;align-items:center;gap:10px}
.uav{width:34px;height:34px;background:linear-gradient(135deg,var(--p),var(--s));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0}
.uname{color:#fff;font-size:13px;font-weight:600}.urole{color:rgba(255,255,255,.4);font-size:11px}
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{height:var(--th);background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 26px;gap:14px;position:sticky;top:0;z-index:100}
.topbar-title{font-size:17px;font-weight:700}.topbar-bc{font-size:12px;color:var(--muted)}.topbar-bc a{color:var(--p);text-decoration:none}
.spacer{flex:1}
.tbtn{width:36px;height:36px;border:none;background:var(--bg);border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--muted);position:relative;transition:background .2s}
.tbtn:hover{background:var(--border);color:var(--dark)}
.pc{padding:26px;flex:1}
.card{background:var(--card);border-radius:16px;border:1px solid var(--border);overflow:hidden}
.card-hdr{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:700}
.card-body{padding:22px}
.sc{background:var(--card);border-radius:14px;border:1px solid var(--border);padding:20px;display:flex;align-items:flex-start;gap:14px}
.si{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.si.p{background:rgba(15,118,110,.1);color:var(--p)}.si.ok{background:rgba(16,185,129,.1);color:var(--ok)}
.si.w{background:rgba(245,158,11,.1);color:var(--warn)}.si.e{background:rgba(239,68,68,.1);color:var(--err)}
.si.i{background:rgba(6,182,212,.1);color:var(--info)}.si.s{background:rgba(45,212,191,.1);color:var(--s)}
.sv{font-size:24px;font-weight:800;line-height:1.2}.sl{font-size:12px;color:var(--muted);margin-top:2px}
.dt{width:100%;border-collapse:collapse}
.dt th{padding:11px 15px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:#f0fdf4;border-bottom:1px solid var(--border)}
.dt td{padding:13px 15px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.dt tbody tr:hover{background:#f0fdf4}.dt tbody tr:last-child td{border-bottom:none}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600}
.bp{background:rgba(15,118,110,.1);color:var(--p)}.bok{background:rgba(16,185,129,.1);color:var(--ok)}
.bw{background:rgba(245,158,11,.15);color:var(--warn)}.be{background:rgba(239,68,68,.1);color:var(--err)}
.bi-badge{background:rgba(6,182,212,.1);color:var(--info)}.bs{background:rgba(100,116,139,.1);color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 17px;border-radius:10px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;white-space:nowrap}
.btn-sm{padding:5px 12px;font-size:12px;border-radius:8px}.btn-xs{padding:3px 8px;font-size:11px;border-radius:6px}
.btn-p{background:var(--p);color:#fff}.btn-p:hover{background:var(--pd);color:#fff}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#059669;color:#fff}
.btn-e{background:var(--err);color:#fff}.btn-e:hover{background:#dc2626;color:#fff}
.btn-w{background:var(--warn);color:#fff}.btn-w:hover{background:#d97706;color:#fff}
.btn-o{background:transparent;border:1.5px solid var(--border);color:var(--dark)}.btn-o:hover{border-color:var(--p);color:var(--p)}
.fg{margin-bottom:18px}.fl{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
.fc{width:100%;padding:9px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;font-family:'Inter',sans-serif;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s}
.fc:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(15,118,110,.1)}
select.fc{cursor:pointer}.fc.err{border-color:var(--err)}
.ft{font-size:12px;color:var(--muted);margin-top:3px}.iv{font-size:12px;color:var(--err);margin-top:3px;display:block}
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:flex-start;gap:9px;margin-bottom:18px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
.a-w{background:rgba(245,158,11,.08);color:#92400e;border:1px solid rgba(245,158,11,.2)}
.a-i{background:rgba(6,182,212,.08);color:#0c4a6e;border:1px solid rgba(6,182,212,.2)}
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.agent-badge{background:rgba(45,212,191,.12);color:#0f766e;border:1px solid rgba(45,212,191,.3);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.how-it-works{background:linear-gradient(135deg,#f0fdf4,#ccfbf1);border:1px solid #99f6e4;border-radius:16px;padding:22px;margin-bottom:22px}
.hiw-step{display:flex;gap:14px;align-items:flex-start;margin-bottom:14px}
.hiw-step:last-child{margin-bottom:0}
.hiw-num{width:32px;height:32px;background:var(--p);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0}
.hiw-text strong{font-size:13px;display:block;margin-bottom:2px}.hiw-text span{font-size:12px;color:var(--muted)}
/* Responsive: hide sidebar on mobile, overlay */
@media(max-width:768px){
  .sb{transform:translateX(-100%);box-shadow:none}
  .sb.open{transform:translateX(0);box-shadow:10px 0 30px rgba(0,0,0,.3)}
  .main{margin-left:0}
  .g2,.g3,.g4{grid-template-columns:1fr}
}
</style>
@stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sb" id="sb">
  <div class="sb-logo">
    <img style="height:36px;width:auto;object-fit:contain" src="{{ \Illuminate\Support\Facades\Storage::url(\App\Models\SystemSetting::get('app_logo')) }}" alt="Logo" onerror="this.src='https://ui-avatars.com/api/?name=Agent&bg=0f766e&color=fff'">
    <div>
      <div style="color:#fff;font-size:14px;font-weight:800;line-height:1.2;letter-spacing:-.02em">{{ \App\Models\SystemSetting::get('app_name', 'MyLoan') }}</div>
      <div class="sub">Agent Portal</div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="nav-lbl">Main</div>
    <div class="nav-item">
      <a href="{{ route('agent.dashboard') }}" class="{{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </div>

    <div class="nav-lbl">Applications</div>
    <div class="nav-item">
      <a href="{{ route('agent.applications.create') }}" class="{{ request()->routeIs('agent.applications.create') ? 'active' : '' }}">
        <i class="bi bi-plus-circle-fill"></i> New Application
      </a>
    </div>
    <div class="nav-item">
      <a href="{{ route('agent.applications.index') }}" class="{{ request()->routeIs('agent.applications.index', 'agent.applications.show') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> My Applications
      </a>
    </div>

    <div class="nav-lbl">Earnings</div>
    <div class="nav-item">
      <a href="{{ route('agent.commissions.index') }}" class="{{ request()->routeIs('agent.commissions.*') ? 'active' : '' }}">
        <i class="bi bi-wallet2"></i> Commission Tracker
      </a>
    </div>

    <div class="nav-lbl">Account</div>
    <div class="nav-item">
      <a href="{{ route('agent.profile.index') }}" class="{{ request()->routeIs('agent.profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> My Profile
      </a>
    </div>
  </nav>

  <div class="sb-foot">
    <a href="{{ route('agent.profile.index') }}" style="text-decoration:none;display:block">
      <div class="upill" style="cursor:pointer;transition:background .2s;border-radius:10px;padding:6px 4px" onmouseover="this.style.background='rgba(45,212,191,.12)'" onmouseout="this.style.background=''">
        @php $authUser = auth('agent')->user(); $agentProfile = $authUser->agentProfile; @endphp
        @if($authUser->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($authUser->profile_photo))
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($authUser->profile_photo) }}" alt="{{ $authUser->name }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(45,212,191,.4)">
        @else
        <div class="uav">{{ strtoupper(substr($authUser->name??'A',0,1)) }}</div>
        @endif
        <div>
          <div class="uname">{{ $authUser->name ?? 'Agent' }}</div>
          <div class="urole">{{ $agentProfile->agent_id ?? 'Agent' }}</div>
        </div>
      </div>
    </a>
  </div>
</aside>

{{-- MAIN --}}
<div class="main">
  <header class="topbar">
    <button class="tbtn" id="sbToggle"><i class="bi bi-list"></i></button>
    <div>
      <div class="topbar-title">@yield('page-title','Dashboard')</div>
    </div>
    <div class="spacer"></div>
    <div style="display:flex;align-items:center;gap:8px">
      <span class="agent-badge"><i class="bi bi-shop me-1"></i>Agent</span>
      <button type="button" class="tbtn" title="Logout" onclick="document.getElementById('logoutModal').classList.add('open')">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </div>
  </header>

  <div style="padding:0 26px;margin-top:14px">
    @if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
    @if(session('info'))<div class="alert a-i"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>@endif
    @if($errors->any())<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i><ul style="margin:0;padding-left:14px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  </div>

  <main class="pc">@yield('content')</main>
</div>

{{-- LOGOUT MODAL --}}
<div id="logoutModal" style="position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
  <div style="background:#fff;border-radius:18px;width:100%;max-width:400px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2);overflow:hidden">
    <div style="padding:24px 26px;text-align:center">
      <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#ef4444"><i class="bi bi-box-arrow-right"></i></div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px">Sign out?</div>
      <div style="font-size:13px;color:#64748b;margin-bottom:24px">Are you sure you want to log out of the Agent Portal?</div>
      <div style="display:flex;gap:10px;justify-content:center">
        <button type="button" onclick="document.getElementById('logoutModal').style.display='none'" class="btn btn-o" style="flex:1">Cancel</button>
        <form method="POST" action="{{ route('agent.logout') }}" style="flex:1">
          @csrf
          <button type="submit" class="btn btn-e" style="width:100%;justify-content:center"><i class="bi bi-box-arrow-right"></i> Sign Out</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const sbToggle=document.getElementById('sbToggle'),sb=document.getElementById('sb');
sbToggle.addEventListener('click',()=>sb.classList.toggle('open'));
setTimeout(()=>document.querySelectorAll('.alert').forEach(el=>{el.style.transition='opacity .5s';el.style.opacity='0';setTimeout(()=>el.remove(),500)}),4500);
// Logout modal open helper
document.getElementById('logoutModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none'});
</script>
@stack('scripts')
</body>
</html>
