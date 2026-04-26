@extends('admin.layouts.app')
@section('title', 'MyBill Loans')
@section('bc')
<a href="{{ route('admin.mybill.dashboard') }}">MyBill</a> / All Loans
@endsection
@section('content')

{{-- Subnav --}}
<div class="card mb6" style="padding:0">
  <div style="display:flex;border-bottom:1px solid #e2e8f0;overflow-x:auto">
    <a href="{{ route('admin.mybill.dashboard') }}" style="padding:14px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#64748b;white-space:nowrap"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="{{ route('admin.mybill.loans') }}" style="padding:14px 20px;text-decoration:none;font-weight:600;font-size:14px;color:var(--blue);border-bottom:2px solid var(--blue);white-space:nowrap"><i class="bi bi-list-ul"></i> All Loans</a>
    <a href="{{ route('admin.mybill.limits') }}" style="padding:14px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#64748b;white-space:nowrap"><i class="bi bi-sliders"></i> Credit Limits</a>
  </div>
</div>

<div class="card" style="padding:0">
  {{-- Toolbar --}}
  <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0">
    <form method="GET" action="{{ route('admin.mybill.loans') }}" style="display:flex;gap:12px;flex-wrap:wrap">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search loan #, client, phone..." class="fc" style="width:240px;font-size:13px">
      
      <select name="status" class="fc" style="width:140px;font-size:13px" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>Settled</option>
        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
      </select>

      <select name="category" class="fc" style="width:140px;font-size:13px" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <option value="electricity" {{ request('category') === 'electricity' ? 'selected' : '' }}>Electricity</option>
        <option value="airtime" {{ request('category') === 'airtime' ? 'selected' : '' }}>Airtime</option>
        <option value="insurance" {{ request('category') === 'insurance' ? 'selected' : '' }}>Insurance</option>
        <option value="ticket" {{ request('category') === 'ticket' ? 'selected' : '' }}>Tickets</option>
      </select>

      <select name="tier" class="fc" style="width:120px;font-size:13px" onchange="this.form.submit()">
        <option value="">All Tiers</option>
        <option value="30" {{ request('tier') === '30' ? 'selected' : '' }}>Standard (30%)</option>
        <option value="40" {{ request('tier') === '40' ? 'selected' : '' }}>No Upfront (40%)</option>
      </select>

      <button type="submit" class="btn btn-p" style="padding:8px 16px"><i class="bi bi-search"></i></button>
      <a href="{{ route('admin.mybill.loans') }}" class="btn btn-o" style="padding:8px 16px" title="Clear filters"><i class="bi bi-x-lg"></i></a>
      
      <a href="{{ route('admin.mybill.export') }}" class="btn btn-o" style="padding:8px 16px;margin-left:auto"><i class="bi bi-download"></i> Export CSV</a>
    </form>
  </div>

  {{-- Table --}}
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Loan #</th>
          <th>Client</th>
          <th>Category</th>
          <th>Provider Ref</th>
          <th>Value</th>
          <th>Payday Due</th>
          <th>Tier</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($loans as $loan)
        <tr>
          <td style="font-family:monospace;font-size:13px;font-weight:600">{{ $loan->loan_number }}</td>
          <td>
            <div style="font-weight:600;font-size:13px"><a href="{{ route('admin.users.show', $loan->user_id) }}" style="text-decoration:none;color:var(--blue)">{{ $loan->user->name ?? 'Unknown' }}</a></div>
            <div style="font-size:11px;color:#64748b">{{ $loan->user->phone ?? '' }}</div>
          </td>
          <td>
            <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569">
              {{ ucfirst($loan->bill_category) }}
            </span>
          </td>
          <td style="font-size:12px;color:#64748b;font-family:monospace">
            {{ $loan->meter_number ?? $loan->phone_number ?? $loan->policy_number ?? '-' }}
          </td>
          <td style="font-weight:600;font-size:13px">M {{ number_format($loan->bill_value, 2) }}</td>
          <td style="font-weight:600;font-size:13px;color:{{ $loan->outstanding_amount > 0 ? '#92400e' : '#065f46' }}">M {{ number_format($loan->outstanding_amount, 2) }}</td>
          <td style="font-size:12px;font-weight:600;color:{{ $loan->tier==='30' ? 'var(--blue)' : '#64748b' }}">{{ $loan->tier }}%</td>
          <td>
            <span style="font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;background:{{ $loan->status === 'settled' ? '#ecfdf5' : ($loan->status === 'active' ? '#fffbeb' : '#fef2f2') }};color:{{ $loan->status === 'settled' ? '#059669' : ($loan->status === 'active' ? '#d97706' : '#dc2626') }}">
              {{ ucfirst($loan->status) }}
            </span>
          </td>
          <td style="font-size:12px;color:#64748b">{{ $loan->created_at->format('d M y') }}</td>
          <td style="text-align:right">
            <a href="{{ route('admin.mybill.loans.show', $loan) }}" class="btn btn-sm btn-o">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="10" style="text-align:center;padding:40px;color:#64748b">
            <i class="bi bi-receipt" style="font-size:32px;display:block;margin-bottom:12px;opacity:.5"></i>
            No MyBill loans found matching your criteria.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  @if($loans->hasPages())
  <div style="padding:16px 20px;border-top:1px solid #e2e8f0">
    {{ $loans->withQueryString()->links() }}
  </div>
  @endif
</div>

@endsection
