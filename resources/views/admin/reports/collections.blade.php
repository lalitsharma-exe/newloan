@extends('admin.layouts.app')
@section('title','Collection Report')
@section('page-title','Collection Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Collections
@endsection
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si ok"><i class="bi bi-calendar-check"></i></div><div><div class="sv">M{{ number_format($data['today'],0) }}</div><div class="sl">Collected Today</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-calendar-week"></i></div><div><div class="sv">M{{ number_format($data['thisWeek'],0) }}</div><div class="sl">This Week</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-cash-stack"></i></div><div><div class="sv">M{{ number_format($data['thisMonth'],0) }}</div><div class="sl">This Month</div></div></div>
  <div class="sc"><div class="si {{ $data['collectionRate'] >= 90 ? 'ok' : ($data['collectionRate'] >= 70 ? 'w' : 'e') }}"><i class="bi bi-percent"></i></div><div><div class="sv">{{ $data['collectionRate'] }}%</div><div class="sl">Collection Rate</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.collections') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="g2 mb6" style="gap:16px">
  <div class="card"><div class="card-hdr"><span class="card-title">By Method</span></div><div style="padding:14px 18px">
    @foreach($data['byMethod'] as $method => $amount)
    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px">
      <span>{{ ucfirst(str_replace('_',' ',$method)) }}</span><strong>M{{ number_format($amount,0) }}</strong>
    </div>
    @endforeach
    @if($data['byMethod']->isEmpty())<div class="empty" style="padding:20px"><i class="bi bi-credit-card"></i><p>No collections in period</p></div>@endif
  </div></div>
  <div class="card"><div class="card-hdr"><span class="card-title">Daily Collections</span></div><div style="padding:14px 18px">
    @foreach($data['byDay']->take(10) as $day => $amount)
    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px"><span class="muted">{{ $day }}</span><strong>M{{ number_format($amount,0) }}</strong></div>
    @endforeach
    @if($data['byDay']->isEmpty())<div class="empty" style="padding:20px"><i class="bi bi-calendar-x"></i><p>No data</p></div>@endif
  </div></div>
</div>
<div class="card">
  <div class="card-hdr"><span class="card-title">All Collections ({{ $data['payments']->count() }})</span><form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="collections"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Reference</th><th>Borrower</th><th>Loan #</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
    <tbody>
    @forelse($data['payments'] as $p)
    <tr>
      <td style="font-weight:700;color:var(--p);font-size:12px">{{ $p->payment_reference }}</td>
      <td><div style="font-weight:600;font-size:13px">{{ $p->loan->user->name??'—' }}</div><div class="muted">{{ $p->loan->user->phone??'' }}</div></td>
      <td class="muted">{{ $p->loan->loan_number??'—' }}</td>
      <td><strong>M{{ number_format($p->amount,2) }}</strong></td>
      <td class="muted">{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
      <td class="muted">{{ $p->created_at->format('d M Y') }}</td>
    </tr>
    @empty<tr><td colspan="6"><div class="empty"><i class="bi bi-inbox"></i><p>No collections in period</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
