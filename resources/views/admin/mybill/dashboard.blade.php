@extends('admin.layouts.app')
@section('title', 'MyBill Dashboard')
@section('bc')
<a href="{{ route('admin.mybill.dashboard') }}">MyBill</a> / Dashboard
@endsection
@section('content')

{{-- Subnav --}}
<div class="card mb6" style="padding:0">
  <div style="display:flex;border-bottom:1px solid #e2e8f0;overflow-x:auto">
    <a href="{{ route('admin.mybill.dashboard') }}" style="padding:14px 20px;text-decoration:none;font-weight:600;font-size:14px;color:var(--blue);border-bottom:2px solid var(--blue);white-space:nowrap"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="{{ route('admin.mybill.loans') }}" style="padding:14px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#64748b;white-space:nowrap"><i class="bi bi-list-ul"></i> All Loans</a>
    <a href="{{ route('admin.mybill.limits') }}" style="padding:14px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#64748b;white-space:nowrap"><i class="bi bi-sliders"></i> Credit Limits</a>
  </div>
</div>

{{-- Top Stats Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
  <div class="card" style="padding:20px;border-top:4px solid var(--blue)">
    <div style="font-size:13px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:8px">Total Disbursed</div>
    <div style="font-size:28px;font-weight:800;color:#0f172a">M {{ number_format($stats['total_bill_value'], 2) }}</div>
    <div style="font-size:12px;color:#64748b;margin-top:4px">{{ number_format($stats['total_loans']) }} total purchases</div>
  </div>
  
  <div class="card" style="padding:20px;border-top:4px solid #f59e0b">
    <div style="font-size:13px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:8px">Outstanding Balance</div>
    <div style="font-size:28px;font-weight:800;color:#92400e">M {{ number_format($stats['total_outstanding'], 2) }}</div>
    <div style="font-size:12px;color:#64748b;margin-top:4px">Across {{ number_format($stats['active_loans']) }} active loans</div>
  </div>

  <div class="card" style="padding:20px;border-top:4px solid #10b981">
    <div style="font-size:13px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:8px">Fee Revenue Collected</div>
    <div style="font-size:28px;font-weight:800;color:#065f46">M {{ number_format($stats['total_revenue'], 2) }}</div>
    <div style="font-size:12px;color:#64748b;margin-top:4px">From {{ number_format($stats['settled_loans']) }} settled loans</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">
  
  {{-- Category Breakdown --}}
  <div class="card" style="padding:20px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:20px"><i class="bi bi-pie-chart-fill" style="color:var(--blue)"></i> Purchases by Category</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      @php
        $cats = [
          ['electricity', 'Electricity', 'lightning-charge-fill', '#f59e0b'],
          ['airtime', 'Airtime', 'phone-fill', '#06b6d4'],
          ['insurance', 'Insurance', 'shield-fill', '#8b5cf6'],
          ['ticket', 'Tickets', 'ticket-perforated-fill', '#ec4899']
        ];
        $totalLoans = max(1, $stats['total_loans']);
      @endphp
      
      @foreach($cats as [$key, $label, $icon, $color])
      @php $count = $stats['by_category'][$key] ?? 0; $pct = round(($count / $totalLoans) * 100); @endphp
      <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
          <div style="width:32px;height:32px;border-radius:8px;background:{{ $color }}15;color:{{ $color }};display:flex;align-items:center;justify-content:center">
            <i class="bi bi-{{ $icon }}"></i>
          </div>
          <div style="font-weight:600;font-size:14px">{{ $label }}</div>
          <div style="margin-left:auto;font-weight:800;font-size:18px">{{ number_format($count) }}</div>
        </div>
        <div style="background:#f1f5f9;height:6px;border-radius:3px;overflow:hidden">
          <div style="background:{{ $color }};height:100%;width:{{ $pct }}%"></div>
        </div>
        <div style="text-align:right;font-size:11px;color:#64748b;margin-top:4px">{{ $pct }}% of total</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Tier Distribution --}}
  <div class="card" style="padding:20px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:20px"><i class="bi bi-layers-fill" style="color:var(--blue)"></i> Tier Selection</h3>
    
    @php
      $t30 = $stats['by_tier']['30'] ?? 0;
      $t40 = $stats['by_tier']['40'] ?? 0;
      $tTot = max(1, $t30 + $t40);
    @endphp
    
    <div style="margin-bottom:24px">
      <div style="display:flex;justify-content:space-between;margin-bottom:8px">
        <span style="font-size:13px;font-weight:600;color:var(--blue)">Standard (30%)</span>
        <span style="font-size:13px;font-weight:800">{{ number_format($t30) }}</span>
      </div>
      <div style="background:#f1f5f9;height:8px;border-radius:4px;overflow:hidden">
        <div style="background:var(--blue);height:100%;width:{{ ($t30/$tTot)*100 }}%"></div>
      </div>
      <div style="font-size:11px;color:#64748b;margin-top:4px">Client pays 10% upfront</div>
    </div>

    <div>
      <div style="display:flex;justify-content:space-between;margin-bottom:8px">
        <span style="font-size:13px;font-weight:600;color:#64748b">No-Upfront (40%)</span>
        <span style="font-size:13px;font-weight:800">{{ number_format($t40) }}</span>
      </div>
      <div style="background:#f1f5f9;height:8px;border-radius:4px;overflow:hidden">
        <div style="background:#94a3b8;height:100%;width:{{ ($t40/$tTot)*100 }}%"></div>
      </div>
      <div style="font-size:11px;color:#64748b;margin-top:4px">0% upfront, fully financed</div>
    </div>
  </div>
</div>

{{-- Recent Transactions --}}
<div class="card" style="padding:0">
  <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
    <h3 style="font-size:15px;font-weight:700;margin:0">Recent Transactions</h3>
    <a href="{{ route('admin.mybill.loans') }}" class="btn btn-sm btn-o">View All</a>
  </div>
  
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Loan #</th>
          <th>Client</th>
          <th>Category</th>
          <th>Bill Value</th>
          <th>Tier</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($recent as $loan)
        <tr>
          <td style="font-family:monospace;font-size:13px;font-weight:600">{{ $loan->loan_number }}</td>
          <td>
            <div style="font-weight:600;font-size:13px">{{ $loan->user->name ?? 'Unknown' }}</div>
            <div style="font-size:11px;color:#64748b">{{ $loan->user->phone ?? '' }}</div>
          </td>
          <td>
            <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569">
              {{ ucfirst($loan->bill_category) }}
            </span>
          </td>
          <td style="font-weight:600;font-size:13px">M {{ number_format($loan->bill_value, 2) }}</td>
          <td style="font-size:12px;font-weight:600;color:{{ $loan->tier==='30' ? 'var(--blue)' : '#64748b' }}">{{ $loan->tier }}%</td>
          <td>
            <span style="font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;background:{{ $loan->status === 'settled' ? '#ecfdf5' : ($loan->status === 'active' ? '#fffbeb' : '#fef2f2') }};color:{{ $loan->status === 'settled' ? '#059669' : ($loan->status === 'active' ? '#d97706' : '#dc2626') }}">
              {{ ucfirst($loan->status) }}
            </span>
          </td>
          <td style="font-size:12px;color:#64748b">{{ $loan->created_at->format('d M y, H:i') }}</td>
          <td style="text-align:right">
            <a href="{{ route('admin.mybill.loans.show', $loan) }}" class="btn btn-sm btn-o">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;padding:30px;color:#64748b;font-size:13px">No transactions recorded yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
