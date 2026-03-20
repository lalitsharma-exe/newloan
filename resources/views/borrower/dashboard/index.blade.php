@extends('borrower.layouts.app')
@section('title','My Dashboard')
@section('content')
@php $user = auth('borrower')->user(); @endphp

<div style="margin-bottom:22px">
  <div style="font-size:22px;font-weight:800">Hello, {{ explode(' ',$user->name)[0] }} 👋</div>
  <div style="font-size:13px;color:var(--muted);margin-top:2px">{{ now()->format('l, d F Y') }}</div>
</div>

{{-- Active Loan Card --}}
@if($activeLoan)
<div style="background:linear-gradient(135deg,#0f2318,#1a5c2e);border-radius:18px;padding:24px;color:#fff;margin-bottom:20px">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px">
    <div>
      <div style="font-size:11px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">Active Loan</div>
      <div style="font-size:28px;font-weight:900">M{{ number_format($activeLoan->outstanding_balance,2) }}</div>
      <div style="font-size:13px;color:rgba(255,255,255,.7);margin-top:2px">Outstanding balance &nbsp;·&nbsp; {{ $activeLoan->loanProduct?->name }}</div>
    </div>
    <div style="text-align:right">
      <div style="font-size:11px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">Next Payment</div>
      @if($nextInst)
      <div style="font-size:20px;font-weight:800">M{{ number_format($nextInst->outstanding_amount,2) }}</div>
      <div style="font-size:13px;color:rgba(255,255,255,.7);margin-top:2px">Due {{ $nextInst->due_date->format('d M Y') }}</div>
      <div style="margin-top:8px;display:flex;gap:8px;justify-content:flex-end">
        <a href="{{ route('borrower.loans.show',$activeLoan) }}" style="background:rgba(255,255,255,.15);color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">View Loan</a>
        <a href="{{ route('borrower.payments.make') }}" style="background:#fff;color:#1a5c2e;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none">Pay Now</a>
      </div>
      @else
      <div style="font-size:14px;color:rgba(255,255,255,.8)">All payments up to date ✓</div>
      @endif
    </div>
  </div>
  @php
    $paid    = $activeLoan->total_amount - $activeLoan->outstanding_balance;
    $pct     = $activeLoan->total_amount > 0 ? round($paid / $activeLoan->total_amount * 100) : 0;
  @endphp
  <div style="margin-top:18px">
    <div style="display:flex;justify-content:space-between;font-size:11px;color:rgba(255,255,255,.6);margin-bottom:6px">
      <span>Paid off</span><span>{{ $pct }}%</span>
    </div>
    <div style="background:rgba(255,255,255,.2);border-radius:99px;height:6px">
      <div style="background:#4caf69;height:100%;width:{{ $pct }}%;border-radius:99px;transition:width .5s"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:rgba(255,255,255,.5);margin-top:5px">
      <span>M{{ number_format($paid,0) }} paid</span>
      <span>M{{ number_format($activeLoan->total_amount,0) }} total</span>
    </div>
  </div>
</div>
@else
{{-- No active loan CTA --}}
<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:18px;padding:32px 24px;text-align:center;margin-bottom:20px">
  <div style="font-size:40px;margin-bottom:12px">💰</div>
  <div style="font-size:20px;font-weight:800;color:#0f172a;margin-bottom:8px">Need a Loan?</div>
  <div style="font-size:14px;color:#64748b;margin-bottom:20px">Apply in minutes. Quick approval, funds disbursed directly to you.</div>
  <a href="{{ route('borrower.apply.start') }}" style="display:inline-flex;align-items:center;gap:8px;background:#1a5c2e;color:#fff;padding:12px 28px;border-radius:12px;font-size:14px;font-weight:700;text-decoration:none"><i class="bi bi-plus-circle-fill"></i> Apply for a Loan</a>
</div>
@endif

{{-- Stats row --}}
<div class="stat-grid">
  <div class="stat"><div class="stat-val">{{ $allLoans->count() }}</div><div class="stat-lbl">Total Loans</div></div>
  <div class="stat"><div class="stat-val">{{ $applications->count() }}</div><div class="stat-lbl">Applications</div></div>
  <div class="stat"><div class="stat-val">M{{ number_format($recentPay->sum('amount'),0) }}</div><div class="stat-lbl">Recent Payments</div></div>
  <div class="stat"><div class="stat-val" style="color:{{ $unread ? 'var(--err)' : 'var(--ok)' }}">{{ $unread ?: '✓' }}</div><div class="stat-lbl">{{ $unread ? 'Unread Notifications' : 'All Read' }}</div></div>
</div>

{{-- Recent Applications --}}
@if($applications->count())
<div class="card">
  <div class="card-hdr">
    <span class="card-title">My Applications</span>
    <a href="{{ route('borrower.applications.index') }}" style="font-size:12.5px;color:var(--p);text-decoration:none;font-weight:600">View All</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>App #</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        @foreach($applications as $app)
        <tr onclick="window.location='{{ route('borrower.applications.show',$app) }}'" style="cursor:pointer">
          <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $app->application_number }}</td>
          <td style="font-size:12.5px">{{ $app->loanProduct?->name ?? '—' }}</td>
          <td style="font-weight:700">M{{ number_format($app->requested_amount??0,0) }}</td>
          <td>
            @php $sc = ['submitted'=>'bi','under_review'=>'bi','info_requested'=>'bw','on_hold'=>'bw','approved'=>'bok','declined'=>'be','disbursed'=>'bp']; @endphp
            <span class="badge {{ $sc[$app->status]??'bs' }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span>
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $app->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Recent Payments --}}
@if($recentPay->count())
<div class="card">
  <div class="card-hdr">
    <span class="card-title">Recent Payments</span>
    <a href="{{ route('borrower.payments.index') }}" style="font-size:12.5px;color:var(--p);text-decoration:none;font-weight:600">View All</a>
  </div>
  @foreach($recentPay as $pay)
  <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
    <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,.1);display:flex;align-items:center;justify-content:center;color:var(--ok);font-size:16px;flex-shrink:0"><i class="bi bi-check-circle-fill"></i></div>
    <div style="flex:1"><div style="font-size:13.5px;font-weight:600">M{{ number_format($pay->amount,2) }}</div><div style="font-size:11.5px;color:var(--muted)">{{ ucfirst(str_replace('_',' ',$pay->method)) }} &nbsp;·&nbsp; {{ $pay->created_at->format('d M Y') }}</div></div>
    <a href="{{ route('borrower.payments.receipt',$pay) }}" style="font-size:12px;color:var(--p);text-decoration:none;font-weight:600">Receipt</a>
  </div>
  @endforeach
</div>
@endif

@endsection
