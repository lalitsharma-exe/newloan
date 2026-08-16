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
   PROSPERITY LOANS — ADMIN DESIGN SYSTEM
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
  --info:    #0284c7;
  --bg:      #f4f7f4;
  --card:    #ffffff;
  --border:  #d4e0d4;
  --muted:   #5a6e5a;
  --dark:    #0f2a1a;
  --sb:      265px;
  --th:      64px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--dark);min-height:100vh;display:flex}

/* ── SIDEBAR ─────────────────────────────────────── */
.sb{width:var(--sb);height:100vh;background:var(--greend);position:fixed;left:0;top:0;z-index:1000;display:flex;flex-direction:column;box-shadow:4px 0 20px rgba(15,69,39,.25)}
.sb-logo{padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:12px}
.sb-logo img{height:38px;width:auto;object-fit:contain}
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

/* ── MAIN ─────────────────────────────────────────── */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-height:100vh}

/* ── TOPBAR ───────────────────────────────────────── */
.topbar{height:var(--th);background:#fff;border-bottom:2px solid var(--border);display:flex;align-items:center;padding:0 26px;gap:14px;position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(15,69,39,.06)}
.topbar-title{font-size:16px;font-weight:700;color:var(--dark)}.topbar-bc{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px;flex-wrap:wrap}.topbar-bc a{color:var(--green);text-decoration:none;font-weight:500}.topbar-bc a:hover{text-decoration:underline}.topbar-bc .bc-sep{color:#b0c4b0;margin:0 2px}
.spacer{flex:1}
.tbtn{width:36px;height:36px;border:none;background:var(--bg);border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--muted);position:relative;transition:background .2s}
.tbtn:hover{background:var(--border);color:var(--dark)}
.ndot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--err);border-radius:50%;border:2px solid #fff}

/* ── CONTENT ──────────────────────────────────────── */
.pc{padding:26px;flex:1}

