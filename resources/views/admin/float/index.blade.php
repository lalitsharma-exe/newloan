@extends('admin.layouts.app')

@section('title', 'MyLoan Float Management')
@section('page-title', 'MyLoan Float Queue')

@section('content')
<div class="g4 mb4">
    <div class="sc">
        <div class="si p"><i class="bi bi-clock-history"></i></div>
        <div><div class="sv">{{ number_format($stats['pending']) }}</div><div class="sl">Pending Approvals</div></div>
    </div>
    <div class="sc">
        <div class="si ok"><i class="bi bi-check2-circle"></i></div>
        <div><div class="sv">{{ number_format($stats['active']) }}</div><div class="sl">Active Float Book</div></div>
    </div>
    <div class="sc">
        <div class="si e"><i class="bi bi-exclamation-triangle"></i></div>
        <div><div class="sv" style="color:var(--err)">{{ number_format($stats['overdue']) }}</div><div class="sl">Overdue Accounts</div></div>
    </div>
    <div class="sc">
        <div class="si w"><i class="bi bi-cash-stack"></i></div>
        <div><div class="sv">M{{ number_format($stats['total_collected'], 0) }}</div><div class="sl">Total Collected</div></div>
    </div>
</div>

<div class="card">
    <div class="card-hdr" style="display:flex; justify-content:space-between; align-items:center">
        <span class="card-title">Float Application Queue</span>
        <div class="flex gap2">
            <form action="{{ route('admin.float.index') }}" method="GET" class="flex gap2">
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['pending', 'approved', 'disbursed', 'due', 'overdue', 'closed', 'rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table class="dt">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Repayment</th>
                    <th>Applied</th>
                    <th>Affordability</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td>
                        <div class="flex aic gap2">
                            <div class="av av-sm">{{ strtoupper(substr($r->user->name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:700">{{ $r->user->name }}</div>
                                <div class="muted" style="font-size:11px">{{ $r->user->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $colors = [
                                'pending' => 'var(--warn)',
                                'approved' => 'var(--ok)',
                                'disbursed' => 'var(--p)',
                                'due' => 'var(--navy)',
                                'overdue' => 'var(--err)',
                                'closed' => 'var(--muted)',
                                'rejected' => 'var(--err)',
                            ];
                            $c = $colors[$r->status] ?? 'var(--muted)';
                        @endphp
                        <span class="badge" style="background:{{ $c }}; color:#fff">{{ ucfirst($r->status) }}</span>
                    </td>
                    <td>M{{ number_format($r->principal_amount, 2) }}</td>
                    <td style="font-weight:800; color:var(--p)">M{{ number_format($r->outstanding_balance, 2) }}</td>
                    <td>{{ $r->applied_at->diffForHumans() }}</td>
                    <td>
                        <span class="badge {{ $r->affordability_result == 'pass' ? 'bok' : 'berr' }}">
                            {{ strtoupper($r->affordability_result) }} (M{{ number_format($r->disposable_income, 0) }})
                        </span>
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.float.show', $r) }}" class="btn btn-sm btn-o">Review</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:40px; text-align:center" class="muted">No float records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:15px">
        {{ $records->links() }}
    </div>
</div>
@endsection
