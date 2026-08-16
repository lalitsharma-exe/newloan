<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name') }}</title>
<link rel="shortcut icon" href="https://ik.imagekit.io/ygydr1m84/2699f0f4-26da-41ec-92ff-ce4aa8ac0f79.jpeg" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ═══════════════════════════════════════════════════
   PROSPERITY LOANS — OFFICER DESIGN SYSTEM
   Brand: Forest Green · Royal Blue · Gold
═══════════════════════════════════════════════════ */
:root{--green:#1a6b3c;--greend:#0f4527;--greenl:#22894e;--green2:#2eaa62;--blue:#1a3a8f;--bluel:#2b55c9;--gold:#c9943a;--goldl:#e0a843;--ok:#16a34a;--warn:#d97706;--err:#dc2626;--info:#0284c7;--bg:#f4f7f4;--card:#ffffff;--border:#d4e0d4;--muted:#5a6e5a;--dark:#0f2a1a;--sb:265px;--th:64px;--p:var(--green);--pd:var(--greend);--pl:var(--greenl);--s:var(--gold)}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--dark);min-height:100vh;display:flex}
.sb{width:var(--sb);min-height:100vh;background:var(--greend);position:fixed;left:0;top:0;z-index:1000;display:flex;flex-direction:column;box-shadow:4px 0 20px rgba(15,69,39,.25)}
.sb-logo{padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:12px}
.sb-logo .sub{color:rgba(255,255,255,.45);font-size:10.5px;margin-top:1px;letter-spacing:.05em;text-transform:uppercase}
.sb-nav{flex:1;padding:10px 0;overflow-y:auto}
.sb-nav::-webkit-scrollbar{width:3px}.sb-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:2px}
.nav-lbl{color:rgba(255,255,255,.28);font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.14em;padding:16px 22px 5px}
.nav-item a{display:flex;align-items:center;gap:11px;padding:9.5px 22px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;font-weight:500;transition:all .18s;position:relative}
.nav-item a:hover{color:#fff;background:rgba(255,255,255,.07)}
.nav-item a.active{color:#fff;background:rgba(201,148,58,.15)}
.nav-item a.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gold);border-radius:0 3px 3px 0}
.nav-item a i{font-size:15px;width:18px;flex-shrink:0}
.sb-foot{padding:14px 22px;border-top:1px solid rgba(255,255,255,.08)}
.upill{display:flex;align-items:center;gap:10px}
.uav{width:34px;height:34px;background:linear-gradient(135deg,var(--green),var(--green2));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0}
.uname{color:#fff;font-size:13px;font-weight:600}.urole{color:rgba(255,255,255,.4);font-size:10.5px}
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{height:var(--th);background:#fff;border-bottom:2px solid var(--border);display:flex;align-items:center;padding:0 26px;gap:14px;position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(15,69,39,.06)}
.topbar-title{font-size:16px;font-weight:700;color:var(--dark)}.topbar-bc{font-size:12px;color:var(--muted)}.topbar-bc a{color:var(--green);text-decoration:none;font-weight:500}
.spacer{flex:1}
.tbtn{width:36px;height:36px;border:none;background:var(--bg);border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--muted);position:relative;transition:background .2s}
.tbtn:hover{background:var(--border);color:var(--dark)}
.ndot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--err);border-radius:50%;border:2px solid #fff}
.pc{padding:26px;flex:1}
.card{background:var(--card);border-radius:14px;border:1px solid var(--border);overflow:hidden;box-shadow:0 1px 4px rgba(15,69,39,.06)}
.card-hdr{padding:15px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#fafcfa}
.card-title{font-size:14px;font-weight:700;color:var(--dark)}
.card-body{padding:22px}
.sc{background:var(--card);border-radius:14px;border:1px solid var(--border);padding:20px;display:flex;align-items:flex-start;gap:14px;box-shadow:0 1px 4px rgba(15,69,39,.06)}
.si{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.si.p{background:rgba(26,107,60,.1);color:var(--green)}.si.ok{background:rgba(22,163,74,.1);color:var(--ok)}
.si.w{background:rgba(217,119,6,.1);color:var(--warn)}.si.e{background:rgba(220,38,38,.1);color:var(--err)}
.si.i{background:rgba(2,132,199,.1);color:var(--info)}.si.s{background:rgba(201,148,58,.12);color:var(--gold)}
.sv{font-size:24px;font-weight:800;line-height:1.2;color:var(--dark)}.sl{font-size:12px;color:var(--muted);margin-top:2px}
.dt{width:100%;border-collapse:collapse}
.dt th{padding:11px 15px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);background:#f6faf6;border-bottom:1px solid var(--border)}
.dt td{padding:13px 15px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.dt tbody tr:hover{background:#f6faf6}.dt tbody tr:last-child td{border-bottom:none}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.bp{background:rgba(26,107,60,.1);color:var(--green)}.bok{background:rgba(22,163,74,.1);color:var(--ok)}
.bw{background:rgba(217,119,6,.12);color:var(--warn)}.be{background:rgba(220,38,38,.1);color:var(--err)}
.bi{background:rgba(2,132,199,.1);color:var(--info)}.bs{background:rgba(90,110,90,.1);color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;white-space:nowrap;font-family:'DM Sans',sans-serif}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:7px}.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}
.btn-p{background:var(--green);color:#fff}.btn-p:hover{background:var(--greend);color:#fff;transform:translateY(-1px);box-shadow:0 4px 14px rgba(15,69,39,.25)}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#15803d;color:#fff}
.btn-e{background:var(--err);color:#fff}.btn-e:hover{background:#b91c1c;color:#fff}
.btn-w{background:var(--warn);color:#fff}.btn-w:hover{background:#b45309;color:#fff}
.btn-i{background:var(--info);color:#fff}.btn-i:hover{background:#0369a1;color:#fff}
.btn-o{background:transparent;border:1.5px solid var(--border);color:var(--dark)}.btn-o:hover{border-color:var(--green);color:var(--green)}
.fg{margin-bottom:18px}.fl{display:block;font-size:11.5px;font-weight:700;margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)}
.fc{width:100%;padding:9px 13px;border:1.5px solid var(--border);border-radius:8px;font-size:13.5px;font-family:'DM Sans',sans-serif;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;color:var(--dark)}
.fc:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(26,107,60,.1)}
select.fc{cursor:pointer}.fc.err{border-color:var(--err)}
.ft{font-size:12px;color:var(--muted);margin-top:3px}.iv{font-size:12px;color:var(--err);margin-top:3px;display:block}
.alert{padding:12px 16px;border-radius:10px;font-size:13px;display:flex;align-items:flex-start;gap:9px;margin-bottom:18px}
.a-ok{background:rgba(22,163,74,.08);color:#15803d;border:1px solid rgba(22,163,74,.2)}
.a-e{background:rgba(220,38,38,.07);color:#991b1b;border:1px solid rgba(220,38,38,.2)}
.a-w{background:rgba(217,119,6,.08);color:#92400e;border:1px solid rgba(217,119,6,.2)}
.a-i{background:rgba(2,132,199,.08);color:#0c4a6e;border:1px solid rgba(2,132,199,.2)}
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.mo{position:fixed;inset:0;background:rgba(10,30,18,.55);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.mo.open{display:flex}
.mb{background:#fff;border-radius:16px;width:100%;max-width:520px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:mIn .22s ease}
.mh{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.mt{font-size:15px;font-weight:700}.mc{background:none;border:none;cursor:pointer;font-size:22px;color:var(--muted);width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px}
.mc:hover{background:var(--bg)}.mbody{padding:22px}
.mf{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:9px}
@keyframes mIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.flex{display:flex}.aic{align-items:center}.jb{justify-content:space-between}
.gap2{gap:8px}.gap3{gap:12px}.mb4{margin-bottom:16px}.mb6{margin-bottom:24px}.mt4{margin-top:16px}
.tabs{display:flex;gap:4px;border-bottom:2px solid var(--border);margin-bottom:20px;overflow-x:auto}
.tab{padding:9px 16px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:500;color:var(--muted);border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;border-radius:8px 8px 0 0;transition:all .2s;display:flex;align-items:center;gap:6px;font-family:'DM Sans',sans-serif}
.tab:hover{color:var(--dark);background:var(--bg)}
.tab.active{color:var(--green);border-bottom-color:var(--green);font-weight:700;background:rgba(26,107,60,.04)}
.tpanel{display:none}.tpanel.active{display:block}
.info-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.info-lbl{font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.info-val{font-size:13.5px;font-weight:500;color:var(--dark)}
.officer-badge{background:rgba(26,107,60,.12);color:var(--green);border:1px solid rgba(26,107,60,.3);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
@media(max-width:768px){.sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}.main{margin-left:0}.g2,.g3,.g4{grid-template-columns:1fr}}
</style>
@stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sb" id="sb">
  <div class="sb-logo">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:40px;height:40px;background:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.3);overflow:hidden">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="height:36px;width:36px;object-fit:contain">
      </div>
      <div>
        <div style="color:#fff;font-size:13.5px;font-weight:800;line-height:1.2;font-family:'Playfair Display',serif">{{ \App\Models\SystemSetting::get('app_name', config('app.name')) }}</div>
        <div class="sub">Loan Officer Portal</div>
      </div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="nav-lbl">Main</div>
    <div class="nav-item">
      <a href="{{ route('officer.dashboard') }}" class="{{ request()->routeIs('officer.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </div>

    <div class="nav-lbl">Applications</div>
    <div class="nav-item">
      <a href="{{ route('officer.applications.assigned') }}" class="{{ request()->routeIs('officer.applications.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-person"></i> My Applications
        @php try { $pendingCount = \App\Models\LoanApplication::where('assigned_officer_id', auth('officer')->id())->whereIn('status',['submitted','info_requested'])->count(); } catch(\Exception $e){ $pendingCount = 0; } @endphp
        @if($pendingCount > 0)
        <span style="margin-left:auto;background:var(--err);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px">{{ $pendingCount }}</span>
        @endif
      </a>
    </div>
    <div class="nav-item">
      <a href="{{ route('officer.applications.all') }}" class="">
        <i class="bi bi-collection"></i> All Applications
      </a>
    </div>

    <div class="nav-lbl">Clients</div>
    <div class="nav-item">
      <a href="{{ route('officer.walk-in.create') }}" class="{{ request()->routeIs('officer.walk-in.*') ? 'active' : '' }}">
        <i class="bi bi-person-plus-fill"></i> Register Walk-in
      </a>
    </div>
    <div class="nav-item">
      <a href="{{ route('officer.clients.index') }}" class="{{ request()->routeIs('officer.clients.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> My Clients
      </a>
    </div>

    <div class="nav-lbl">Documents</div>
    <div class="nav-item">
      <a href="{{ route('officer.documents.index') }}" class="{{ request()->routeIs('officer.documents.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-check"></i> Verification Queue
        @php try { $docsCount = \App\Models\Document::whereHas('application', fn($q)=>$q->where('assigned_officer_id', auth('officer')->id()))->where('status','pending')->count(); } catch(\Exception $e){ $docsCount = 0; } @endphp
        @if($docsCount > 0)
        <span style="margin-left:auto;background:var(--warn);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px">{{ $docsCount }}</span>
        @endif
      </a>
    </div>

    <div class="nav-lbl">Account</div>
    <div class="nav-item">
      <a href="{{ route('officer.notifications.index') }}" class="{{ request()->routeIs('officer.notifications.*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> Notifications
      </a>
    </div>
    <div class="nav-item">
      <a href="{{ route('officer.profile.index') }}" class="{{ request()->routeIs('officer.profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> My Profile
      </a>
    </div>
  </nav>

  <div class="sb-foot">
    <a href="{{ route('officer.profile.index') }}" style="text-decoration:none;display:block">
      <div class="upill" style="cursor:pointer;transition:background .2s;border-radius:10px;padding:6px 4px" onmouseover="this.style.background='rgba(201,148,58,.12)'" onmouseout="this.style.background=''">
        @php $authUser = auth('officer')->user(); @endphp
        @if($authUser->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($authUser->profile_photo))
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($authUser->profile_photo) }}" alt="{{ $authUser->name }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(201,148,58,.5)">
        @else
        <div class="uav">{{ strtoupper(substr($authUser->name??'O',0,1)) }}</div>
        @endif
        <div>
          <div class="uname">{{ $authUser->name ?? 'Officer' }}</div>
          <div class="urole">Loan Officer</div>
        </div>
        <i class="bi bi-chevron-right" style="margin-left:auto;font-size:11px;color:rgba(255,255,255,.3)"></i>
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
      @hasSection('bc')
      <div class="topbar-bc" id="topbarBc">{!! $__env->yieldContent('bc') !!}</div>
      <script>
        (function(){
          var bc = document.getElementById('topbarBc');
          if(!bc) return;
          bc.innerHTML = bc.innerHTML.replace(/\s+\/\s+/g,
            '<i class="bi bi-chevron-right" style="font-size:10px;color:#b0c4b0;margin:0 4px;vertical-align:middle"></i>');
        })();
      </script>
      @endif
    </div>
    <div class="spacer"></div>
    <div class="flex aic gap2">
      @php try { $bellUnread = \App\Models\Notification::where('user_id', auth('officer')->id())->where('is_read',false)->count(); } catch(\Exception $e) { $bellUnread = 0; } @endphp
      <a href="{{ route('officer.notifications.index') }}" class="tbtn" style="text-decoration:none;position:relative">
        <i class="bi bi-bell{{ $bellUnread > 0 ? '-fill' : '' }}" style="{{ $bellUnread > 0 ? 'color:var(--green)' : '' }}"></i>
        @if($bellUnread > 0)<span class="ndot"></span>@endif
      </a>
      <span class="officer-badge"><i class="bi bi-person-badge me-1"></i>Officer</span>
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

{{-- SESSION IDLE MODAL --}}
<div id="idleModal" class="mo">
  <div class="mb" style="max-width:400px;text-align:center;padding:26px">
    <div style="width:56px;height:56px;background:rgba(201,148,58,.12);color:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px"><i class="bi bi-clock-history"></i></div>
    <div style="font-size:18px;font-weight:700;margin-bottom:8px;font-family:'Playfair Display',serif;color:var(--dark)">Session Expiring Soon</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:24px">You have been inactive for a while. For your security, you will be logged out in <strong id="idleCounter" style="color:var(--err)">60</strong> seconds.</div>
    <button type="button" onclick="stayLoggedIn()" class="btn btn-p" style="width:100%;justify-content:center">Stay Logged In</button>
  </div>
</div>

{{-- LOGOUT MODAL --}}
<div id="logoutModal" class="mo">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2);overflow:hidden">
    <div style="padding:24px 26px;text-align:center">
      <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#dc2626"><i class="bi bi-box-arrow-right"></i></div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;font-family:'Playfair Display',serif;color:var(--dark)">Sign out?</div>
      <div style="font-size:13px;color:#64748b;margin-bottom:24px">Are you sure you want to log out of the Officer Portal?</div>
      <div style="display:flex;gap:10px;justify-content:center">
        <button type="button" onclick="document.getElementById('logoutModal').classList.remove('open')" class="btn btn-o" style="flex:1">Cancel</button>
        <form method="POST" action="{{ route('officer.logout') }}" style="flex:1">
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
function openModal(id){document.getElementById(id).classList.add('open')}
function switchTab(g,id){document.querySelectorAll('[data-tg="'+g+'"]').forEach(e=>e.classList.remove('active'));document.querySelectorAll('[data-pg="'+g+'"]').forEach(e=>e.classList.remove('active'));document.querySelector('[data-tg="'+g+'"][data-t="'+id+'"]').classList.add('active');document.querySelector('[data-pg="'+g+'"][data-p="'+id+'"]').classList.add('active')}

(function(){
  const IDLE_MS=30*60*1000,WARN_SECS=60;
  const CSRF=document.querySelector('meta[name="csrf-token"]')?.content||'';
  const LOGOUT_URL="{{ route('officer.logout') }}";
  let idleTimer,countdownTimer,warnActive=false,secs=WARN_SECS;
  const modal=document.getElementById('idleModal'),counter=document.getElementById('idleCounter');
  function doLogout(){const f=document.createElement('form');f.method='POST';f.action=LOGOUT_URL;const t=document.createElement('input');t.type='hidden';t.name='_token';t.value=CSRF;f.appendChild(t);document.body.appendChild(f);f.submit()}
  function showWarning(){warnActive=true;secs=WARN_SECS;counter.textContent=secs;modal.classList.add('open');countdownTimer=setInterval(()=>{secs--;counter.textContent=secs;if(secs<=0){clearInterval(countdownTimer);doLogout()}},1000)}
  window.stayLoggedIn=function(){clearInterval(countdownTimer);modal.classList.remove('open');warnActive=false;resetIdle()};
  function resetIdle(){if(warnActive)return;clearTimeout(idleTimer);idleTimer=setTimeout(showWarning,IDLE_MS)}
  ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(ev=>document.addEventListener(ev,resetIdle,{passive:true}));
  resetIdle();
})();
</script>
@stack('scripts')
</body>
</html>
