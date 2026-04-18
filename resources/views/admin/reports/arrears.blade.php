@extends('admin.layouts.app')
@section('title','Arrears Report')
@section('page-title','Arrears Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Arrears
@endsection
@section('content')
<div class="g4 mb6">
  @foreach($data['buckets'] as $label => $items)
  <div class="sc"><div class="si {{ str_contains($label,'90') ? 'e' : (str_contains($label,'61') ? 'e' : (str_contains($label,'31') ? 'w' : 'w')) }}"><i class="bi bi-clock-history"></i></div><div><div class="sv">M{{ number_format($items->sum('outstanding_amount'),0) }}</div><div class="sl">{{ $label }}</div></div></div>
  @endforeach
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">Min Days Overdue</label><input type="number" name="min_days" class="fc" placeholder="e.g. 30" value="{{ $filters['min_days']??'' }}" style="width:140px"></div>
  <div class="fg" style="margin-bottom:0;min-width:180px"><label class="fl">Category</label>
    <select name="category" class="fc">
      <option value="">— All Categories —</option>
      @foreach(['Defence','Nss','Police','Lcs','Pensioner','Civil servants','Teacher'] as $cat)
        <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.arrears') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">Overdue Installments ({{ $data['installments']->count() }}) — Total: M{{ number_format($data['total_overdue'],0) }}</span>
    <form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="arrears"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form>
  </div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan #</th><th>Borrower</th><th>Phone</th><th>Product</th><th>Due Date</th><th>Days Late</th><th>Amount Due</th><th>Outstanding</th></tr></thead>
    <tbody>
    @forelse($data['installments'] as $i)
    @php $days = $i->due_date->diffInDays(now()); @endphp
    <tr>
      <td><a href="{{ route('admin.loans.show',$i->loan) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $i->loan->loan_number??'—' }}</a></td>
      <td style="font-weight:600;font-size:13px">{{ $i->loan->user->name??'—' }}</td>
      <td style="color:var(--p);font-weight:600">{{ $i->loan->user->phone??'—' }}</td>
      <td class="muted">{{ $i->loan->loanProduct->name??'—' }}</td>
      <td style="color:var(--err);font-weight:600">{{ $i->due_date->format('d M Y') }}</td>
      <td><span class="badge {{ $days > 90 ? 'be' : ($days > 30 ? 'bw' : 'bw') }}">{{ $days }} days</span></td>
      <td>M{{ number_format($i->total_amount,2) }}</td>
      <td style="font-weight:700;color:var(--err)">M{{ number_format($i->outstanding_amount,2) }}</td>
    </tr>
    @empty<tr><td colspan="8"><div class="empty"><i class="bi bi-check-circle"></i><p>No overdue installments</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
