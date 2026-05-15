@extends('borrower.layouts.app')
@section('title','My Dashboard')
@section('content')
@php $user = auth('borrower')->user(); @endphp

{{-- Greeting --}}
<div style="margin-bottom:24px">
  <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--navy)">
    Hello, {{ explode(' ',$user->name)[0] }} 👋
  </div>
  <div style="font-size:13px;color:var(--muted);margin-top:2px">{{ now()->format('l, d F Y') }}</div>
</div>

@if($activeLoan)
{{-- ─── 3-CARD ROW ─────────────────────────────────────── --}}
@php
  $nextInst    = $activeLoan->installments->whereIn('status',['pending','overdue','partial'])->sortBy('due_date')->first();
  $overdueInst = $activeLoan->installments->where('status','overdue')->sortBy('due_date')->first();
  $paid        = $activeLoan->total_amount - $activeLoan->outstanding_balance;
  $pct         = $activeLoan->total_amount > 0 ? round($paid / $activeLoan->total_amount * 100) : 0;
  $recentPayment = $activeLoan->payments()->where('status','verified')->latest()->first();
@endphp

<div style="display:flex;gap:18px;margin-bottom:24px;overflow-x:auto;padding-bottom:8px;scrollbar-width:none">

  {{-- CARD 1: Amount Due (Red) --}}
  <div style="flex:1;min-width:280px;background:#fff;border-radius:16px;border:2px solid #ef4444;padding:24px;display:flex;flex-direction:column;align-items:flex-start;gap:0">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
      <div style="width:36px;height:36px;background:rgba(239,68,68,.1);border-radius:9px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-exclamation-triangle-fill" style="color:#ef4444;font-size:16px"></i>
      </div>
      <div style="font-size:16px;font-weight:700;color:#991b1b">Amount Due</div>
    </div>
    <div style="width:100%;border-top:1px solid #fef2f2;margin-bottom:16px"></div>
    @if($nextInst)
    <div style="font-family:'Cormorant Garamond',serif;font-size:42px;font-weight:800;color:#ef4444;line-height:1;margin-bottom:6px">
      M{{ number_format($nextInst->outstanding_amount,0) }}
    </div>
    <div style="font-size:13px;color:#64748b;margin-bottom:20px">
      Due Date: {{ $nextInst->due_date->format('d M Y') }}
    </div>
    <a href="{{ route('borrower.payments.make') }}"
       style="display:block;width:100%;text-align:center;background:#ef4444;color:#fff;padding:11px;border-radius:10px;font-size:14px;font-weight:800;letter-spacing:.06em;text-decoration:none;transition:background .2s">
      PAY NOW
    </a>
    @else
    <div style="font-size:16px;color:#10b981;font-weight:600;margin-top:8px">✓ No payment due</div>
    @endif
  </div>

  {{-- CARD 2: Overdue Amount (Yellow) --}}
  <div style="flex:1;min-width:280px;background:#fff;border-radius:16px;border:2px solid #f59e0b;padding:24px;display:flex;flex-direction:column;align-items:flex-start">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
      <div style="width:36px;height:36px;background:rgba(245,158,11,.1);border-radius:9px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b;font-size:16px"></i>
      </div>
      <div style="font-size:16px;font-weight:700;color:#92400e">Overdue Amount</div>
    </div>
    <div style="width:100%;border-top:1px solid #fef3c7;margin-bottom:16px"></div>
    @if($overdueInst)
    <div style="font-family:'Cormorant Garamond',serif;font-size:42px;font-weight:800;color:#f59e0b;line-height:1;margin-bottom:6px">
      M{{ number_format($overdueInst->outstanding_amount,0) }}
    </div>
    <div style="font-size:13px;color:#64748b;margin-bottom:20px">
      {{ now()->diffInDays($overdueInst->due_date) }} days overdue
    </div>
    <a href="{{ route('borrower.payments.make') }}"
       style="display:block;width:100%;text-align:center;background:#f59e0b;color:#fff;padding:11px;border-radius:10px;font-size:14px;font-weight:800;letter-spacing:.06em;text-decoration:none">
      PAY NOW
    </a>
    @else
    <div style="font-size:16px;color:#10b981;font-weight:600;margin-top:8px">✓ No overdue payments</div>
    <div style="font-size:13px;color:#64748b;margin-top:4px">Great — keep it up!</div>
    @endif
  </div>

  {{-- CARD 3: Loan Summary (green) --}}
  <div style="flex:1;min-width:280px;background:#fff;border-radius:16px;border:2px solid #16a34a;padding:24px;display:flex;flex-direction:column;align-items:flex-start">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
      <div style="width:36px;height:36px;background:rgba(22,163,74,.1);border-radius:9px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-clipboard2-check-fill" style="color:#16a34a;font-size:16px"></i>
      </div>
      <div style="font-size:16px;font-weight:700;color:#14532d">Loan Summary</div>
    </div>
    <div style="width:100%;border-top:1px solid #f0fdf4;margin-bottom:16px"></div>
    <div style="display:flex;flex-direction:column;gap:8px;width:100%;margin-bottom:20px">
      <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px">
        <span style="color:#64748b">Total Loan</span>
        <span style="font-weight:700;color:#14532d">M{{ number_format($activeLoan->principal_amount,0) }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px">
        <span style="color:#64748b">Applications:</span>
        <span style="font-weight:700;color:#14532d">{{ auth('borrower')->user()->loanApplications()->count() }}</span>
      </div>
      @if($recentPayment)
      <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px">
        <span style="color:#64748b">Recent Payment</span>
        <span style="font-weight:700;color:#14532d">M{{ number_format($recentPayment->amount,0) }}</span>
      </div>
      @else
      <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px">
        <span style="color:#64748b">Outstanding</span>
        <span style="font-weight:700;color:#14532d">M{{ number_format($activeLoan->outstanding_balance,0) }}</span>
      </div>
      @endif
    </div>
    
    <a href="{{ route('borrower.loans.show', $activeLoan) }}"
       style="display:block;width:100%;text-align:center;background:#16a34a;color:#fff;padding:11px;border-radius:10px;font-size:14px;font-weight:800;letter-spacing:.06em;text-decoration:none;margin-top:auto">
      View Details
    </a>
  </div>

</div>
@else
@endif

{{-- ─── MYFLOAT SECTION ────────────────────────────────────────── --}}
@if($activeFloat)
    <div class="card" style="margin-bottom: 24px; border: 1px solid var(--navy); background: linear-gradient(135deg, #fff, #f8fafc); overflow: hidden;">
        <div style="display: flex; align-items: stretch; min-height: 100px;">
            <div style="width: 8px; background: var(--navy);"></div>
            <div style="padding: 20px; flex: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 44px; height: 44px; background: rgba(13, 27, 62, 0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--navy); font-size: 20px;">
                        <i class="bi bi-lightning-fill"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); font-weight: 700;">MyFloat Status</div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--navy);">Active Float: M {{ number_format($activeFloat->outstanding_balance, 2) }}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 24px;">
                    <div style="text-align: right;">
                        <div style="font-size: 11px; color: var(--muted); font-weight: 600;">Due Date</div>
                        <div style="font-size: 13px; font-weight: 700; color: var(--navy);">{{ $activeFloat->due_date ? $activeFloat->due_date->format('d M Y') : 'Processing' }}</div>
                    </div>
                    <a href="{{ route('borrower.float.index') }}" class="btn btn-sm btn-p" style="border-radius: 10px; padding: 10px 20px;">Manage Float</a>
                </div>
            </div>
        </div>
    </div>
