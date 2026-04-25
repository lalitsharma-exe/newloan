@extends('admin.layouts.app')
@section('title','Default Report')
@section('page-title','Default Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Defaults
@endsection
@section('content')
<div class="g3 mb6" style="gap:16px">
  <div class="sc"><div class="si e"><i class="bi bi-x-circle-fill"></i></div><div><div class="sv">M{{ number_format($data['totalDefaulted'],0) }}</div><div class="sl">Total Defaulted</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-trash3-fill"></i></div><div><div class="sv">M{{ number_format($data['totalWrittenOff'],0) }}</div><div class="sl">Written Off</div></div></div>
  <div class="sc"><div class="si {{ $data['defaultRate'] < 3 ? 'ok' : ($data['defaultRate'] < 8 ? 'w' : 'e') }}"><i class="bi bi-percent"></i></div><div><div class="sv" style="color:{{ $data['defaultRate'] < 3 ? 'var(--ok)' : 'var(--err)' }}">{{ $data['defaultRate'] }}%</div><div class="sl">Default Rate</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfYear()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.default') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">Defaulted & Written-Off Loans ({{ $data['loans']->count() }})</span><form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="default"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan #</th><th>Borrower</th><th>District</th><th>Phone</th><th>Product</th><th>Principal</th><th>Amount Lost</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    @forelse($data['loans'] as $l)
    <tr>
      <td><a href="{{ route('admin.loans.show',$l) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $l->loan_number }}</a></td>
      <td style="font-weight:600;font-size:13px">{{ $l->user->name??'—' }}</td>
      <td class="muted">{{ $l->application->district ?? '—' }}</td>
      <td style="color:var(--p)">{{ $l->user->phone??'—' }}</td>
      <td class="muted">{{ $l->loanProduct->name??'—' }}</td>
      <td>M{{ number_format($l->principal_amount,0) }}</td>
      <td style="font-weight:700;color:var(--err)">M{{ number_format($l->outstanding_balance,0) }}</td>
      <td><span class="badge be">{{ ucfirst(str_replace('_',' ',$l->status)) }}</span></td>
      <td class="muted">{{ $l->updated_at->format('d M Y') }}</td>
    </tr>
    @empty<tr><td colspan="9"><div class="empty"><i class="bi bi-check-circle"></i><p>No defaults in this period</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
