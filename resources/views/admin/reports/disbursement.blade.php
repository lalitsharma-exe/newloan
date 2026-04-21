@extends('admin.layouts.app')
@section('title','Disbursement Report')
@section('page-title','Disbursement Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Disbursement
@endsection
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si p"><i class="bi bi-calendar-check"></i></div><div><div class="sv">M{{ number_format($data['today'],0) }}</div><div class="sl">Disbursed Today</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-calendar-week"></i></div><div><div class="sv">M{{ number_format($data['thisWeek'],0) }}</div><div class="sl">This Week</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-calendar-month"></i></div><div><div class="sv">M{{ number_format($data['thisMonth'],0) }}</div><div class="sl">This Month</div></div></div>
  <div class="sc"><div class="si s"><i class="bi bi-hash"></i></div><div><div class="sv">{{ $data['loans']->count() }}</div><div class="sl">Loans in Period</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">Category</label>
    <select name="category" class="fc">
      <option value="">— All Categories —</option>
      @foreach(['Defence','Nss','Police','Lcs','Pensioner','Civil servants','Teacher','SMEs'] as $cat)
        <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.disbursement') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="g2 mb6" style="gap:16px">
  <div class="card"><div class="card-hdr"><span class="card-title">By Product</span></div><div style="padding:14px 18px">
    @foreach($data['byProduct'] as $name => $amount)
    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px"><span>{{ $name }}</span><strong>M{{ number_format($amount,0) }}</strong></div>
    @endforeach
  </div></div>
  <div class="card"><div class="card-hdr"><span class="card-title">By Day</span></div><div style="padding:14px 18px">
    @foreach($data['byDay'] as $day => $amount)
    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px"><span class="muted">{{ $day }}</span><strong>M{{ number_format($amount,0) }}</strong></div>
    @endforeach
    @if($data['byDay']->isEmpty())<div class="empty" style="padding:20px"><i class="bi bi-calendar-x"></i><p>No disbursements in period</p></div>@endif
  </div></div>
</div>
<div class="card">
  <div class="card-hdr"><span class="card-title">Disbursements ({{ $data['loans']->count() }})</span><form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="disbursement"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan #</th><th>Borrower</th><th>Product</th><th>Principal</th><th>Monthly</th><th>Payout</th><th>Date</th></tr></thead>
    <tbody>
    @forelse($data['loans'] as $l)
    <tr>
      <td><a href="{{ route('admin.loans.show',$l) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $l->loan_number }}</a></td>
      <td><div style="font-weight:600;font-size:13px">{{ $l->user->name??'—' }}</div><div class="muted">{{ $l->user->phone??'' }}</div></td>
      <td class="muted">{{ $l->loanProduct->name??'—' }}</td>
      <td><strong>M{{ number_format($l->principal_amount,0) }}</strong></td>
      <td>M{{ number_format($l->monthly_installment,2) }}</td>
      <td class="muted">{{ ucfirst(str_replace('_',' ',$l->payout_method??'—')) }}</td>
      <td class="muted">{{ $l->disbursement_date?->format('d M Y')??'—' }}</td>
    </tr>
    @empty<tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i><p>No disbursements in this period</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