@elseif($user->float_eligible && !$user->float_frozen)
    <a href="{{ route('borrower.float.index') }}" style="display: block; text-decoration: none; margin-bottom: 24px;">
        <div class="card" style="border: none; border-radius: 20px; overflow: hidden; background: #000; position: relative; min-height: 160px; display: flex; align-items: center;">
            <img src="{{ asset('emergency_float_banner_1778866066374.png') }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.6;">
            <div style="position: relative; z-index: 2; padding: 24px 32px; width: 100%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="max-width: 480px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--accent); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; margin-bottom: 12px; letter-spacing: 0.5px;">
                        <i class="bi bi-star-fill" style="font-size: 8px;"></i> EXCLUSIVE OFFER
                    </div>
                    <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #fff; line-height: 1.1;">Need quick cash? Get <span style="color: var(--accent);">M500</span> instantly.</h2>
                    <p style="font-size: 13px; color: rgba(255,255,255,0.8); margin-top: 6px;">Exclusive emergency float for you. M125 fixed fee. No paperwork.</p>
                </div>
                <div class="btn" style="background: var(--accent); color: #fff; font-weight: 700; padding: 12px 28px; border-radius: 12px; font-size: 14px; box-shadow: 0 10px 20px rgba(140, 198, 63, 0.3);">
                    Claim M500 Now <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </div>
    </a>
@endif

{{-- Recent Applications strip --}}
@if($applications->count())
<div class="card">
  <div class="card-hdr">
    <span class="card-title">My Applications</span>
    <a href="{{ route('borrower.applications.index') }}" style="font-size:12.5px;color:var(--blue);text-decoration:none;font-weight:600">View All</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>App #</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th><th style="text-align:right">Action</th></tr></thead>
      <tbody>
        @foreach($applications->take(3) as $app)
        <tr>
          <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--blue)">{{ $app->application_number }}</td>
          <td style="font-size:12.5px">{{ $app->loanProduct?->name ?? '—' }}</td>
          <td style="font-weight:700">M{{ number_format($app->requested_amount??0,0) }}</td>
          <td>
            @php $sc=['submitted'=>'bi','under_review'=>'bi','info_requested'=>'bw','on_hold'=>'bw','approved'=>'bok','declined'=>'be','disbursed'=>'bp']; @endphp
            <span class="badge {{ $sc[$app->status]??'bs' }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span>
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $app->created_at->format('d M Y') }}</td>
          <td style="text-align:right">
            <a href="{{ route('borrower.applications.show',$app) }}" style="display:inline-block;padding:6px 12px;background:var(--navy);color:#fff;border-radius:6px;font-size:12px;text-decoration:none;font-weight:600;">View</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection
