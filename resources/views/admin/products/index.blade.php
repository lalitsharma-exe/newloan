@extends('admin.layouts.app')
@section('title', 'Loan Products')
@section('page-title', 'Product Portfolio')

@section('content')

@if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif

<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Executive Row -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
        @php
            $active   = $products->where('is_active', true)->count();
            $inactive = $products->where('is_active', false)->count();
            $totalLoans = $products->sum('loans_count');
        @endphp
        
        <div class="card" style="border-left: 4px solid var(--info); transition: transform 0.2s;">
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-fill" style="font-size: 20px;"></i>
                    </div>
                    <span style="font-size: 11px; font-weight: 800; color: var(--info); text-transform: uppercase;">Total Products</span>
                </div>
                <div style="font-size: 28px; font-weight: 900; color: var(--navy);">{{ $products->count() }}</div>
            </div>
        </div>

        <div class="card" style="border-left: 4px solid var(--ok);">
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-check-circle-fill" style="font-size: 20px;"></i>
                    </div>
                    <span style="font-size: 11px; font-weight: 800; color: var(--ok); text-transform: uppercase;">Active Suite</span>
                </div>
                <div style="font-size: 28px; font-weight: 900; color: var(--navy);">{{ $active }}</div>
            </div>
        </div>

        <div class="card" style="border-left: 4px solid var(--warn);">
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-pause-circle-fill" style="font-size: 20px;"></i>
                    </div>
                    <span style="font-size: 11px; font-weight: 800; color: var(--warn); text-transform: uppercase;">Inactive</span>
                </div>
                <div style="font-size: 28px; font-weight: 900; color: var(--navy);">{{ $inactive }}</div>
            </div>
        </div>

        <div class="card" style="border-left: 4px solid var(--navy);">
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f1f5f9; color: var(--navy); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-badge-fill" style="font-size: 20px;"></i>
                    </div>
                    <span style="font-size: 11px; font-weight: 800; color: var(--navy); text-transform: uppercase;">Market Penetration</span>
                </div>
                <div style="font-size: 28px; font-weight: 900; color: var(--navy);">{{ $totalLoans }} <small style="font-size: 13px; font-weight: 500; opacity: 0.6;">Loans</small></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card">
        <div class="card-hdr" style="padding: 20px 24px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: var(--navy); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-layers-half" style="font-size: 16px;"></i>
                </div>
                <span class="card-title">Available Loan Products</span>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-p btn-sm" style="padding: 8px 16px;">
                <i class="bi bi-plus-lg"></i> Add New Product
            </a>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="dt" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Configuration</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Financials</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Limits & Terms</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Performance</th>
                        <th style="padding: 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                        <th style="padding: 16px 24px; text-align: right; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr style="transition: background 0.2s; cursor: pointer;" onclick="window.location='{{ route('admin.products.show', $p) }}'">
                        <td style="padding: 20px 24px;">
                            <div style="font-weight: 800; color: var(--navy); font-size: 14px; margin-bottom: 4px;">{{ $p->name }}</div>
                            <div style="font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 5px;">
                                <i class="bi bi-info-circle"></i> {{ Str::limit($p->description ?? 'No description provided.', 40) }}
                            </div>
                        </td>
                        <td style="padding: 16px;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="font-size: 12px; font-weight: 700; color: var(--navy);">{{ $p->interest_rate }}% <small class="muted">Monthly</small></div>
                                <div style="font-size: 11px; color: var(--ok); font-weight: 600;">{{ ucfirst($p->interest_method) }} Balance</div>
                            </div>
                        </td>
                        <td style="padding: 16px;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="font-size: 12px; font-weight: 700; color: var(--navy);">M{{ number_format($p->min_amount, 0) }} – {{ number_format($p->max_amount, 0) }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $p->min_term_months }} – {{ $p->max_term_months }} Months</div>
                            </div>
                        </td>
                        <td style="padding: 16px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--navy); font-size: 13px;">
                                    {{ $p->loans_count }}
                                </div>
                                <span style="font-size: 11px; color: var(--muted); font-weight: 600;">Active Loans</span>
                            </div>
                        </td>
                        <td style="padding: 16px;">
                            <span class="badge {{ $p->is_active ? 'bok' : 'bs' }}" style="padding: 6px 12px; font-weight: 700; font-size: 10px; letter-spacing: 0.5px;">
                                {{ $p->is_active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </td>
                        <td style="padding: 20px 24px; text-align: right;" onclick="event.stopPropagation()">
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-xs btn-o" style="padding: 6px 10px; font-weight: 700;">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('admin.products.toggle', $p) }}" style="margin: 0;">
                                    @csrf
                                    <button class="btn btn-xs {{ $p->is_active ? 'btn-w' : 'btn-ok' }}" style="padding: 6px; border-radius: 6px;">
                                        <i class="bi bi-{{ $p->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center;">
                            <div style="color: var(--muted); font-size: 14px;">
                                <i class="bi bi-box-seam" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                                No products found. <a href="{{ route('admin.products.create') }}" style="color: var(--ok); font-weight: 700;">Add your first product.</a>
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
    .dt th { border-bottom: 1px solid var(--border); }
    .dt td { border-bottom: 1px solid #f1f5f9; }
</style>
@endsection
