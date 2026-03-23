@extends('admin.layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('bc') Home @endsection
@section('content')
@php
use App\Models\{Loan, LoanApplication, LoanInstallment, Payment, User, LoanProduct};
$now = now();
$totalPortfolio = Loan::whereIn('status',['active','overdue'])->sum('outstanding_balance');
$par30amount    = Loan::whereIn('status',['active','overdue'])
    ->whereHas('installments', fn($q)=>$q->where('status','overdue')->whereDate('due_date','<=',$now->copy()->subDays(30)))
    ->sum('outstanding_balance');
$par30pct    = $totalPortfolio > 0 ? round($par30amount/$totalPortfolio*100,1) : 0;
$totalPrincipal = Loan::sum('principal_amount');
$defaultAmt  = Loan::whereIn('status',['defaulted','written_off'])->sum('outstanding_balance');
$defaultRate = $totalPrincipal > 0 ? round($defaultAmt/$totalPrincipal*100,1) : 0;
$monthExpected  = LoanInstallment::whereMonth('due_date',$now->month)->whereYear('due_date',$now->year)->sum('total_amount');
$monthCollected = Payment::whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','verified')->sum('amount');
$collectionPct  = $monthExpected > 0 ? round($monthCollected/$monthExpected*100,1) : 0;
$yearData = collect(range(1,12))->map(function($m) use ($now){
    $dis = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->sum('principal_amount');
    $col = Payment::whereMonth('created_at',$m)->whereYear('created_at',$now->year)->where('status','verified')->sum('amount');
    $exp = LoanInstallment::whereMonth('due_date',$m)->whereYear('due_date',$now->year)->sum('total_amount');
    $arr = LoanInstallment::whereMonth('due_date',$m)->whereYear('due_date',$now->year)->where('status','overdue')->sum('outstanding_amount');
    $ini = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->get()->sum(fn($l)=>round($l->principal_amount*0.40,2));
    $adm = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->get()->sum(fn($l)=>50*$l->term_months);
    $int = $dis > 0 ? round($dis*0.15*6,2) : 0;
    $agr = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->count();
    $cp  = $exp > 0 ? round($col/$exp*100,1) : 0;
    $turnover = $dis + $ini + $adm + $int;
    return ['disbursed'=>$dis,'collected'=>$col,'expected'=>$exp,'arrears'=>$arr,'initiation'=>$ini,'admin'=>$adm,'interest'=>$int,'agreements'=>$agr,'collPct'=>$cp,'turnover'=>$turnover];
});
$ytdT  = $yearData->sum('disbursed');
$ytdC  = $yearData->sum('collected');
$ytdI  = $yearData->sum('initiation');
$ytdAd = $yearData->sum('admin');
$ytdIn = $yearData->sum('interest');
$ytdAg = $yearData->sum('agreements');
$ytdTurnover = $yearData->sum('turnover');
$arrBuckets = [
    '1-30 days'  => Loan::where('status','overdue')->whereHas('installments', fn($q)=>$q->where('status','overdue')->whereBetween('due_date',[$now->copy()->subDays(30),$now->copy()->subDay()]))->count(),
    '31-60 days' => Loan::where('status','overdue')->whereHas('installments', fn($q)=>$q->where('status','overdue')->whereBetween('due_date',[$now->copy()->subDays(60),$now->copy()->subDays(31)]))->count(),
    '61-90 days' => Loan::where('status','overdue')->whereHas('installments', fn($q)=>$q->where('status','overdue')->whereBetween('due_date',[$now->copy()->subDays(90),$now->copy()->subDays(61)]))->count(),
    '90+ days'   => Loan::where('status','overdue')->whereHas('installments', fn($q)=>$q->where('status','overdue')->whereDate('due_date','<',$now->copy()->subDays(90)))->count(),
];
$loanStatusBreakdown = ['Active'=>Loan::where('status','active')->count(),'Overdue'=>Loan::where('status','overdue')->count(),'Closed'=>Loan::where('status','closed')->count(),'Written Off'=>Loan::where('status','written_off')->count()];
$productStats = LoanProduct::withCount(['loans as loan_count'])->withSum(['loans as total_amount'=>fn($q)=>$q],'principal_amount')->get();
$months12 = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
@endphp

{{-- greeting --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
  <div>
    <div style="font-size:22px;font-weight:800">Good {{ $now->hour<12?'morning':($now->hour<17?'afternoon':'evening') }}, {{ explode(' ',auth('admin')->user()->name)[0] }} 👋</div>
    <div style="font-size:13px;color:var(--muted);margin-top:3px">{{ $now->format('l, d F Y') }} &nbsp;·&nbsp; Here's your lending overview</div>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-o btn-sm"><i class="bi bi-bar-chart-line"></i> Reports</a>
    <a href="{{ route('admin.applications.create') }}" class="btn btn-p"><i class="bi bi-plus-lg"></i> New Application</a>
  </div>
</div>

{{-- PRIMARY KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px">
  @foreach([
    ['Total Portfolio',     'M '.number_format($totalPortfolio,0),                    'pie-chart-fill',    '#4f46e5','rgba(79,70,229,.1)',   route('admin.loans.index')],
    ['Disbursed This Month','M '.number_format($yearData[$now->month-1]['disbursed'],0),'arrow-up-circle-fill','#8b5cf6','rgba(139,92,246,.1)',route('admin.loans.index')],
    ['Collected This Month','M '.number_format($monthCollected,0),                    'cash-stack',        '#10b981','rgba(16,185,129,.1)',  route('admin.payments.index')],
    ['Pending Applications',LoanApplication::whereIn('status',['submitted','under_review','info_requested','on_hold'])->count(),'hourglass-split','#f59e0b','rgba(245,158,11,.1)',route('admin.applications.index')],
  ] as [$lbl,$val,$icon,$color,$bg,$link])
  <a href="{{ $link }}" style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 22px;display:flex;align-items:center;justify-content:space-between;text-decoration:none;transition:all .2s" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
    <div><div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px">{{ $lbl }}</div><div style="font-size:28px;font-weight:800;color:var(--dark);line-height:1">{{ $val }}</div></div>
    <div style="width:50px;height:50px;border-radius:14px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:22px;color:{{ $color }};flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
  </a>
  @endforeach
</div>

{{-- SECONDARY KPIs --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:22px">
  @foreach([
    ['Total Borrowers',User::where('role','borrower')->count(),'people-fill','#0ea5e9'],
    ['Active Loans',Loan::where('status','active')->count(),'check-circle-fill','#10b981'],
    ['Overdue Loans',Loan::where('status','overdue')->count(),'exclamation-circle','#ef4444'],
    ['Collection %',$collectionPct.'%','percent',$collectionPct>=80?'#10b981':($collectionPct>=60?'#f59e0b':'#ef4444')],
    ['PAR 30',$par30pct.'%','shield-exclamation',$par30pct<5?'#10b981':($par30pct<10?'#f59e0b':'#ef4444')],
    ['Default Rate',$defaultRate.'%','x-circle-fill',$defaultRate<3?'#10b981':($defaultRate<8?'#f59e0b':'#ef4444')],
  ] as [$lbl,$val,$icon,$color])
  <div style="background:#fff;border:1px solid var(--border);border-radius:13px;padding:14px 16px;display:flex;align-items:center;gap:11px">
    <div style="width:38px;height:38px;border-radius:10px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;font-size:16px;color:{{ $color }};flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div style="font-size:18px;font-weight:800;color:var(--dark)">{{ $val }}</div><div style="font-size:11px;color:var(--muted)">{{ $lbl }}</div></div>
  </div>
  @endforeach
</div>

{{-- MONTHLY MOVEMENT TABLE --}}
<div class="card" style="margin-bottom:22px">
  <div class="card-hdr">
    <span class="card-title" style="font-size:16px;font-weight:800"><i class="bi bi-table" style="color:var(--p)"></i> Monthly Movement — {{ $now->year }}</span>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-o"><i class="bi bi-download"></i> Export</a>
  </div>
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:12.5px">
      <thead>
        <tr style="background:#f8fafc">
          <th style="padding:11px 14px;text-align:left;font-weight:700;color:var(--muted);border-bottom:2px solid var(--border);font-size:11px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Movement</th>
          @foreach($months12 as $m)<th style="padding:11px 8px;text-align:right;font-weight:700;color:var(--muted);border-bottom:2px solid var(--border);font-size:11px;text-transform:uppercase;letter-spacing:.04em">{{ $m }}</th>@endforeach
          <th style="padding:11px 14px;text-align:right;font-weight:800;color:var(--p);border-bottom:2px solid var(--border);font-size:11px;text-transform:uppercase;letter-spacing:.05em;background:rgba(26,92,46,.04)">YTD</th>
        </tr>
      </thead>
      <tbody>
        @foreach([
          ['Turnover',   'turnover',   '#4f46e5', false, $ytdTurnover],
          ['Capital',    'disbursed',  '#0ea5e9', false, $ytdT],
          ['Initiation', 'initiation', '#8b5cf6', false, $ytdI],
          ['Admin',      'admin',      '#64748b', false, $ytdAd],
          ['Interest',   'interest',   '#f59e0b', false, $ytdIn],
          ['Agreements', 'agreements', '#10b981', true,  $ytdAg],
          ['Collected',  'collected',  '#10b981', false, $ytdC],
          ['Collection %','collPct',   '#06b6d4', true,  null],
        ] as $ri => [$rowLabel, $key, $color, $isInt, $ytd])
        <tr style="background:{{ $ri%2===0?'#fff':'#fafafa' }}" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='{{ $ri%2===0?'#fff':'#fafafa' }}'">
          <td style="padding:10px 14px;font-weight:600;color:var(--dark);border-bottom:1px solid var(--border);white-space:nowrap">{{ $rowLabel }}</td>
          @foreach($yearData as $yd)
          @php $v = $yd[$key] ?? 0; @endphp
          <td style="padding:10px 8px;text-align:right;color:{{ $v>0?$color:'var(--muted)' }};font-weight:{{ $v>0?'600':'400' }};border-bottom:1px solid var(--border)">
            @if($key==='collPct'){{ $v>0?$v.'%':'—' }}@elseif($isInt){{ number_format($v) }}@else{{ number_format($v,2) }}@endif
          </td>
          @endforeach
          <td style="padding:10px 14px;text-align:right;font-weight:800;color:{{ $color }};border-bottom:1px solid var(--border);background:rgba(26,92,46,.04)">
            @if($key==='collPct')—@elseif($ytd!==null&&$isInt){{ number_format($ytd) }}@elseif($ytd!==null){{ number_format($ytd,2) }}@else—@endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- CHARTS ROW 1 — Turnover / Capital / Initiation --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-bar-chart-fill" style="color:#4f46e5"></i> Turnover (M)</span></div><div class="card-body" style="padding:14px"><canvas id="cTurnover" height="180"></canvas></div></div>
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-arrow-up-circle-fill" style="color:#0ea5e9"></i> Capital (M)</span></div><div class="card-body" style="padding:14px"><canvas id="cCapital" height="180"></canvas></div></div>
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-percent" style="color:#8b5cf6"></i> Initiation Fees (M)</span></div><div class="card-body" style="padding:14px"><canvas id="cInitiation" height="180"></canvas></div></div>
</div>

{{-- CHARTS ROW 2 — Admin Fees / Interest --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-receipt" style="color:#64748b"></i> Admin Fees (M)</span></div><div class="card-body" style="padding:14px"><canvas id="cAdmin" height="150"></canvas></div></div>
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-currency-dollar" style="color:#f59e0b"></i> Interest Income (M)</span></div><div class="card-body" style="padding:14px"><canvas id="cInterest" height="150"></canvas></div></div>
</div>

{{-- CHARTS ROW 3 — Lending Overview grouped + Loan Status doughnut --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">
  <div class="card">
    <div class="card-hdr">
      <span class="card-title"><i class="bi bi-graph-up-arrow" style="color:var(--p)"></i> Monthly Lending Overview</span>
      <div style="display:flex;gap:8px;font-size:11px;flex-wrap:wrap">
        @foreach([['#1a5c2e','Disbursement'],['#f59e0b','Expected'],['#10b981','Received'],['#ef4444','Arrears'],['#8b5cf6','Exp. Profit'],['#78716c','Rec. Profit']] as [$c,$l])
        <div style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;background:{{ $c }};border-radius:2px;display:inline-block"></span><span style="color:var(--muted)">{{ $l }}</span></div>
        @endforeach
      </div>
    </div>
    <div class="card-body" style="padding:14px"><canvas id="cLendingOverview" height="140"></canvas></div>
  </div>
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-pie-chart-fill" style="color:var(--p)"></i> Loan Status</span></div>
    <div class="card-body" style="padding:14px;display:flex;flex-direction:column;align-items:center">
      <canvas id="cLoanStatus" height="180" style="max-width:200px"></canvas>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;width:100%">
        @foreach([['Active','#10b981',$loanStatusBreakdown['Active']],['Overdue','#ef4444',$loanStatusBreakdown['Overdue']],['Closed','#64748b',$loanStatusBreakdown['Closed']],['Written Off','#374151',$loanStatusBreakdown['Written Off']]] as [$lbl,$c,$v])
        <div style="display:flex;align-items:center;gap:6px;font-size:12px"><span style="width:10px;height:10px;border-radius:50%;background:{{ $c }};flex-shrink:0"></span><span style="color:var(--muted)">{{ $lbl }}</span><span style="font-weight:700;margin-left:auto">{{ $v }}</span></div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- CHARTS ROW 4 — Agreements / Arrears Buckets / Products --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-file-earmark-text" style="color:#10b981"></i> Agreements / Month</span></div><div class="card-body" style="padding:14px"><canvas id="cAgreements" height="180"></canvas></div></div>
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-exclamation-triangle-fill" style="color:#ef4444"></i> Arrears Buckets</span></div><div class="card-body" style="padding:14px"><canvas id="cArrears" height="180"></canvas></div></div>
  <div class="card"><div class="card-hdr"><span class="card-title"><i class="bi bi-layers-fill" style="color:#8b5cf6"></i> By Loan Product</span></div><div class="card-body" style="padding:14px"><canvas id="cProducts" height="180"></canvas></div></div>
</div>

{{-- Collection % line --}}
<div class="card" style="margin-bottom:22px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-graph-up" style="color:#06b6d4"></i> Collection Rate % — {{ $now->year }}</span>
    <span style="font-size:12px;color:var(--muted)">Target: 80%+</span>
  </div>
  <div class="card-body" style="padding:14px"><canvas id="cCollPct" height="80"></canvas></div>
</div>

{{-- BOTTOM ROW —  Recent Apps + Overdue + Payments --}}
<div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;margin-bottom:16px">
  {{-- Recent Applications --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Recent Applications</span><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-o">View All</a></div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead><tr><th>Applicant</th><th>Product</th><th>Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @forelse($recentApplications as $app)
          <tr>
            <td><div style="display:flex;align-items:center;gap:9px"><div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:11px;flex-shrink:0">{{ strtoupper(substr($app->applicant_name,0,1)) }}</div><div><div style="font-size:13px;font-weight:600">{{ $app->applicant_name }}</div><div style="font-size:11px;color:var(--muted)">{{ $app->application_number }}</div></div></div></td>
            <td style="font-size:12px;color:var(--muted)">{{ $app->loanProduct?->name??'—' }}</td>
            <td style="font-weight:700">M{{ number_format($app->requested_amount??0,0) }}</td>
            <td><span class="badge b{{ $app->status_badge }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
            <td><a href="{{ route('admin.applications.show',$app) }}" class="btn btn-xs btn-o">View</a></td>
          </tr>
          @empty<tr><td colspan="5"><div style="text-align:center;padding:40px;color:var(--muted)">No applications yet</div></td></tr>@endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Overdue --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title" style="display:flex;align-items:center;gap:7px"><span style="width:8px;height:8px;background:#ef4444;border-radius:50%;animation:pulse 2s infinite;display:inline-block"></span>Overdue</span><a href="{{ route('admin.loans.index',['status'=>'overdue']) }}" class="btn btn-sm btn-o">All</a></div>
    @forelse($overdueLoans as $loan)
    <a href="{{ route('admin.loans.show',$loan) }}" style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
      <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:11px;flex-shrink:0">{{ strtoupper(substr($loan->user->name??'U',0,1)) }}</div>
      <div style="flex:1;min-width:0"><div style="font-size:12.5px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $loan->user->name??'—' }}</div><div style="font-size:11px;color:var(--muted)">{{ $loan->loan_number }}</div></div>
      <div style="text-align:right;flex-shrink:0"><div style="font-size:12.5px;font-weight:700;color:#ef4444">M{{ number_format($loan->outstanding_balance,0) }}</div><div style="font-size:10.5px;color:var(--muted)">{{ $loan->days_overdue }}d</div></div>
    </a>
    @empty<div style="text-align:center;padding:40px;color:var(--muted)"><i class="bi bi-check-circle-fill" style="color:#10b981;font-size:32px;display:block;margin-bottom:8px"></i><div style="font-size:12.5px;font-weight:600">All loans current!</div></div>@endforelse
  </div>

  {{-- Recent Payments --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Recent Payments</span><a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-o">All</a></div>
    @forelse($recentPayments as $pay)
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:9px">
      <div style="width:34px;height:34px;border-radius:10px;background:rgba(16,185,129,.1);display:flex;align-items:center;justify-content:center;font-size:15px;color:#10b981;flex-shrink:0"><i class="bi bi-cash"></i></div>
      <div style="flex:1;min-width:0"><div style="font-size:11.5px;font-weight:700;color:var(--p);font-family:monospace">{{ $pay->payment_reference }}</div><div style="font-size:11px;color:var(--muted)">{{ $pay->loan->user->name??'—' }}</div></div>
      <div style="text-align:right;flex-shrink:0"><div style="font-size:13px;font-weight:700">M{{ number_format($pay->amount,0) }}</div><span class="badge b{{ $pay->status_badge }}" style="font-size:10px">{{ ucfirst($pay->status) }}</span></div>
    </div>
    @empty<div style="text-align:center;padding:40px;color:var(--muted)"><i class="bi bi-credit-card" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i><div style="font-size:12.5px">No payments yet</div></div>@endforelse
  </div>
</div>

<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>

@push('scripts')
<script>
(function(){
  const months = @json($months12);
  const yd     = @json($yearData->values());
  const ab     = @json($arrBuckets);
  const prod   = @json($productStats->map(fn($p)=>['name'=>$p->name,'count'=>$p->loan_count??0,'amt'=>$p->total_amount??0]));
  const lsb    = @json($loanStatusBreakdown);
  const gc='#f1f5f9';
  const fmt = v => 'M'+(v>=1000?(v/1000).toFixed(1)+'k':Number(v).toFixed(0));

  function mkBar(id,data,color,label){
    new Chart(document.getElementById(id),{type:'bar',data:{labels:months,datasets:[{label,data,backgroundColor:color+'cc',borderRadius:5,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+fmt(c.raw)}}},scales:{y:{beginAtZero:true,grid:{color:gc},ticks:{callback:fmt,font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});
  }
  mkBar('cTurnover',  yd.map(d=>d.turnover),   '#4f46e5','Turnover');
  mkBar('cCapital',   yd.map(d=>d.disbursed),  '#0ea5e9','Capital');
  mkBar('cInitiation',yd.map(d=>d.initiation), '#8b5cf6','Initiation');
  mkBar('cAdmin',     yd.map(d=>d.admin),       '#64748b','Admin Fees');
  mkBar('cInterest',  yd.map(d=>d.interest),    '#f59e0b','Interest');
  mkBar('cAgreements',yd.map(d=>d.agreements),  '#10b981','Agreements');

  // Lending Overview grouped
  new Chart(document.getElementById('cLendingOverview'),{type:'bar',data:{labels:months,datasets:[
    {label:'Disbursement',       data:yd.map(d=>d.disbursed),             backgroundColor:'#1a5c2ecc',borderRadius:4},
    {label:'Expected Collection',data:yd.map(d=>d.expected),              backgroundColor:'#f59e0bcc',borderRadius:4},
    {label:'Received Collection',data:yd.map(d=>d.collected),             backgroundColor:'#10b981cc',borderRadius:4},
    {label:'Arrears',            data:yd.map(d=>d.arrears),               backgroundColor:'#ef4444cc',borderRadius:4},
    {label:'Expected Profit',    data:yd.map(d=>d.interest),              backgroundColor:'#8b5cf6cc',borderRadius:4},
    {label:'Received Profit',    data:yd.map(d=>d.collected*0.25),        backgroundColor:'#78716ccc',borderRadius:4},
  ]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.dataset.label+': '+fmt(c.raw)}}},scales:{y:{beginAtZero:true,grid:{color:gc},ticks:{callback:fmt,font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});

  // Loan Status doughnut
  new Chart(document.getElementById('cLoanStatus'),{type:'doughnut',data:{labels:Object.keys(lsb),datasets:[{data:Object.values(lsb),backgroundColor:['#10b981','#ef4444','#64748b','#374151'],borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,maintainAspectRatio:true,cutout:'65%',plugins:{legend:{display:false}}}});

  // Arrears buckets (horizontal)
  new Chart(document.getElementById('cArrears'),{type:'bar',data:{labels:Object.keys(ab),datasets:[{data:Object.values(ab),backgroundColor:['#f59e0b','#f97316','#ef4444','#991b1b'],borderRadius:6}]},options:{responsive:true,maintainAspectRatio:true,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,grid:{color:gc},ticks:{font:{size:10}}},y:{grid:{display:false},ticks:{font:{size:10}}}}}});

  // Products
  new Chart(document.getElementById('cProducts'),{type:'bar',data:{labels:prod.map(p=>p.name.replace(' Loan','')),datasets:[{label:'Count',data:prod.map(p=>p.count),backgroundColor:'#4f46e5cc',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:gc},ticks:{font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});

  // Collection % line
  new Chart(document.getElementById('cCollPct'),{type:'line',data:{labels:months,datasets:[
    {label:'Collection %',data:yd.map(d=>d.collPct),borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,.08)',borderWidth:2.5,fill:true,tension:0.4,pointRadius:4,pointBackgroundColor:'#06b6d4'},
    {label:'Target 80%', data:months.map(()=>80),borderColor:'#ef4444',borderWidth:1.5,borderDash:[6,4],pointRadius:0,fill:false}
  ]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.dataset.label+': '+c.raw+'%'}}},scales:{y:{beginAtZero:true,max:120,grid:{color:gc},ticks:{callback:v=>v+'%',font:{size:11}}},x:{grid:{display:false},ticks:{font:{size:11}}}}}});
})();
</script>
@endpush
@endsection
