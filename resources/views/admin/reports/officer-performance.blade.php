@extends('admin.layouts.app')
@section('title','Officer Performance Report')
@section('page-title','Officer Performance Report')
@section('content')
<div class="card mb-4">
  <div class="card-header">
    <span class="card-title">Officer Performance Report</span>
    <form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="report_type" value="{{ str_replace('-','_','officer-performance') }}"><input type="hidden" name="format" value="csv"><button class="btn btn-sm btn-outline"><i class="bi bi-download"></i> Export CSV</button></form>
  </div>
  <div class="card-body">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="margin-bottom:0"><label class="form-label">From</label><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}"></div>
      <div class="form-group" style="margin-bottom:0"><label class="form-label">To</label><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body">
    @if(isset($data['loans']))
    <table class="data-table"><thead><tr><th>Loan #</th><th>Borrower</th><th>Product</th><th>Amount</th><th>Outstanding</th><th>Status</th></tr></thead><tbody>
    @forelse($data['loans'] as $loan)
    <tr><td style="font-weight:700;color:#4f46e5">{{ $loan->loan_number }}</td><td>{{ $loan->user->name??'—' }}</td><td>{{ $loan->loanProduct->name??'—' }}</td><td>L {{ number_format($loan->principal_amount,0) }}</td><td>L {{ number_format($loan->outstanding_balance,0) }}</td><td><span class="badge badge-{{ $loan->status==='active'?'success':($loan->status==='overdue'?'danger':'secondary') }}">{{ ucfirst($loan->status) }}</span></td></tr>
    @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i><p>No data for this period</p></div></td></tr>@endforelse
    </tbody></table>
    @elseif(isset($data['payments']))
    <table class="data-table"><thead><tr><th>Reference</th><th>Borrower</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody>
    @forelse($data['payments'] as $p)
    <tr><td style="font-weight:700;color:#4f46e5">{{ $p->payment_reference }}</td><td>{{ $p->loan->user->name??'—' }}</td><td>L {{ number_format($p->amount,2) }}</td><td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td><td>{{ $p->created_at->format('d M Y') }}</td></tr>
    @empty<tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i><p>No data</p></div></td></tr>@endforelse
    </tbody></table>
    @else<div class="empty-state"><i class="bi bi-bar-chart"></i><p>No data available for the selected period.</p></div>
    @endif
  </div>
</div>
@endsection
