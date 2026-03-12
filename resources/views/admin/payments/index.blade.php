@extends('admin.layouts.app')
@section('title', 'Payments')
@section('page-title', 'Payment Tracking')
@section('content')
<div class="g4 mb6">
    <div class="sc"><div class="si ok"><i class="bi bi-cash-stack"></i></div><div><div class="sv">L {{ number_format($stats['total_today'],0) }}</div><div class="stat-label">Collected Today</div></div></div>
    <div class="sc"><div class="stat-icon primary"><i class="bi bi-calendar-month"></i></div><div><div class="sv">L {{ number_format($stats['total_month'],0) }}</div><div class="stat-label">This Month</div></div></div>
    <div class="sc"><div class="stat-icon warning"><i class="bi bi-hourglass"></i></div><div><div class="sv">{{ $stats['pending_count'] }}</div><div class="sl">Pending Verification</div></div></div>
    <div class="sc"><div class="stat-icon info"><i class="bi bi-receipt"></i></div><div><div class="sv">{{ $stats['total_count_month'] }}</div><div class="sl">Transactions Month</div></div></div>
</div>
<form method="GET" action="{{ route('admin.payments.index') }}" class="filter-bar mb-4">
    <div class="fg" style="flex:2"><label class="fl">Search</label><input type="text" name="search" class="fc" placeholder="Ref#, borrower…" value="{{ $filters['search'] ?? '' }}"></div>
    <div class="fg"><label class="fl">Status</label><select name="status" class="fc"><option value="">All</option>@foreach(['pending','verified','rejected','failed'] as $s)<option value="{{ $s }}" {{ ($filters['status']??'')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <div class="fg"><label class="fl">Method</label><select name="method" class="fc"><option value="">All</option>@foreach(['card','bank_transfer','mobile_money','cash'] as $m)<option value="{{ $m }}" {{ ($filters['method']??'')===$m?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>@endforeach</select></div>
    <div style="display:flex;gap:8px;align-items:flex-end"><button type="submit" class="btn btn-p"><i class="bi bi-search"></i> Filter</button><a href="{{ route('admin.payments.index') }}" class="btn btn-o">Clear</a></div>
</form>
<div class="card">
    <div class="card-hdr"><span class="card-title">Payments ({{ $payments->total() }})</span></div>
    <div style="overflow-x:auto">
        <table class="dt">
            <thead><tr><th>Reference</th><th>Borrower</th><th>Loan #</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($payments as $p)
            <tr>
                <td><span style="font-weight:700;color:#4f46e5;font-size:12px">{{ $p->payment_reference }}</span></td>
                <td>{{ $p->loan->user->name ?? '—' }}</td>
                <td><a href="{{ route('admin.loans.show', $p->loan_id) }}" style="color:#4f46e5;font-size:12px;font-weight:600;text-decoration:none">{{ $p->loan->loan_number ?? '—' }}</a></td>
                <td><strong>L {{ number_format($p->amount,2) }}</strong></td>
                <td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
                <td><span class="badge badge-{{ $p->status_badge }}">{{ ucfirst($p->status) }}</span></td>
                <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.payments.show', $p) }}" class="btn btn-xs btn-outline">View</a>
                    @if($p->status==='pending')
                    <form method="POST" action="{{ route('admin.payments.verify', $p) }}" style="display:inline">@csrf<input type="hidden" name="status" value="verified"><button class="btn btn-xs btn-ok">Verify</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><div class="empty"><i class="bi bi-credit-card"></i><p>No payments</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
<div class="flex jb aic mt4" style="padding:16px 20px;border-top:1px solid #f1f5f9">

    <div class="muted">
        Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
    </div>

    <div class="flex gap2">
        @if ($payments->onFirstPage())
            <span class="btn btn-xs btn-o">Previous</span>
        @else
            <a href="{{ $payments->previousPageUrl() }}" class="btn btn-xs btn-o">Previous</a>
        @endif

        @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
            @if ($page == $payments->currentPage())
                <span class="btn btn-xs btn-p">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="btn btn-xs btn-o">{{ $page }}</a>
            @endif
        @endforeach

        @if ($payments->hasMorePages())
            <a href="{{ $payments->nextPageUrl() }}" class="btn btn-xs btn-o">Next</a>
        @else
            <span class="btn btn-xs btn-o">Next</span>
        @endif
    </div>

</div>
@endif
</div>
@endsection
