@extends('officer.layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('content')
@php
use App\Models\{LoanApplication, Loan, LoanInstallment, Payment, User, Document, LoanProduct};
$officer = auth('officer')->user();
$now     = now();

// ── OFFICER KPIs ─────────────────────────────────────────────────────────
$assignedTotal   = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','!=','draft')->count();
$pendingReview   = LoanApplication::where('assigned_officer_id',$officer->id)->whereIn('status',['submitted','info_requested'])->count();
$underReview     = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','under_review')->count();
$completedMonth  = LoanApplication::where('assigned_officer_id',$officer->id)->whereIn('status',['approved','declined','disbursed'])->whereMonth('decided_at',$now->month)->whereYear('decided_at',$now->year)->count();
$myClients       = User::where('assigned_officer_id',$officer->id)->where('role','borrower')->count();
$docsPending     = Document::whereHas('application',fn($q)=>$q->where('assigned_officer_id',$officer->id))->where('status','pending')->count();
$totalApproved   = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','approved')->count();
$totalDeclined   = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','declined')->count();

// ── APPLICATION STATUS BREAKDOWN ─────────────────────────────────────────
$statusBreakdown = [
    'Submitted'    => LoanApplication::where('assigned_officer_id',$officer->id)->where('status','submitted')->count(),
    'Under Review' => LoanApplication::where('assigned_officer_id',$officer->id)->where('status','under_review')->count(),
    'Info Needed'  => LoanApplication::where('assigned_officer_id',$officer->id)->where('status','info_requested')->count(),
    'Approved'     => LoanApplication::where('assigned_officer_id',$officer->id)->where('status','approved')->count(),
    'Declined'     => LoanApplication::where('assigned_officer_id',$officer->id)->where('status','declined')->count(),
    'Disbursed'    => LoanApplication::where('assigned_officer_id',$officer->id)->where('status','disbursed')->count(),
];

// ── MONTHLY ACTIVITY — last 6 months ─────────────────────────────────────
$monthlyActivity = collect(range(5,0))->map(function($i) use ($officer,$now){
    $d   = $now->copy()->subMonths($i);
    $sub = LoanApplication::where('assigned_officer_id',$officer->id)->whereMonth('submitted_at',$d->month)->whereYear('submitted_at',$d->year)->count();
    $app = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','approved')->whereMonth('decided_at',$d->month)->whereYear('decided_at',$d->year)->count();
    $dec = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','declined')->whereMonth('decided_at',$d->month)->whereYear('decided_at',$d->year)->count();
    $cli = User::where('assigned_officer_id',$officer->id)->where('role','borrower')->whereMonth('created_at',$d->month)->whereYear('created_at',$d->year)->count();
    $amt = LoanApplication::where('assigned_officer_id',$officer->id)->where('status','approved')->whereMonth('decided_at',$d->month)->whereYear('decided_at',$d->year)->sum('approved_amount');
    return ['month'=>$d->format('M'),'submitted'=>$sub,'approved'=>$app,'declined'=>$dec,'clients'=>$cli,'amount'=>$amt];
});

// ── DOCUMENT STATUS ───────────────────────────────────────────────────────
$docStatus = [
    'Verified' => Document::whereHas('application',fn($q)=>$q->where('assigned_officer_id',$officer->id))->where('status','verified')->count(),
    'Pending'  => Document::whereHas('application',fn($q)=>$q->where('assigned_officer_id',$officer->id))->where('status','pending')->count(),
    'Rejected' => Document::whereHas('application',fn($q)=>$q->where('assigned_officer_id',$officer->id))->where('status','rejected')->count(),
];

// ── PRODUCT BREAKDOWN (my applications) ──────────────────────────────────
$myProductBreakdown = LoanApplication::where('assigned_officer_id',$officer->id)
    ->where('status','!=','draft')
    ->join('loan_products','loan_applications.loan_product_id','=','loan_products.id')
    ->selectRaw('loan_products.name, count(*) as cnt, sum(requested_amount) as total')
    ->groupBy('loan_products.name')
    ->get();

// ── RECENT ITEMS ──────────────────────────────────────────────────────────
$myApplications = LoanApplication::with(['user','loanProduct'])
    ->where('assigned_officer_id',$officer->id)->where('status','!=','draft')
    ->latest()->take(8)->get();
$pendingDocs = Document::with(['application.user'])
    ->whereHas('application',fn($q)=>$q->where('assigned_officer_id',$officer->id))
    ->where('status','pending')->latest()->take(5)->get();
$recentClients = User::where('assigned_officer_id',$officer->id)->where('role','borrower')
    ->with(['loanApplications'=>fn($q)=>$q->latest()->take(1)])->latest()->take(5)->get();

// Approval rate
$totalDecided = $totalApproved + $totalDeclined;
$approvalRate = $totalDecided > 0 ? round($totalApproved/$totalDecided*100,1) : 0;
@endphp

{{-- GREETING --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
  <div>
    <div style="font-size:22px;font-weight:800">Good {{ $now->hour<12?'morning':($now->hour<17?'afternoon':'evening') }}, {{ explode(' ',$officer->name)[0] }} 👋</div>
    <div style="font-size:13px;color:var(--muted);margin-top:3px">{{ $now->format('l, d F Y') }} &nbsp;·&nbsp; Your activity overview</div>
  </div>
  <a href="{{ route('officer.walk-in.create') }}" class="btn btn-p"><i class="bi bi-person-plus-fill"></i> Register Walk-in Client</a>
</div>

{{-- PRIMARY KPI CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px">
  @foreach([
    ['My Assigned',   $assignedTotal,  'file-earmark-person', '#4f46e5','rgba(79,70,229,.1)',   route('officer.applications.assigned')],
    ['Pending Review',$pendingReview,  'hourglass-split',     '#f59e0b','rgba(245,158,11,.1)',  route('officer.applications.pending')],
    ['My Clients',    $myClients,      'people-fill',         '#10b981','rgba(22,163,74,.1)',  route('officer.clients.index')],
    ['Docs Pending',  $docsPending,    'file-earmark-x',      '#ef4444','rgba(239,68,68,.1)',   route('officer.documents.index')],
  ] as [$lbl,$val,$icon,$color,$bg,$link])
  <a href="{{ $link }}" style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 22px;display:flex;align-items:center;justify-content:space-between;text-decoration:none;transition:all .2s" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
    <div><div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px">{{ $lbl }}</div><div style="font-size:28px;font-weight:800;color:var(--dark);line-height:1">{{ $val }}</div></div>
    <div style="width:50px;height:50px;border-radius:14px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:22px;color:{{ $color }};flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
  </a>
  @endforeach
</div>

{{-- SECONDARY KPI ROW --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px">
  @foreach([
    ['Under Review',   $underReview,    'search',         '#06b6d4'],
    ['Completed Month',$completedMonth, 'check-circle',   '#10b981'],
    ['Total Approved', $totalApproved,  'check-lg',       '#10b981'],
    ['Approval Rate',  $approvalRate.'%','graph-up',      $approvalRate>=70?'#10b981':($approvalRate>=50?'#f59e0b':'#ef4444')],
  ] as [$lbl,$val,$icon,$color])
  <div style="background:#fff;border:1px solid var(--border);border-radius:13px;padding:14px 16px;display:flex;align-items:center;gap:11px">
    <div style="width:38px;height:38px;border-radius:10px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;font-size:16px;color:{{ $color }};flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div style="font-size:20px;font-weight:800;color:var(--dark)">{{ $val }}</div><div style="font-size:11px;color:var(--muted)">{{ $lbl }}</div></div>
  </div>
  @endforeach
</div>

{{-- CHARTS ROW 1 — Monthly Activity + Application Status Doughnut --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

  {{-- Monthly Activity grouped bar --}}
  <div class="card">
    <div class="card-hdr">
      <span class="card-title"><i class="bi bi-bar-chart-fill" style="color:var(--p)"></i> My Monthly Activity — Last 6 Months</span>
      <div style="display:flex;gap:10px;font-size:11px">
        @foreach([['#4f46e5','Submitted'],['#10b981','Approved'],['#ef4444','Declined'],['#0ea5e9','New Clients']] as [$c,$l])
        <div style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;background:{{ $c }};border-radius:2px;display:inline-block"></span><span style="color:var(--muted)">{{ $l }}</span></div>
        @endforeach
      </div>
    </div>
    <div class="card-body" style="padding:14px"><canvas id="ocMonthly" height="150"></canvas></div>
  </div>

  {{-- Application Status Doughnut --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-pie-chart-fill" style="color:var(--p)"></i> Application Status</span></div>
    <div class="card-body" style="padding:14px;display:flex;flex-direction:column;align-items:center">
      <canvas id="ocStatus" height="160" style="max-width:180px"></canvas>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:12px;width:100%">
        @foreach([
          ['Submitted','#6366f1',$statusBreakdown['Submitted']],
          ['Under Review','#06b6d4',$statusBreakdown['Under Review']],
          ['Info Needed','#f59e0b',$statusBreakdown['Info Needed']],
          ['Approved','#10b981',$statusBreakdown['Approved']],
          ['Declined','#ef4444',$statusBreakdown['Declined']],
          ['Disbursed','#4f46e5',$statusBreakdown['Disbursed']],
        ] as [$lbl,$c,$v])
        <div style="display:flex;align-items:center;gap:5px;font-size:11.5px">
          <span style="width:8px;height:8px;border-radius:50%;background:{{ $c }};flex-shrink:0"></span>
          <span style="color:var(--muted)">{{ $lbl }}</span>
          <span style="font-weight:700;margin-left:auto;color:var(--dark)">{{ $v }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- CHARTS ROW 2 — Loan Value + Document Status + Product Breakdown --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">

  {{-- Loan value per month (line) --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-graph-up" style="color:var(--p)"></i> Approved Loan Value (M)</span></div>
    <div class="card-body" style="padding:14px"><canvas id="ocLoanValue" height="200"></canvas></div>
  </div>

  {{-- Document status doughnut --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-files" style="color:var(--p)"></i> Document Status</span></div>
    <div class="card-body" style="padding:14px;display:flex;flex-direction:column;align-items:center;justify-content:center">
      <canvas id="ocDocs" height="180" style="max-width:180px"></canvas>
      <div style="display:flex;gap:14px;margin-top:12px;justify-content:center">
        @foreach([['Verified','#10b981',$docStatus['Verified']],['Pending','#f59e0b',$docStatus['Pending']],['Rejected','#ef4444',$docStatus['Rejected']]] as [$lbl,$c,$v])
        <div style="display:flex;flex-direction:column;align-items:center;gap:3px">
          <span style="width:10px;height:10px;border-radius:50%;background:{{ $c }};display:block"></span>
          <span style="font-size:11px;color:var(--muted)">{{ $lbl }}</span>
          <span style="font-weight:800;font-size:16px;color:var(--dark)">{{ $v }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Product breakdown --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-layers-fill" style="color:#8b5cf6"></i> My Apps by Product</span></div>
    <div class="card-body" style="padding:14px">
      <canvas id="ocProducts" height="200"></canvas>
    </div>
  </div>
</div>

{{-- APPROVAL RATE TREND LINE --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-graph-up-arrow" style="color:#10b981"></i> Submitted vs Approved vs Declined — Last 6 Months</span>
    <span style="font-size:12px;color:var(--muted)">Approval rate: <strong style="color:{{ $approvalRate>=70?'#10b981':($approvalRate>=50?'#f59e0b':'#ef4444') }}">{{ $approvalRate }}%</strong></span>
  </div>
  <div class="card-body" style="padding:14px"><canvas id="ocTrend" height="80"></canvas></div>
</div>

{{-- BOTTOM ROW — My Applications + Docs to Verify + Recent Clients --}}
<div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px">

  {{-- My Applications --}}
  <div class="card">
    <div class="card-hdr">
      <span class="card-title">My Assigned Applications</span>
      <a href="{{ route('officer.applications.assigned') }}" class="btn btn-sm btn-o">View All</a>
    </div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead><tr><th>Applicant</th><th>Product</th><th>Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @forelse($myApplications as $app)
          <tr>
            <td><div style="display:flex;align-items:center;gap:9px"><div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:11px;flex-shrink:0">{{ strtoupper(substr($app->applicant_name,0,1)) }}</div><div><div style="font-size:13px;font-weight:600">{{ $app->applicant_name }}</div><div style="font-size:11px;color:var(--muted)">{{ $app->application_number }}</div></div></div></td>
            <td style="font-size:12px;color:var(--muted)">{{ $app->loanProduct?->name??'—' }}</td>
            <td style="font-weight:700">M{{ number_format($app->requested_amount??0,0) }}</td>
            <td><span class="badge b{{ $app->status_badge }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
            <td><a href="{{ route('officer.applications.show',$app) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i></a></td>
          </tr>
          @empty<tr><td colspan="5"><div style="text-align:center;padding:40px;color:var(--muted)"><i class="bi bi-inbox" style="font-size:36px;opacity:.25;display:block;margin-bottom:8px"></i>No applications assigned yet</div></td></tr>@endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Docs to Verify --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-file-earmark-check" style="color:var(--warn)"></i> Docs to Verify</span><a href="{{ route('officer.documents.index') }}" class="btn btn-sm btn-o">All</a></div>
    @forelse($pendingDocs as $doc)
    <a href="{{ route('officer.documents.show',$doc) }}" style="padding:12px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--warn);flex-shrink:0"><i class="bi bi-file-earmark"></i></div>
      <div style="flex:1;min-width:0"><div style="font-size:12.5px;font-weight:600;color:var(--dark);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $doc->application?->applicant_name??'—' }}</div><div style="font-size:11px;color:var(--muted)">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</div></div>
      <span class="badge bw">Pending</span>
    </a>
    @empty<div style="text-align:center;padding:24px;color:var(--muted)"><i class="bi bi-check-circle-fill" style="color:#10b981;font-size:28px;display:block;margin-bottom:6px"></i><div style="font-size:12.5px;font-weight:600">All clear!</div></div>@endforelse
  </div>

  {{-- Recent Clients --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-people" style="color:var(--p)"></i> Recent Clients</span><a href="{{ route('officer.clients.index') }}" class="btn btn-sm btn-o">All</a></div>
    @forelse($recentClients as $client)
    <a href="{{ route('officer.clients.show',$client) }}" style="padding:11px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
      <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0">{{ strtoupper(substr($client->name,0,1)) }}</div>
      <div style="flex:1;min-width:0"><div style="font-size:12.5px;font-weight:600;color:var(--dark)">{{ $client->name }}</div><div style="font-size:11px;color:var(--muted)">{{ $client->phone }}</div></div>
      <span class="badge bs" style="font-size:10px">{{ $client->loanApplications->count() }} app(s)</span>
    </a>
    @empty<div style="text-align:center;padding:24px;color:var(--muted);font-size:12.5px">No clients yet</div>@endforelse
  </div>
</div>

@push('scripts')
<script>
(function(){
  const ma  = @json($monthlyActivity->values());
  const sb  = @json($statusBreakdown);
  const ds  = @json($docStatus);
  const pd  = @json($myProductBreakdown->map(fn($p)=>['name'=>$p->name,'cnt'=>$p->cnt,'total'=>$p->total??0]));
  const gc  = '#f1f5f9';
  const fmt = v => 'M'+(v>=1000?(v/1000).toFixed(1)+'k':Number(v).toFixed(0));

  // Monthly Activity grouped
  new Chart(document.getElementById('ocMonthly'),{type:'bar',data:{
    labels:ma.map(d=>d.month),
    datasets:[
      {label:'Submitted',data:ma.map(d=>d.submitted),backgroundColor:'#4f46e5cc',borderRadius:4},
      {label:'Approved', data:ma.map(d=>d.approved), backgroundColor:'#10b981cc',borderRadius:4},
      {label:'Declined', data:ma.map(d=>d.declined), backgroundColor:'#ef4444cc',borderRadius:4},
      {label:'New Clients',data:ma.map(d=>d.clients),backgroundColor:'#0ea5e9cc',borderRadius:4},
    ]
  },options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:gc},ticks:{font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});

  // Status doughnut
  new Chart(document.getElementById('ocStatus'),{type:'doughnut',data:{
    labels:Object.keys(sb),
    datasets:[{data:Object.values(sb),backgroundColor:['#6366f1','#06b6d4','#f59e0b','#10b981','#ef4444','#4f46e5'],borderWidth:2,borderColor:'#fff'}]
  },options:{responsive:true,maintainAspectRatio:true,cutout:'65%',plugins:{legend:{display:false}}}});

  // Loan value line
  new Chart(document.getElementById('ocLoanValue'),{type:'line',data:{
    labels:ma.map(d=>d.month),
    datasets:[{label:'Approved Amount',data:ma.map(d=>d.amount),borderColor:'var(--p)',backgroundColor:'rgba(26,107,60,.08)',borderWidth:2.5,fill:true,tension:0.4,pointRadius:5,pointBackgroundColor:'var(--p)'}]
  },options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+fmt(c.raw)}}},scales:{y:{beginAtZero:true,grid:{color:gc},ticks:{callback:fmt,font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});

  // Doc status doughnut
  new Chart(document.getElementById('ocDocs'),{type:'doughnut',data:{
    labels:Object.keys(ds),
    datasets:[{data:Object.values(ds),backgroundColor:['#10b981','#f59e0b','#ef4444'],borderWidth:2,borderColor:'#fff'}]
  },options:{responsive:true,maintainAspectRatio:true,cutout:'60%',plugins:{legend:{display:false}}}});

  // Products horizontal bar
  new Chart(document.getElementById('ocProducts'),{type:'bar',data:{
    labels:pd.map(p=>p.name.replace(' Loan','')),
    datasets:[{label:'Applications',data:pd.map(p=>p.cnt),backgroundColor:['#4f46e5cc','#10b981cc','#f59e0bcc','#0ea5e9cc','#8b5cf6cc'],borderRadius:6}]
  },options:{responsive:true,maintainAspectRatio:true,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,grid:{color:gc},ticks:{font:{size:10}}},y:{grid:{display:false},ticks:{font:{size:10}}}}}});

  // Trend line
  new Chart(document.getElementById('ocTrend'),{type:'line',data:{
    labels:ma.map(d=>d.month),
    datasets:[
      {label:'Submitted',data:ma.map(d=>d.submitted),borderColor:'#4f46e5',borderWidth:2.5,fill:false,tension:0.4,pointRadius:4,pointBackgroundColor:'#4f46e5'},
      {label:'Approved', data:ma.map(d=>d.approved), borderColor:'#10b981',borderWidth:2.5,fill:false,tension:0.4,pointRadius:4,pointBackgroundColor:'#10b981'},
      {label:'Declined', data:ma.map(d=>d.declined), borderColor:'#ef4444',borderWidth:2,  fill:false,tension:0.4,pointRadius:4,pointBackgroundColor:'#ef4444'},
    ]
  },options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:gc},ticks:{font:{size:11}}},x:{grid:{display:false},ticks:{font:{size:11}}}}}});
})();
</script>
@endpush
@endsection
