@extends('admin.layouts.app')
@section('title', 'Collection Sheet')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / <a href="{{ route('admin.reports.index') }}">Reports</a> / Collection Sheet
@endsection
@section('content')
<div class="flex jb aic mb6">
    <div>
        <h1 style="font-size:20px;font-weight:800;color:var(--pd)">Collection Sheet</h1>
        <div class="muted" style="font-size:13px;margin-top:2px">Monthly debit list for bank collections.</div>
    </div>
    <a href="{{ route('admin.reports.collection-sheet.export', $filters) }}" class="btn btn-p">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
    </a>
</div>

<div class="card mb4">
    <div class="card-body">
        <form action="{{ route('admin.reports.collection-sheet') }}" method="GET" class="flex aic" style="gap:14px;flex-wrap:wrap">
            <div class="fg" style="margin-bottom:0;min-width:200px">
                <label class="fl">Collection Month</label>
                <input type="month" name="month" class="fc" value="{{ $data['month'] }}">
            </div>
            <div class="fg" style="margin-bottom:0;min-width:220px">
                <label class="fl">Employment Category</label>
                <select name="category" class="fc">
                    <option value="">— All Categories —</option>
                    @foreach(['Defence','NSS','Police','LCS','Pensioner','Civil servants','Teacher','Private sector'] as $c)
                    <option value="{{ $c }}" {{ ($filters['category'] ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-o" style="margin-top:auto">Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <table class="dt">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Bank</th>
                    <th>Account Number</th>
                    <th>Branch Code</th>
                    <th style="text-align:right">Amount (M)</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['installments'] as $i)
                @php $loan = $i->loan; @endphp
                <tr>
                    <td style="font-weight:700;color:var(--p)">{{ $loan->user?->name ?? 'Unknown' }}</td>
                    <td>{{ $loan->application?->bankDetails?->bank_name ?? '—' }}</td>
                    <td><code>{{ $loan->application?->bankDetails?->account_number ?? '—' }}</code></td>
                    <td><code>{{ str_pad($loan->application?->bankDetails?->branch_code ?? '', 6, '0', STR_PAD_LEFT) }}</code></td>
                    <td style="text-align:right;font-weight:800;color:var(--p)">{{ number_format($i->total_amount, 2) }}</td>
                    <td class="muted">{{ $i->due_date->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No installments found for the selected criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
