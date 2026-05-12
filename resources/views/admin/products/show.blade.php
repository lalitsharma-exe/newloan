@extends('admin.layouts.app')
@section('title', $product->name)
@section('page-title', 'Product Intelligence')
@section('bc')
<a href="{{ route('admin.products.index') }}">Products</a> / {{ $product->name }}
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 350px 1fr; gap: 24px; align-items: start;">
    
    <!-- Sidebar: Product Identity -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card" style="overflow: hidden; border-top: 4px solid var(--navy);">
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div style="background: var(--navy); color: #fff; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-fill" style="font-size: 24px;"></i>
                    </div>
                    <span class="badge {{ $product->is_active ? 'bok' : 'bs' }}" style="padding: 6px 12px; font-weight: 800;">
                        {{ $product->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </div>
                
                <h2 style="font-size: 22px; font-weight: 900; color: var(--navy); margin-bottom: 8px;">{{ $product->name }}</h2>
                <p style="font-size: 13px; color: var(--muted); line-height: 1.6; margin-bottom: 24px;">{{ $product->description ?? 'No detailed description provided for this product.' }}</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                    @foreach([
                        'Interest Rate'  => $product->interest_rate.'% <small class="muted">/ mo ('.$product->interest_method.')</small>',
                        'Initiation Fee' => $product->initiation_fee_rate.'% <small class="muted">of principal</small>',
                        'Monthly Admin'  => 'M'.number_format($product->admin_fee_fixed, 2),
                        'Loan Range'     => 'M'.number_format($product->min_amount, 0).' - '.number_format($product->max_amount, 0),
                        'Term Range'     => $product->min_term_months.' - '.$product->max_term_months.' Months',
                    ] as $label => $val)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                        <span style="color: var(--muted); font-weight: 500;">{{ $label }}</span>
                        <span style="font-weight: 800; color: var(--navy);">{!! $val !!}</span>
                    </div>
                    @endforeach
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-p" style="flex: 1; justify-content: center; padding: 10px;">
                        <i class="bi bi-pencil"></i> Edit Suite
                    </a>
                    <form method="POST" action="{{ route('admin.products.toggle', $product) }}" style="margin: 0;">
                        @csrf
                        <button class="btn {{ $product->is_active ? 'btn-w' : 'btn-ok' }}" style="padding: 10px; width: 42px; border-radius: 8px;">
                            <i class="bi bi-{{ $product->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dynamic Intelligence -->
        <div class="card" style="background: linear-gradient(135deg, var(--navy) 0%, #001f3f 100%); color: #fff; border: none;">
            <div class="card-body" style="padding: 24px;">
                <div style="font-weight: 800; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; letter-spacing: 0.5px;">
                    <i class="bi bi-lightning-charge-fill" style="color: var(--warn);"></i> PERFORMANCE SNAPSHOT
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                        <div style="font-size: 10px; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Active Loans</div>
                        <div style="font-size: 20px; font-weight: 900;">{{ $product->loans()->where('status','active')->count() }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                        <div style="font-size: 10px; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Total Volume</div>
                        <div style="font-size: 20px; font-weight: 900;">{{ $product->loans_count }}</div>
                    </div>
                </div>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <div style="font-size: 10px; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Principal Disbursed</div>
                    <div style="font-size: 24px; font-weight: 900; color: var(--ok);">M{{ number_format($product->loans()->sum('principal_amount'), 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Recent Activity -->
    <div class="card">
        <div class="card-hdr" style="padding: 20px 24px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #f1f5f9; color: var(--navy); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-clock-history" style="font-size: 16px;"></i>
                </div>
                <span class="card-title">Recent Activity Log</span>
            </div>
            <a href="{{ route('admin.loans.index', ['product' => $product->id]) }}" class="btn btn-o btn-sm" style="font-weight: 700;">
                View All Activity
            </a>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="dt" style="width: 100%;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase;">Loan Ref</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase;">Borrower</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase;">Principal</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase;">Outstanding</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase;">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; text-align: right;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->loans()->latest()->limit(15)->get() as $l)
                    <tr style="transition: background 0.2s; cursor: pointer;" onclick="window.location='{{ route('admin.loans.show', $l) }}'">
                        <td style="padding: 16px 24px;">
                            <span style="font-weight: 800; color: var(--info); font-size: 13px;">{{ $l->loan_number }}</span>
                        </td>
                        <td style="padding: 16px;">
                            <div style="font-weight: 700; color: var(--navy); font-size: 13px;">{{ $l->user->name ?? '—' }}</div>
                        </td>
                        <td style="padding: 16px; font-weight: 600;">M{{ number_format($l->principal_amount, 2) }}</td>
                        <td style="padding: 16px;">
                            <span style="font-weight: 800; color: {{ $l->outstanding_balance > 0 ? 'var(--warn)' : 'var(--ok)' }}">
                                M{{ number_format($l->outstanding_balance, 2) }}
                            </span>
                        </td>
                        <td style="padding: 16px;">
                            <span class="badge {{ $l->status === 'active' ? 'bok' : ($l->status === 'overdue' ? 'be' : 'bs') }}" style="font-weight: 800; font-size: 10px;">
                                {{ strtoupper(str_replace('_', ' ', $l->status)) }}
                            </span>
                        </td>
                        <td style="padding: 16px 24px; text-align: right; color: var(--muted); font-size: 12px;">
                            {{ $l->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 60px; text-align: center;">
                            <div style="color: var(--muted); font-size: 14px;">
                                <i class="bi bi-inbox" style="font-size: 40px; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
                                No loan activity recorded for this product yet.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    tr:hover { background: #f1f5f9; }
    .dt td { border-bottom: 1px solid #f1f5f9; }
</style>
@endsection
