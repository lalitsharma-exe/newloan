@extends('admin.layouts.app')
@section('title','Outstanding Loans')
@section('page-title','Outstanding Loans Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Outstanding Loans
@endsection
@section('content')
<div class="g2 mb6" style="gap:16px">
  <div class="sc"><div class="si e"><i class="bi bi-wallet2"></i></div><div><div class="sv">M{{ number_format($data['totalOutstanding'],0) }}</div><div class="sl">Total Outstanding Balance</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-hash"></i></div><div><div class="sv">{{ $data['totalLoans'] }}</div><div class="sl">Active/Overdue Loans</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="flex:2;min-width:180px"><label class="fl">Search Borrower</label><input type="text" name="search" class="fc" placeholder="Name or phone…" value="{{ $filters['search']??'' }}"></div>
  <div class="fg" style="margin-bottom:0;min-width:180px"><label class="fl">Category</label>
    <select name="category" class="fc">
      <option value="">— All Categories —</option>
      @foreach(['Defence','Nss','Police','Lcs','Pensioner','Civil servants','Teacher','SMEs'] as $cat)
        <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-search"></i> Search</button><a href="{{ route('admin.reports.outstanding') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">All Outstanding Loans</span><form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="outstanding"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan #</th><th>Borrower</th><th>Phone</th><th>Product</th><th>Loan Amount</th><th>Balance Remaining</th><th>Monthly</th><th>Next Due</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($data['loans'] as $l)
    @php $next = $l->installments->first(); @endphp
    <tr>
      <td><a href="{{ route('admin.loans.show',$l) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $l->loan_number }}</a></td>
      <td style="font-weight:600;font-size:13px">{{ $l->user->name??'—' }}</td>
      <td class="muted">{{ $l->user->phone??'—' }}</td>
      <td class="muted">{{ $l->loanProduct->name??'—' }}</td>
      <td>M{{ number_format($l->principal_amount,0) }}</td>
      <td style="font-weight:700;color:var(--err)">M{{ number_format($l->outstanding_balance,0) }}</td>
      <td>M{{ number_format($l->monthly_installment,2) }}</td>
      <td style="{{ $next && $next->due_date->isPast() ? 'color:var(--err);font-weight:600' : '' }}">
        {{ $next?->due_date?->format('d M Y') ?? '—' }}
      </td>
      <td><span class="badge {{ $l->status==='active'?'bok':'be' }}">{{ ucfirst($l->status) }}</span></td>
    </tr>
    @empty<tr><td colspan="9"><div class="empty"><i class="bi bi-wallet2"></i><p>No outstanding loans</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
  @if($data['loans']->hasPages())<div style="padding:14px 18px;border-top:1px solid var(--border)">{{ $data['loans']->withQueryString()->links() }}</div>@endif
</div>
@endsection
