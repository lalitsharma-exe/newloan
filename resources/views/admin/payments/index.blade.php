@extends('admin.layouts.app')
@section('title','Payments')
@section('page-title','Payment Tracking')
@section('bc','<a href="'.route('admin.dashboard').'">Home</a> / Payments')
@section('content')

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  @foreach([
    ['Collected Today','L '.number_format($stats['total_today'],0),'cash-stack','#10b981','rgba(16,185,129,.1)'],
    ['This Month','L '.number_format($stats['total_month'],0),'calendar-month','#4f46e5','rgba(79,70,229,.1)'],
    ['Pending Verify',$stats['pending_count'],'hourglass-split','#f59e0b','rgba(245,158,11,.1)'],
    ['Transactions/Month',$stats['total_count_month'],'receipt','#0891b2','rgba(8,145,178,.1)'],
  ] as [$label,$val,$icon,$color,$bg])
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px;display:flex;align-items:center;gap:14px">
    <div style="width:50px;height:50px;border-radius:14px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:22px;color:{{ $color }};flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div style="font-size:24px;font-weight:800;color:var(--dark);line-height:1">{{ $val }}</div><div style="font-size:12px;color:var(--muted);font-weight:500;margin-top:3px">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

{{-- Quick access bar --}}
<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
  <a href="{{ route('admin.payments.reconciliation') }}" class="btn btn-o"><i class="bi bi-arrow-left-right"></i> Reconciliation</a>
  <a href="{{ route('admin.payments.index',['status'=>'pending']) }}" class="btn btn-o">
    <i class="bi bi-hourglass-split"></i> Pending Only
    @if($stats['pending_count']>0)<span style="background:var(--warn);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:20px;margin-left:2px">{{ $stats['pending_count'] }}</span>@endif
  </a>
  <a href="{{ route('admin.payments.export',request()->query()) }}" class="btn btn-o"><i class="bi bi-download"></i> Export CSV</a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.payments.index') }}" style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:20px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div class="fg" style="margin-bottom:0;flex:2;min-width:180px">
      <label class="fl">Search</label>
      <div style="position:relative"><i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
        <input type="text" name="search" class="fc" style="padding-left:33px" placeholder="Reference #, borrower…" value="{{ $filters['search']??'' }}"></div>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:140px">
      <label class="fl">Status</label>
      <select name="status" class="fc"><option value="">All Statuses</option>
        @foreach(['pending'=>'Pending','verified'=>'Verified','rejected'=>'Rejected','reversed'=>'Reversed','failed'=>'Failed'] as $v=>$l)
        <option value="{{ $v }}" {{ ($filters['status']??'')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
      </select>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:140px">
      <label class="fl">Method</label>
      <select name="method" class="fc"><option value="">All Methods</option>
        @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','mobile_money'=>'Mobile Money','card'=>'Card','cheque'=>'Cheque'] as $v=>$l)
        <option value="{{ $v }}" {{ ($filters['method']??'')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
      </select>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:130px"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from']??'' }}"></div>
    <div class="fg" style="margin-bottom:0;min-width:130px"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to']??'' }}"></div>
    <div style="display:flex;gap:8px"><button type="submit" class="btn btn-p"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.payments.index') }}" class="btn btn-o">Clear</a></div>
  </div>
</form>

{{-- Bulk form wraps table --}}
<form method="POST" action="{{ route('admin.payments.bulk-verify') }}" id="bulkForm">@csrf
<div class="card">
  <div class="card-hdr">
    <div style="display:flex;align-items:center;gap:10px">
      <input type="checkbox" id="selectAll" style="width:16px;height:16px;accent-color:var(--p);cursor:pointer" title="Select all pending">
      <span class="card-title">Payments</span>
      <span style="background:var(--bg);color:var(--muted);font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px">{{ $payments->total() }}</span>
    </div>
    <div id="bulkActions" style="display:none;align-items:center;gap:8px">
      <span id="selectedCount" style="font-size:12px;color:var(--muted)"></span>
      <button type="submit" formaction="{{ route('admin.payments.bulk-verify') }}" class="btn btn-sm btn-ok"><i class="bi bi-check-all"></i> Bulk Verify</button>
      <button type="submit" formaction="{{ route('admin.payments.bulk-reject') }}" class="btn btn-sm btn-e"><i class="bi bi-x-lg"></i> Bulk Reject</button>
    </div>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th style="width:40px"></th><th>Reference</th><th>Borrower</th><th>Loan #</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th><th style="text-align:right">Actions</th></tr></thead>
      <tbody>
        @forelse($payments as $p)
        @php $sc=['verified'=>['#059669','#d1fae5'],'pending'=>['#d97706','#fef3c7'],'rejected'=>['#dc2626','#fee2e2'],'reversed'=>['#64748b','#f1f5f9'],'failed'=>['#dc2626','#fee2e2']]; [$tc,$bc]=$sc[$p->status]??['#64748b','#f1f5f9']; @endphp
        <tr>
          <td>@if($p->status==='pending')<input type="checkbox" name="ids[]" value="{{ $p->id }}" class="row-check" style="width:15px;height:15px;accent-color:var(--p);cursor:pointer">@endif</td>
          <td><a href="{{ route('admin.payments.show',$p) }}" style="font-weight:700;color:var(--p);font-size:12.5px;font-family:monospace;text-decoration:none">{{ $p->payment_reference }}</a></td>
          <td>
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0">{{ strtoupper(substr($p->loan->user->name??'?',0,1)) }}</div>
              <span style="font-size:13px;font-weight:600">{{ $p->loan->user->name ?? '—' }}</span>
            </div>
          </td>
          <td>@if($p->loan_id)<a href="{{ route('admin.loans.show',$p->loan_id) }}" style="color:var(--p);font-size:12.5px;font-weight:600;text-decoration:none;font-family:monospace">{{ $p->loan->loan_number??'—' }}</a>@else<span class="muted">—</span>@endif</td>
          <td><strong style="font-size:13.5px">L{{ number_format($p->amount,2) }}</strong></td>
          <td style="font-size:12.5px;color:var(--muted)">{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
          <td><span style="background:{{ $bc }};color:{{ $tc }};font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:20px">{{ ucfirst($p->status) }}</span></td>
          <td style="font-size:12px;color:var(--muted);white-space:nowrap">{{ $p->created_at->format('d M Y') }}<br><span style="font-size:11px">{{ $p->created_at->format('H:i') }}</span></td>
          <td style="text-align:right">
            <div style="display:flex;gap:5px;justify-content:flex-end">
              <a href="{{ route('admin.payments.show',$p) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i> View</a>
              @if($p->status==='pending')
              <form method="POST" action="{{ route('admin.payments.verify',$p) }}" style="display:inline">@csrf<input type="hidden" name="status" value="verified"><button class="btn btn-xs btn-ok"><i class="bi bi-check-lg"></i></button></form>
              <form method="POST" action="{{ route('admin.payments.verify',$p) }}" style="display:inline">@csrf<input type="hidden" name="status" value="rejected"><button class="btn btn-xs btn-e"><i class="bi bi-x"></i></button></form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="9"><div style="text-align:center;padding:60px;color:var(--muted)"><i class="bi bi-credit-card" style="font-size:44px;opacity:.2;display:block;margin-bottom:12px"></i><div style="font-weight:600">No payments found</div></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($payments->hasPages())<div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $payments->withQueryString()->links() }}</div>@endif
</div>
</form>

<script>
const selectAll  = document.getElementById('selectAll');
const bulkBar    = document.getElementById('bulkActions');
const countLabel = document.getElementById('selectedCount');
function updateBulk(){
  const n = document.querySelectorAll('.row-check:checked').length;
  bulkBar.style.display = n > 0 ? 'flex' : 'none';
  countLabel.textContent = n > 0 ? n+' selected' : '';
}
selectAll.addEventListener('change',function(){ document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked); updateBulk(); });
document.querySelectorAll('.row-check').forEach(c=>c.addEventListener('change',updateBulk));
</script>
@endsection