/* ── CARD ─────────────────────────────────────────── */
.card{background:var(--card);border-radius:14px;border:1px solid var(--border);overflow:hidden;box-shadow:0 1px 4px rgba(15,69,39,.06)}
.card-hdr{padding:15px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#fafcfa}
.card-title{font-size:14px;font-weight:700;color:var(--dark)}
.card-body{padding:22px}

/* ── STAT CARDS ───────────────────────────────────── */
.sc{background:var(--card);border-radius:14px;border:1px solid var(--border);padding:20px;display:flex;align-items:flex-start;gap:14px;box-shadow:0 1px 4px rgba(15,69,39,.06)}
.si{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.si.p{background:rgba(26,107,60,.1);color:var(--green)}.si.ok{background:rgba(22,163,74,.1);color:var(--ok)}
.si.w{background:rgba(217,119,6,.1);color:var(--warn)}.si.e{background:rgba(220,38,38,.1);color:var(--err)}
.si.i{background:rgba(2,132,199,.1);color:var(--info)}.si.s{background:rgba(201,148,58,.12);color:var(--gold)}
.sv{font-size:24px;font-weight:800;line-height:1.2;color:var(--dark)}.sl{font-size:12px;color:var(--muted);margin-top:2px}

/* ── TABLE ────────────────────────────────────────── */
.dt{width:100%;border-collapse:collapse}
.dt th{padding:11px 15px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);background:#f6faf6;border-bottom:1px solid var(--border)}
.dt td{padding:13px 15px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.dt tbody tr:hover{background:#f6faf6}.dt tbody tr:last-child td{border-bottom:none}

/* ── BADGE ────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.02em}
.bp{background:rgba(26,107,60,.1);color:var(--green)}.bok{background:rgba(22,163,74,.1);color:var(--ok)}
.bw{background:rgba(217,119,6,.12);color:var(--warn)}.be{background:rgba(220,38,38,.1);color:var(--err)}
.bi{background:rgba(2,132,199,.1);color:var(--info)}.bs{background:rgba(90,110,90,.1);color:var(--muted)}
.ba{background:rgba(201,148,58,.12);color:var(--gold)}

/* ── BUTTON ───────────────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;white-space:nowrap;font-family:'DM Sans',sans-serif}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:7px}.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}
.btn-p{background:var(--green);color:#fff}.btn-p:hover{background:var(--greend);color:#fff;transform:translateY(-1px);box-shadow:0 4px 14px rgba(15,69,39,.25)}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#15803d;color:#fff}
.btn-e{background:var(--err);color:#fff}.btn-e:hover{background:#b91c1c;color:#fff}
.btn-w{background:var(--warn);color:#fff}.btn-w:hover{background:#b45309;color:#fff}
.btn-i{background:var(--info);color:#fff}.btn-i:hover{background:#0369a1;color:#fff}
.btn-gold{background:var(--gold);color:#fff}.btn-gold:hover{background:var(--goldl);color:#fff}
.btn-o{background:transparent;border:1.5px solid var(--border);color:var(--dark)}.btn-o:hover{border-color:var(--green);color:var(--green)}

/* ── FORM ─────────────────────────────────────────── */
.fg{margin-bottom:18px}.fl{display:block;font-size:11.5px;font-weight:700;margin-bottom:5px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.fc{width:100%;padding:9px 13px;border:1.5px solid var(--border);border-radius:8px;font-size:13.5px;font-family:'DM Sans',sans-serif;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s;color:var(--dark)}
.fc:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(26,107,60,.1)}
select.fc{cursor:pointer}.fc.err{border-color:var(--err)}
.ft{font-size:12px;color:var(--muted);margin-top:3px}.iv{font-size:12px;color:var(--err);margin-top:3px;display:block}

/* ── ALERT ────────────────────────────────────────── */
.alert{padding:12px 16px;border-radius:10px;font-size:13px;display:flex;align-items:flex-start;gap:9px;margin-bottom:18px}
.a-ok{background:rgba(22,163,74,.08);color:#15803d;border:1px solid rgba(22,163,74,.2)}
.a-e{background:rgba(220,38,38,.07);color:#991b1b;border:1px solid rgba(220,38,38,.2)}
.a-w{background:rgba(217,119,6,.08);color:#92400e;border:1px solid rgba(217,119,6,.2)}
.a-i{background:rgba(2,132,199,.08);color:#0c4a6e;border:1px solid rgba(2,132,199,.2)}

/* ── GRID ─────────────────────────────────────────── */
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}

/* ── MODAL ────────────────────────────────────────── */
.mo{position:fixed;inset:0;background:rgba(10,30,18,.55);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.mo.open{display:flex}
.mb{background:#fff;border-radius:16px;width:100%;max-width:520px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:mIn .22s ease}
.mh{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.mt{font-size:15px;font-weight:700}
.mc{background:none;border:none;cursor:pointer;font-size:22px;color:var(--muted);width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:8px}
.mc:hover{background:var(--bg)}.mbody{padding:22px}
.mf{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:9px}
@keyframes mIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}

/* ── MISC ─────────────────────────────────────────── */
.flex{display:flex}.aic{align-items:center}.jb{justify-content:space-between}
.gap2{gap:8px}.gap3{gap:12px}.mb4{margin-bottom:16px}.mb6{margin-bottom:24px}.mt4{margin-top:16px}

/* ── PAGINATION ───────────────────────────────────── */
.pagination{display:flex;list-style:none;gap:6px;margin:0;padding:0;align-items:center}
.page-item .page-link{display:flex;align-items:center;justify-content:center;height:32px;min-width:32px;padding:0 10px;border-radius:7px;background:var(--card);border:1px solid var(--border);color:var(--dark);font-size:12.5px;font-weight:600;text-decoration:none;transition:all .2s}
.page-item .page-link:hover{background:var(--bg);border-color:#a0c0a0}
.page-item.active .page-link{background:var(--green);color:#fff;border-color:var(--green)}
.page-item.disabled .page-link{opacity:.4;cursor:not-allowed;pointer-events:none;background:var(--bg)}

/* ── UTILS ────────────────────────────────────────── */
.tc{text-align:center}.tr{text-align:right}.muted{color:var(--muted);font-size:12.5px}
.av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0}
.av-sm{width:28px;height:28px;font-size:11px}.av-lg{width:52px;height:52px;font-size:18px}
.empty{text-align:center;padding:50px 20px;color:var(--muted)}.empty i{font-size:44px;opacity:.35;display:block;margin-bottom:10px}
.filter-bar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:18px;padding:16px;background:#fff;border-radius:12px;border:1px solid var(--border)}
.filter-bar .fg{margin-bottom:0;min-width:140px}
.tabs{display:flex;gap:3px;border-bottom:2px solid var(--border);margin-bottom:22px}
.tab{padding:9px 16px;border:none;background:none;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;font-family:'DM Sans',sans-serif}
.tab:hover{color:var(--green)}.tab.active{color:var(--green);border-bottom-color:var(--green)}
.tpanel{display:none}.tpanel.active{display:block}
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px}
.info-lbl{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
.info-val{font-size:13.5px;font-weight:600;margin-top:2px;color:var(--dark)}
::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
@media(max-width:1024px){.g4{grid-template-columns:repeat(2,1fr)}.g3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){.sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}.main{margin-left:0}.g4,.g3,.g2{grid-template-columns:1fr}}

/* ── LOGOUT MODAL ─────────────────────────────────── */
#logoutModal{display:none;position:fixed;inset:0;background:rgba(10,30,18,.55);z-index:3000;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
#logoutModal.open{display:flex}
</style>
@stack('styles')
</head>
<body>
<aside class="sb" id="sb">
  <div class="sb-logo">
    {{-- Prosperity Loans Logo --}}
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:40px;height:40px;background:linear-gradient(135deg,#1a6b3c,#22894e);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.3)">
        <svg width="22" height="22" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg">
          <rect x="3" y="14" width="3" height="5" rx="1" fill="#fff" opacity=".85"/>
          <rect x="8" y="9" width="3" height="10" rx="1" fill="#fff"/>
          <rect x="13" y="5" width="3" height="14" rx="1" fill="#c9943a"/>
          <rect x="18" y="3" width="2" height="16" rx="1" fill="#e0a843" opacity=".9"/>
          <polyline points="3,14 8,9 13,5 18,3" fill="none" stroke="rgba(255,255,255,.4)" stroke-width="1.1"/>
        </svg>
      </div>
      <div>
        <div style="font-size:14px;font-weight:800;color:#fff;letter-spacing:.2px;line-height:1.1;font-family:'Playfair Display',serif">{{ config('app.name') }}</div>
        <div class="sub">Admin Portal</div>
      </div>
    </div>
  </div>
  <nav class="sb-nav">
    @php $u = auth('admin')->user(); @endphp
    <div class="nav-lbl">Overview</div>
    @if($u->hasAdminPermission('dashboard'))
    <div class="nav-item"><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'active':'' }}"><i class="bi bi-grid-fill"></i> Dashboard</a></div>
    @endif
    <div class="nav-lbl">Lending</div>
    @if($u->hasAdminPermission('applications.view'))
    <div class="nav-item"><a href="{{ route('admin.applications.index') }}" class="{{ request()->routeIs('admin.applications.*')?'active':'' }}"><i class="bi bi-file-earmark-text-fill"></i> Applications</a></div>
    @endif
    @if($u->hasAdminPermission('loans.view'))
    <div class="nav-item"><a href="{{ route('admin.loans.index') }}" class="{{ request()->routeIs('admin.loans.*')?'active':'' }}"><i class="bi bi-bank2"></i> Loan Management</a></div>
    @endif
    @if($u->hasAdminPermission('payments.view'))
    <div class="nav-item"><a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*')?'active':'' }}"><i class="bi bi-credit-card-fill"></i> Payments</a></div>
    @endif
    @if($u->hasAdminPermission('financial.intelligence'))
    <div class="nav-item">
      <a href="{{ route('admin.financial.dashboard') }}" class="{{ request()->routeIs('admin.financial.dashboard')?'active':'' }}">
        <i class="bi bi-bank"></i> Financial Intelligence
        <span class="badge ba" style="font-size:8px;margin-left:auto">Phase 2</span>
      </a>
    </div>
    @endif
    @if($u->hasAdminPermission('financial.transfers'))
    <div class="nav-item">
      <a href="{{ route('admin.financial.transfers') }}" class="{{ request()->routeIs('admin.financial.transfers')?'active':'' }}">
        <i class="bi bi-arrow-left-right"></i> Internal Transfers
      </a>
    </div>
    @endif
    @if($u->hasAdminPermission('investors.partners'))
    <div class="nav-item">
      <a href="{{ route('admin.investments.investors.index') }}" class="{{ request()->routeIs('admin.investments.investors.*')?'active':'' }}">
        <i class="bi bi-people-fill"></i> Investor Partners
      </a>
    </div>
    @endif
    @if($u->hasAdminPermission('investments.tranches'))
    <div class="nav-item">
      <a href="{{ route('admin.investments.index') }}" class="{{ request()->routeIs('admin.investments.index') || request()->routeIs('admin.investments.show') ?'active':'' }}">
        <i class="bi bi-wallet2"></i> Investment Tranches
      </a>
    </div>
    @endif
    @if($u->hasAdminPermission('credit_bureau'))
    <div class="nav-item"><a href="{{ route('admin.credit.index') }}" class="{{ request()->routeIs('admin.credit.*')?'active':'' }}"><i class="bi bi-database-fill-check"></i> Credit Bureau</a></div>
    @endif
    @if($u->hasAdminPermission('compuscan'))
    <div class="nav-item"><a href="{{ route('admin.compuscan.index') }}" class="{{ request()->routeIs('admin.compuscan.*')?'active':'' }}"><i class="bi bi-cloud-arrow-up-fill"></i> Compuscan (CCI)</a></div>
    @endif
    @if($u->hasAdminPermission('mybill') || $u->hasAdminPermission('myfloat'))
    <div class="nav-lbl">Bill Payments</div>
    @if($u->hasAdminPermission('mybill'))
    <div class="nav-item"><a href="{{ route('admin.mybill.dashboard') }}" class="{{ request()->routeIs('admin.mybill.*')?'active':'' }}"><i class="bi bi-lightning-charge-fill"></i> MyBill</a></div>
    @endif
    @if($u->hasAdminPermission('myfloat'))
    <div class="nav-item">
      <a href="{{ route('admin.float.index') }}" class="{{ request()->routeIs('admin.float.*')?'active':'' }}">
        <i class="bi bi-lightning-fill" style="color:#e0a843"></i> MyFloat
        <span class="badge" style="font-size:8px;margin-left:auto;background:var(--gold);color:#fff">HOT</span>
      </a>
    </div>
    @endif
    @endif
    @if($u->hasAdminPermission('reports') || $u->hasAdminPermission('reports.three_tier') || $u->hasAdminPermission('cbl.complaints'))
    <div class="nav-lbl">Reports</div>
    @if($u->hasAdminPermission('reports'))
    <div class="nav-item"><a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.index')?'active':'' }}"><i class="bi bi-bar-chart-fill"></i> Standard Reports</a></div>
    @endif
    @if($u->hasAdminPermission('reports.three_tier'))
    <div class="nav-item">
      <a href="{{ route('admin.reports.financial-dashboard') }}" class="{{ request()->routeIs('admin.reports.financial-dashboard')?'active':'' }}">
        <i class="bi bi-pie-chart-fill" style="color:var(--info)"></i> 3-Tier Financials
      </a>
    </div>
    @endif
    @if($u->hasAdminPermission('cbl.complaints'))
    <div class="nav-item">
      <a href="{{ route('admin.reports.cbl.index') }}" class="{{ request()->routeIs('admin.reports.cbl.*')?'active':'' }}">
        <i class="bi bi-safe2" style="color:var(--gold)"></i> CBL Compliance
      </a>
    </div>
    @endif
    @endif
    @if($u->hasAdminPermission('referrals.view'))
    <div class="nav-item"><a href="{{ route('admin.referrals.index') }}" class="{{ request()->routeIs('admin.referrals.*')?'active':'' }}"><i class="bi bi-gift-fill"></i> Referrals</a></div>
    @endif
    @if($u->hasAdminPermission('decline.tracker'))
    <div class="nav-item"><a href="{{ route('admin.declines.index') }}" class="{{ request()->routeIs('admin.declines.*')?'active':'' }}"><i class="bi bi-x-circle-fill"></i> Decline Tracker</a></div>
    @endif
    @if($u->hasAdminPermission('bulk_sms'))
    <div class="nav-item"><a href="{{ route('admin.bulk-sms.index') }}" class="{{ request()->routeIs('admin.bulk-sms.*')?'active':'' }}"><i class="bi bi-chat-dots-fill"></i> Bulk SMS</a></div>
    @endif
    <div class="nav-lbl">Agent Network</div>
    <div class="nav-item">
      <a href="{{ route('admin.agents.applications') }}" class="{{ request()->routeIs('admin.agents.applications*') ? 'active' : '' }}">
        <i class="bi bi-person-plus-fill" style="color:#6ee7b7"></i>
        Agent Applications
        @php try { $pendingAgents = \App\Models\AgentApplication::where('status', 'pending')->count(); } catch(\Exception $e) { $pendingAgents = 0; } @endphp
        @if($pendingAgents > 0)
        <span style="margin-left:auto;background:var(--gold);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;line-height:1.8">{{ $pendingAgents }}</span>
        @endif
      </a>
    </div>
    <div class="nav-item">
      <a href="{{ route('admin.agents.index') }}" class="{{ request()->routeIs('admin.agents.index') || request()->routeIs('admin.agents.show') ? 'active' : '' }}">
        <i class="bi bi-shop" style="color:#6ee7b7"></i> Active Agents
      </a>
    </div>
    <div class="nav-lbl">Config</div>
    @if($u->hasAdminPermission('users.view'))
    <div class="nav-item"><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index')?'active':'' }}"><i class="bi bi-people-fill"></i> Users</a></div>
    <div class="nav-item"><a href="{{ route('admin.users.profile-requests') }}" class="{{ request()->routeIs('admin.users.profile-requests')?'active':'' }}"><i class="bi bi-person-gear"></i> Profile Requests</a></div>
    @endif
    @if($u->hasAdminPermission('roles.manage'))
    <div class="nav-item"><a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*')?'active':'' }}"><i class="bi bi-shield-lock-fill"></i> Roles & Permissions</a></div>
    @endif
    @if($u->hasAdminPermission('products.manage'))
    <div class="nav-item"><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*')?'active':'' }}"><i class="bi bi-box-fill"></i> Loan Products</a></div>
    @endif
    @if($u->hasAdminPermission('banks.manage'))
    <div class="nav-item"><a href="{{ route('admin.banks.index') }}" class="{{ request()->routeIs('admin.banks.*')?'active':'' }}"><i class="bi bi-bank"></i> Manage Banks</a></div>
    @endif
    @if($u->hasAdminPermission('settings'))
    <div class="nav-item"><a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*')?'active':'' }}"><i class="bi bi-gear-fill"></i> Settings</a></div>
    @endif
    <div class="nav-lbl">System</div>
    @if($u->hasAdminPermission('notifications'))
    <div class="nav-item">
      <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*')?'active':'' }}">
        <i class="bi bi-bell-fill"></i> Notifications
        @php try { $unreadNotifs = \App\Models\Notification::where('user_id', auth('admin')->id())->where('is_read',false)->count(); } catch(\Exception $e) { $unreadNotifs = 0; } @endphp
        @if($unreadNotifs > 0)
        <span style="margin-left:auto;background:var(--err);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;line-height:1.8">{{ $unreadNotifs }}</span>
        @endif
      </a>
    </div>
    @endif
    @if($u->hasAdminPermission('audit_log'))
    <div class="nav-item"><a href="{{ route('admin.audit.index') }}" class="{{ request()->routeIs('admin.audit.*')?'active':'' }}"><i class="bi bi-journal-text"></i> Audit Log</a></div>
    @endif

    <div style="margin-top:20px;padding:0 22px;margin-bottom:10px">
      <button onclick="openModal('logoutModal')" style="width:100%;background:rgba(220,38,38,.1);color:#dc2626;border:1px solid rgba(220,38,38,.2);padding:10px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;font-family:'DM Sans',sans-serif" onmouseover="this.style.background='rgba(220,38,38,.2)'" onmouseout="this.style.background='rgba(220,38,38,.1)'">
        <i class="bi bi-box-arrow-right"></i> Sign Out
      </button>
    </div>
  </nav>
  <div class="sb-foot">
    <a href="{{ route('admin.profile.index') }}" style="text-decoration:none;display:block" title="My Profile">
    <div class="upill" style="cursor:pointer;transition:background .2s;border-radius:10px;padding:6px 4px" onmouseover="this.style.background='rgba(201,148,58,.12)'" onmouseout="this.style.background=''">
      @php $authUser = auth('admin')->user(); @endphp
      @if($authUser->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($authUser->profile_photo))
      <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($authUser->profile_photo) }}"
           alt="{{ $authUser->name }}"
           style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(201,148,58,.5)">
      @else
      <div class="uav">{{ strtoupper(substr($authUser->name??'A',0,1)) }}</div>
      @endif
      <div>
        <div class="uname">{{ $authUser->name??'Admin' }}</div>
        <div class="urole">{{ ucfirst(str_replace('_',' ',$authUser->role??'admin')) }}</div>
      </div>
      <div onclick="openModal('logoutModal')" title="Sign Out" style="margin-left:auto;width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);transition:all .2s" onmouseover="this.style.background='rgba(220,38,38,.2)';this.style.color='#ef4444'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.3)'">
        <i class="bi bi-power"></i>
      </div>
    </div>
    </a>
  </div>
</aside>

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
      @php try { $bellUnread = \App\Models\Notification::where('user_id', auth('admin')->id())->where('is_read',false)->count(); } catch(\Exception $e) { $bellUnread = 0; } @endphp
      <a href="{{ route('admin.notifications.index') }}" class="tbtn" style="text-decoration:none;position:relative" title="Notifications">
        <i class="bi bi-bell{{ $bellUnread > 0 ? '-fill' : '' }}" style="{{ $bellUnread > 0 ? 'color:var(--green)' : '' }}"></i>
        @if($bellUnread > 0)<span class="ndot"></span>@endif
      </a>
      <a href="{{ route('admin.profile.index') }}" class="tbtn" style="text-decoration:none;overflow:hidden" title="My Profile">
        @if(auth('admin')->user()->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth('admin')->user()->profile_photo))
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth('admin')->user()->profile_photo) }}"
             style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid var(--border)">
        @else
        <i class="bi bi-person-circle" style="{{ request()->routeIs('admin.profile.*') ? 'color:var(--green)' : '' }}"></i>
        @endif
      </a>
      <button type="button" class="tbtn" title="Logout" onclick="document.getElementById('logoutModal').classList.add('open')">
        <i class="bi bi-box-arrow-right"></i>
      </button>
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

{{-- ── LOGOUT CONFIRMATION MODAL ─────────────────────────────── --}}
<div id="logoutModal">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:mIn .22s ease;overflow:hidden">
    <div style="padding:24px 26px;text-align:center">
      <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#dc2626">
        <i class="bi bi-box-arrow-right"></i>
      </div>
      <div style="font-size:18px;font-weight:700;color:var(--dark);margin-bottom:8px;font-family:'Playfair Display',serif">Sign out?</div>
      <div style="font-size:13px;color:var(--muted);margin-bottom:24px">Are you sure you want to log out of {{ config('app.name') }} Admin?</div>
      <div style="display:flex;gap:10px;justify-content:center">
        <button type="button" onclick="document.getElementById('logoutModal').classList.remove('open')" class="btn btn-o" style="flex:1">
          Cancel
        </button>
        <form method="POST" action="{{ route('admin.logout') }}" style="flex:1">
          @csrf
          <button type="submit" class="btn btn-e" style="width:100%;justify-content:center">
            <i class="bi bi-box-arrow-right"></i> Sign Out
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ── IDLE SESSION WARNING MODAL ──────────────────────────────────── --}}
<div id="idleModal" style="display:none;position:fixed;inset:0;background:rgba(10,30,18,.65);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px)">
  <div style="background:#fff;border-radius:18px;width:100%;max-width:420px;margin:20px;box-shadow:0 30px 70px rgba(0,0,0,.25);overflow:hidden;text-align:center">
    <div style="background:linear-gradient(135deg,var(--gold),var(--goldl));padding:28px 26px 20px">
      <div style="font-size:44px;margin-bottom:8px">&#9200;</div>
      <div style="font-size:19px;font-weight:800;color:#fff;margin-bottom:4px;font-family:'Playfair Display',serif">Session Expiring Soon</div>
      <div style="font-size:13px;color:rgba(255,255,255,.85)">You've been inactive for a while</div>
    </div>
    <div style="padding:28px 26px">
      <div style="font-size:13.5px;color:#475569;margin-bottom:20px">You will be automatically logged out in</div>
      <div style="font-size:60px;font-weight:900;color:var(--gold);line-height:1;margin-bottom:4px" id="idleCounter">60</div>
      <div style="font-size:12px;color:#94a3b8;margin-bottom:28px">seconds</div>
      <div style="display:flex;gap:10px;justify-content:center">
        <form method="POST" action="{{ route('admin.logout') }}" style="flex:1">
          @csrf
          <button type="submit" class="btn btn-o" style="width:100%;justify-content:center">Log Out Now</button>
        </form>
        <button type="button" onclick="stayLoggedIn()" class="btn btn-p" style="flex:1;justify-content:center">
          <i class="bi bi-shield-check"></i> Stay Logged In
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const sbToggle=document.getElementById('sbToggle'),sb=document.getElementById('sb');
sbToggle.addEventListener('click',()=>sb.classList.toggle('open'));
setTimeout(()=>document.querySelectorAll('.alert').forEach(el=>{el.style.transition='opacity .5s';el.style.opacity='0';setTimeout(()=>el.remove(),500)}),4500);
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.mo').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
document.getElementById('logoutModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')});
function switchTab(g,id){document.querySelectorAll('[data-tg="'+g+'"]').forEach(e=>e.classList.remove('active'));document.querySelectorAll('[data-pg="'+g+'"]').forEach(e=>e.classList.remove('active'));document.querySelector('[data-tg="'+g+'"][data-t="'+id+'"]').classList.add('active');document.querySelector('[data-pg="'+g+'"][data-p="'+id+'"]').classList.add('active')}

// ── IDLE AUTO-LOGOUT (30 min idle → 60 s warning → logout) ──────────
(function(){
  const IDLE_MS    = 30 * 60 * 1000;
  const WARN_SECS  = 60;
  const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const LOGOUT_URL = document.querySelector('#idleModal form')?.action || '/admin/logout';
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
