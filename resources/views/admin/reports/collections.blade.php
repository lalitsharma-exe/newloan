@extends('admin.layouts.app')

@section('title','Collections Report')
@section('page-title','Collections Report')

@section('content')

<div class="card mb6">

<div class="card-hdr flex jb aic">

<span class="card-title">Collections Report</span>

<form method="POST" action="{{ route('admin.reports.export') }}">
@csrf

<input type="hidden" name="report_type" value="collections">
<input type="hidden" name="format" value="csv">

<button class="btn btn-o btn-sm">
<i class="bi bi-download"></i> Export CSV
</button>

</form>

</div>


<div class="card-body">

<form method="GET" class="flex gap3 aic mb4">

<div class="fg">
<label class="fl">From</label>

<input
type="date"
name="date_from"
class="fc"
value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}">

</div>


<div class="fg">
<label class="fl">To</label>

<input
type="date"
name="date_to"
class="fc"
value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}">

</div>


<div class="fg">
<label class="fl">&nbsp;</label>

<button class="btn btn-p">
<i class="bi bi-funnel"></i> Filter
</button>

</div>

</form>

</div>

</div>



<div class="card">

<div class="card-body">

@if(isset($data['loans']))

<table class="dt">

<thead>
<tr>
<th>Loan #</th>
<th>Borrower</th>
<th>Product</th>
<th>Amount</th>
<th>Outstanding</th>
<th>Status</th>
</tr>
</thead>

<tbody>

@forelse($data['loans'] as $loan)

<tr>

<td style="font-weight:700;color:#4f46e5">
{{ $loan->loan_number }}
</td>

<td>{{ $loan->user->name ?? '—' }}</td>

<td>{{ $loan->loanProduct->name ?? '—' }}</td>

<td>L {{ number_format($loan->principal_amount,0) }}</td>

<td>L {{ number_format($loan->outstanding_balance,0) }}</td>

<td>

<span class="badge {{ $loan->status==='active'?'bok':($loan->status==='overdue'?'bw':'bs') }}">
{{ ucfirst($loan->status) }}
</span>

</td>

</tr>

@empty

<tr>

<td colspan="6">

<div class="empty">
<i class="bi bi-inbox"></i>
<p>No data for this period</p>

</div>

</td>

</tr>

@endforelse

</tbody>

</table>


@elseif(isset($data['payments']))

<table class="dt">

<thead>
<tr>
<th>Reference</th>
<th>Borrower</th>
<th>Amount</th>
<th>Method</th>
<th>Date</th>
</tr>
</thead>

<tbody>

@forelse($data['payments'] as $p)

<tr>

<td style="font-weight:700;color:#4f46e5">
{{ $p->payment_reference }}
</td>

<td>{{ $p->loan->user->name ?? '—' }}</td>

<td>L {{ number_format($p->amount,2) }}</td>

<td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>

<td>{{ $p->created_at->format('d M Y') }}</td>

</tr>

@empty

<tr>

<td colspan="5">

<div class="empty">
<i class="bi bi-inbox"></i>
<p>No data</p>

</div>

</td>

</tr>

@endforelse

</tbody>

</table>


@else

<div class="empty">

<i class="bi bi-bar-chart"></i>

<p>No data available for the selected period.</p>

</div>

@endif

</div>

</div>

@endsection