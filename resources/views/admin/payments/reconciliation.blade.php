@extends('admin.layouts.app')
@section('title','Reconciliation')
@section('page-title','Payment Reconciliation')
@section('bc','<a href="'.route('admin.payments.index').'">Payments</a> / Reconciliation')
@section('content')

{{-- Date picker --}}
<form method="GET" action="{{ route('admin.payments.reconciliation') }}" style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-end">
  <div class="fg" style="margin-bottom:0">
    <label class="fl">Reconciliation Date</label>
    <input type="date" name="date" class="fc" value="{{ $date }}" style="min-width:180px">
  </div>
  <button type="submit" class="btn btn-p"><i class="bi bi-search"></i> Load</button>
  <form method="POST" action="{{ route('admin.payments.reconcile') }}" style="margin:0">@csrf
    <input type="hidden" name="date" value="{{ $date }}">
    <button type="submit" class="btn btn-ok"><i class="bi bi-check-circle"></i> Mark Reconciled</button>
  </form>
</form>

{{-- Summary cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  @foreach([
    ['Verified','L '.number_format($summary['total_received'],2),'check-circle-fill','#10b981','rgba(16,185,129,.1)',$summary['count_verified']],
    ['Pending','L '.number_format($summary['total_pending'],2),'clock-fill','#f59e0b','rgba(245,158,11,.1)',$summary['count_pending']],
    ['Rejected','L '.number_format($summary['total_rejected'],2),'x-circle-fill','#ef4444','rgba(239,68,68,.1)',$summary['count_rejected']],
    ['Reversed','L '.number_format($summary['total_reversed'],2),'arrow-counterclockwise','#64748b','rgba(100,116,139,.1)',0],
  ] as [$label,$amt,$icon,$color,$bg,$count])
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
      <div style="width:42px;height:42px;border-radius:12px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;color:{{ $color }};font-size:18px"><i class="bi bi-{{ $icon }}"></i></div>
      <div style="font-size:13px;font-weight:600;color:var(--muted)">{{ $label }}</div>
    </div>
    <div style="font-size:22px;font-weight:800;color:var(--dark)">{{ $amt }}</div>
    @if($count > 0)<div style="font-size:12px;color:var(--muted);margin-top:3px">{{ $count }} transaction(s)</div>@endif
  </div>
  @endforeach
</div>

{{-- By Method --}}
@if(!empty($summary['by_method']))
<div class="card" style="margin-bottom:20px">
  <div class="card-hdr"><span class="card-title">Breakdown by Method</span></div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px">
      @foreach($summary['by_method'] as $method => $data)
      <div style="background:#f8fafc;border-radius:12px;padding:14px;border:1px solid var(--border)">
        <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">{{ ucfirst(str_replace('_',' ',$method)) }}</div>
        <div style="font-size:20px;font-weight:800;color:var(--p);margin-top:4px">L{{ number_format($data['total'],2) }}</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $data['count'] }} payments</div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endif

{{-- Transactions list --}}
<div class="card">
  <div class="card-hdr"><span class="card-title">Transactions for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
    <span style="background:var(--bg);color:var(--muted);font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px">{{ $summary['payments']->count() }}</span>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Reference</th><th>Borrower</th><th>Amount</th><th>Method</th><th>Status</th><th>Time</th></tr></thead>
      <tbody>
        @forelse($summary['payments'] as $p)
        @php $sc=['verified'=>['#059669','#d1fae5'],'pending'=>['#d97706','#fef3c7'],'rejected'=>['#dc2626','#fee2e2'],'reversed'=>['#64748b','#f1f5f9']]; [$tc,$bc]=$sc[$p->status]??['#64748b','#f1f5f9']; @endphp
        <tr>
          <td><span style="font-family:monospace;font-size:12.5px;color:var(--p);font-weight:700">{{ $p->payment_reference }}</span></td>
          <td style="font-size:13px">{{ $p->loan->user->name ?? '—' }}</td>
          <td><strong>L{{ number_format($p->amount,2) }}</strong></td>
          <td><span style="font-size:12px;color:var(--muted)">{{ ucfirst(str_replace('_',' ',$p->method)) }}</span></td>
          <td><span style="background:{{ $bc }};color:{{ $tc }};font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:20px">{{ ucfirst($p->status) }}</span></td>
          <td style="font-size:12px;color:var(--muted)">{{ $p->created_at->format('H:i:s') }}</td>
        </tr>
        @empty
        <tr><td colspan="6"><div style="text-align:center;padding:40px;color:var(--muted)"><i class="bi bi-inbox" style="font-size:36px;opacity:.2;display:block;margin-bottom:10px"></i>No transactions for this date</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection