@extends('admin.layouts.app')
@section('title','Payment Failure Report')
@section('page-title','Payment Failure Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Payment Failures
@endsection
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si e"><i class="bi bi-x-circle-fill"></i></div><div><div class="sv">{{ $data['totalFailed'] }}</div><div class="sl">Total Failed</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-currency-dollar"></i></div><div><div class="sv">M{{ number_format($data['totalAmount'],0) }}</div><div class="sl">Amount Failed</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-bank2"></i></div><div><div class="sv">{{ $data['insufficientFunds'] }}</div><div class="sl">Insufficient Funds</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-credit-card-2-front"></i></div><div><div class="sv">{{ $data['blockedCard'] }}</div><div class="sl">Blocked Cards</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">Method</label>
    <select name="method" class="fc">
      <option value="">All Methods</option>
      @foreach(['card_payment'=>'Card Payment','debit_order'=>'Debit Order','mobile_money'=>'Mobile Money'] as $v=>$l)
      <option value="{{ $v }}" {{ ($filters['method']??'')===$v?'selected':'' }}>{{ $l }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.payment-failures') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="g2 mb6" style="gap:16px">
  <div class="card"><div class="card-hdr"><span class="card-title">Failures by Method</span></div><div style="padding:14px 18px">
    @foreach($data['byMethod'] as $method => $count)
    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px"><span>{{ ucfirst(str_replace('_',' ',$method)) }}</span><span class="badge be">{{ $count }}</span></div>
    @endforeach
    @if($data['byMethod']->isEmpty())<div class="empty" style="padding:20px"><i class="bi bi-check-circle"></i><p>No failures in period</p></div>@endif
  </div></div>
  <div class="card"><div class="card-hdr"><span class="card-title">Failures by Reason</span></div><div style="padding:14px 18px">
    @foreach($data['byReason'] as $reason => $count)
    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px"><span>{{ ucfirst(str_replace('_',' ',$reason??'Unknown')) }}</span><span class="badge be">{{ $count }}</span></div>
    @endforeach
    @if($data['byReason']->isEmpty())<div class="empty" style="padding:20px"><i class="bi bi-check-circle"></i><p>No failures in period</p></div>@endif
  </div></div>
</div>
<div class="card">
  <div class="card-hdr"><span class="card-title">Failed Payments ({{ $data['totalFailed'] }})</span><form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="payment_failures"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Reference</th><th>Borrower</th><th>Phone</th><th>Loan #</th><th>Amount</th><th>Method</th><th>Reason</th><th>Date</th></tr></thead>
    <tbody>
    @forelse($data['payments'] as $p)
    <tr>
      <td style="font-weight:700;color:var(--err);font-size:12px">{{ $p->payment_reference }}</td>
      <td style="font-weight:600;font-size:13px">{{ $p->loan->user->name??'—' }}</td>
      <td style="color:var(--p)">{{ $p->loan->user->phone??'—' }}</td>
      <td class="muted">{{ $p->loan->loan_number??'—' }}</td>
      <td>M{{ number_format($p->amount,2) }}</td>
      <td class="muted">{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
      <td><span class="badge be">{{ ucfirst(str_replace('_',' ',$p->failure_reason??'Unknown')) }}</span></td>
      <td class="muted">{{ $p->created_at->format('d M Y H:i') }}</td>
    </tr>
    @empty<tr><td colspan="8"><div class="empty"><i class="bi bi-check-circle"></i><p>No failed payments in period</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
