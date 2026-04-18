@extends('admin.layouts.app')
@section('title','Portfolio Report')
@section('page-title','Portfolio Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Portfolio
@endsection
@section('content')

<div class="g4 mb6">
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{ $data['activeLoans']->count() }}</div><div class="sl">Active Loans</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-pie-chart-fill"></i></div><div><div class="sv">M{{ number_format($data['totalPortfolio'],0) }}</div><div class="sl">Portfolio Value</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-calculator"></i></div><div><div class="sv">M{{ number_format($data['avgLoanSize'],0) }}</div><div class="sl">Avg Loan Size</div></div></div>
  <div class="sc"><div class="si s"><i class="bi bi-people-fill"></i></div><div><div class="sv">{{ $data['borrowerCount'] }}</div><div class="sl">Active Borrowers</div></div></div>
</div>
<div class="g2 mb6" style="gap:16px">
  <div class="sc"><div class="si {{ $data['growth'] >= 0 ? 'ok' : 'e' }}"><i class="bi bi-graph-up{{ $data['growth'] >= 0 ? '' : '-arrow' }}"></i></div><div><div class="sv" style="color:{{ $data['growth'] >= 0 ? 'var(--ok)' : 'var(--err)' }}">{{ $data['growth'] >= 0 ? '+' : '' }}{{ $data['growth'] }}%</div><div class="sl">Portfolio Growth vs Last Month</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-currency-dollar"></i></div><div><div class="sv">M{{ number_format($data['totalPrincipal'],0) }}</div><div class="sl">Total Principal Disbursed</div></div></div>
</div>

<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfYear()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">Category</label>
    <select name="category" class="fc">
      <option value="">— All Categories —</option>
      @foreach(['Defence','Nss','Police','Lcs','Pensioner','Civil servants','Teacher'] as $cat)
        <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.portfolio') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>

<div class="g2 mb6" style="gap:16px">
  <div class="card">
    <div class="card-hdr"><span class="card-title">By Status</span></div>
    <div style="padding:16px 18px">
      @foreach($data['byStatus'] as $s)
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
        <span class="badge {{ $s->status==='active'?'bok':($s->status==='overdue'?'be':'bs') }}">{{ ucfirst(str_replace('_',' ',$s->status)) }}</span>
        <div style="text-align:right"><strong>{{ $s->count }}</strong> loans &nbsp;·&nbsp; M{{ number_format($s->total,0) }}</div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="card">
    <div class="card-hdr"><span class="card-title">By Product</span></div>
    <div style="padding:16px 18px">
      @foreach($data['byProduct'] as $name => $amount)
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
        <span>{{ $name }}</span><strong>M{{ number_format($amount,0) }}</strong>
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="card">
  <div class="card-hdr">
    <span class="card-title">Active Loans ({{ $data['activeLoans']->count() }})</span>
    <form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="portfolio"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> Export CSV</button></form>
  </div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan #</th><th>Borrower</th><th>Product</th><th>Principal</th><th>Outstanding</th><th>Monthly</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($data['activeLoans'] as $l)
    <tr>
      <td><a href="{{ route('admin.loans.show',$l) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $l->loan_number }}</a></td>
      <td><div style="font-size:13px;font-weight:600">{{ $l->user->name??'—' }}</div><div class="muted">{{ $l->user->phone??'' }}</div></td>
      <td class="muted">{{ $l->loanProduct->name??'—' }}</td>
      <td>M{{ number_format($l->principal_amount,0) }}</td>
      <td style="font-weight:700;color:var(--err)">M{{ number_format($l->outstanding_balance,0) }}</td>
      <td>M{{ number_format($l->monthly_installment,2) }}</td>
      <td><span class="badge {{ $l->status==='active'?'bok':($l->status==='overdue'?'be':'bs') }}">{{ ucfirst($l->status) }}</span></td>
    </tr>
    @empty<tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i><p>No loans found</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
