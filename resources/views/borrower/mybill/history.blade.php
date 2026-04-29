@extends('borrower.layouts.app')
@section('title','MyBill — History')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px">
    <div>
        <h2 style="font-size:26px; font-weight:900; color:#0f172a; margin:0; letter-spacing:-1px">Transaction History</h2>
        <div style="font-size:13px; color:#64748b; margin-top:4px">Account Limit: <strong style="color:#0f172a">M {{ number_format($limit->total_limit, 2) }}</strong></div>
    </div>
    <a href="{{ route('borrower.mybill.index') }}" class="glass-btn-circle">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

{{-- Filter Categories --}}
<div style="display:flex; gap:12px; margin-bottom:30px; overflow-x:auto; padding-bottom:10px; scrollbar-width:none">
    @foreach(['' => 'All Services', 'electricity' => 'Electricity', 'airtime' => 'Airtime & Data', 'insurance' => 'Insurance', 'ticket' => 'Tickets'] as $val => $label)
    <a href="{{ route('borrower.mybill.history', array_merge(request()->only('status'), $val ? ['category' => $val] : [])) }}"
       class="glass-pill {{ (request('category', '') === $val) ? 'active' : '' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Transactions List (Light Glass) --}}
<div class="light-glass-card" style="padding:0; overflow:hidden">
    <div style="padding:24px; border-bottom:1px solid rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.4)">
        <span style="font-size:14px; font-weight:900; color:#0f172a; text-transform:uppercase; letter-spacing:1px">All Transactions</span>
        <div style="display:flex; gap:16px">
            @foreach(['' => 'All', 'active' => 'Active', 'settled' => 'Settled'] as $val => $label)
                <a href="{{ route('borrower.mybill.history', array_merge(request()->only('category'), $val ? ['status' => $val] : [])) }}" 
                   style="font-size:11px; font-weight:800; color:{{ request('status', '') === $val ? '#3b82f6' : '#94a3b8' }}; text-decoration:none; text-transform:uppercase; letter-spacing:1px; transition:all 0.3s">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div style="padding:10px">
        @forelse($loans as $loan)
        <a href="{{ route('borrower.mybill.show', $loan) }}" class="glass-txn-item">
            @php
              $colors = ['electricity'=>'#f59e0b', 'airtime'=>'#0ea5e9', 'insurance'=>'#8b5cf6', 'ticket'=>'#ec4899'];
              $c = $colors[$loan->bill_category] ?? '#64748b';
            @endphp
            <div class="txn-icon-wrap" style="background: {{ $c }}15; color: {{ $c }}">
                <i class="bi bi-{{ $loan->category_icon }}"></i>
            </div>
            <div style="flex:1">
                <div style="font-weight:800; font-size:15px; color:#1e293b">{{ ucfirst($loan->bill_category) }}</div>
                <div style="font-size:12px; color:#94a3b8; font-weight:600">{{ $loan->created_at->format('d M Y, H:i') }} · #{{ $loan->loan_number }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:900; font-size:17px; color:#0f172a">M {{ number_format($loan->bill_value, 2) }}</div>
                <div style="font-size:10px; font-weight:900; color:{{ $loan->status === 'settled' ? '#10b981' : '#f59e0b' }}; text-transform:uppercase; letter-spacing:1px">{{ $loan->status }}</div>
            </div>
        </a>
        @empty
        <div style="padding:80px 20px; text-align:center; color:#94a3b8">
            <i class="bi bi-search" style="font-size:32px; display:block; margin-bottom:12px; opacity:0.3"></i>
            <div style="font-size:14px; font-weight:600">No transactions found</div>
            <div style="font-size:12px">Try adjusting your filters</div>
        </div>
        @endforelse
    </div>
</div>

@if($loans->hasPages())
<div style="margin-top:24px">
    {{ $loans->withQueryString()->links() }}
</div>
@endif

<style>
.light-glass-card {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(0,0,0,0.05); border-radius: 32px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.04);
}
.glass-btn-circle {
    width: 44px; height: 44px; border-radius: 50%;
    background: #fff; border: 1px solid rgba(0,0,0,0.08);
    display: flex; align-items: center; justify-content: center;
    color: #64748b; text-decoration: none; transition: all 0.3s;
}
.glass-btn-circle:hover { color: #0f172a; transform: scale(1.1); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

.glass-pill {
    padding: 12px 22px; border-radius: 100px; font-size: 13px; font-weight: 800;
    text-decoration: none; white-space: nowrap; transition: all 0.3s;
    background: #fff; border: 1px solid rgba(0,0,0,0.08); color: #64748b;
}
.glass-pill:hover { background: #f8fafc; color: #0f172a; }
.glass-pill.active { background: #0f172a; color: #fff; border-color: #0f172a; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

.glass-txn-item {
    display: flex; align-items: center; gap: 16px; padding: 18px 20px;
    border-radius: 20px; text-decoration: none; transition: all 0.3s; margin-bottom: 6px;
}
.glass-txn-item:hover { background: #fff; transform: scale(1.01); box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
.txn-icon-wrap {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 22px;
}
</style>

@endsection
