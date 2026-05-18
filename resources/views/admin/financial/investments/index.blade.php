@extends('admin.layouts.app')
@section('title', 'Investment Tranches')
@section('page-title', 'Capital Investments Ledger')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px">
    
    <!-- Top Summary Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); border-radius: 20px; padding: 28px; color: white; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);">
        <div style="position: absolute; right: -20px; bottom: -20px; font-size: 140px; opacity: 0.05; color: white;"><i class="bi bi-wallet2"></i></div>
        <div style="position: relative; z-index: 1;">
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 8px;">Capital Pool Registry</div>
            <h2 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Active <span style="color: #60a5fa;">Investment Tranches</span></h2>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 8px; max-width: 500px;">Review and manage active funding contracts, generated monthly accruals, and early termination notices.</p>
        </div>
        <div style="text-align: right; position: relative; z-index: 1; display:flex; gap:10px">
            <a href="{{ route('admin.investments.investors.index') }}" class="btn btn-light" style="font-weight:700; color:#1e3a8a"><i class="bi bi-people-fill"></i> View Partners</a>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:20px">
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 20px 24px;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:8px">Active Principal Pool</div>
            <div style="font-size:24px; font-weight:800; color:#1e293b">LSL {{ number_format(\App\Models\Investment::where('status', 'active')->sum('principal_cents') / 100, 2) }}</div>
        </div>
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 20px 24px;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:8px">Unpaid Accrued Interest</div>
            <div style="font-size:24px; font-weight:800; color:#4f46e5">LSL {{ number_format(\App\Models\InvestmentAccrual::where('status', 'posted')->sum('interest_cents') / 100, 2) }}</div>
        </div>
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 20px 24px;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:8px">Total Repayable Liability</div>
            <div style="font-size:24px; font-weight:800; color:#ef4444">LSL {{ number_format((\App\Models\Investment::where('status', 'active')->sum('principal_cents') + \App\Models\InvestmentAccrual::where('status', 'posted')->sum('interest_cents')) / 100, 2) }}</div>
        </div>
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 20px 24px;">
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:8px">Active Agreements</div>
            <div style="font-size:24px; font-weight:800; color:#10b981">{{ \App\Models\Investment::where('status', 'active')->count() }} Contracts</div>
        </div>
    </div>

    <!-- Filters Table Card -->
    <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: 16px;">
        <div class="card-hdr" style="padding: 20px 24px; background: #fff; border-bottom: 1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center">
            <span class="card-title" style="font-size: 15px; font-weight: 800; color: #1e293b;">Active Capital Ledger</span>
            
            <form action="{{ route('admin.investments.index') }}" method="GET" style="display:flex; gap:10px">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contract ref or name..." style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
                <select name="status" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
                    <option value="">-- All Statuses --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="termination_pending" {{ request('status') === 'termination_pending' ? 'selected' : '' }}>Termination Pending</option>
                    <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="repaid" {{ request('status') === 'repaid' ? 'selected' : '' }}>Repaid</option>
                    <option value="matured" {{ request('status') === 'matured' ? 'selected' : '' }}>Matured</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary" style="font-weight:700">Filter</button>
            </form>
        </div>
        
        <div style="overflow-x:auto">
            <table class="dt" style="width:100%">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Contract Reference</th>
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Investor / Partner</th>
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Principal</th>
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Accrued Interest</th>
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Maturity</th>
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                        <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investments as $inv)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 18px 24px;">
                            <a href="{{ route('admin.investments.show', $inv->id) }}" style="font-weight: 800; color: #1e3a8a; font-size: 14px; text-decoration: none;">{{ $inv->contract_ref }}</a>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Injected: {{ $inv->investment_date->format('d M, Y') }}</div>
                        </td>
                        <td style="padding: 18px 24px;">
                            <a href="{{ route('admin.investments.investors.show', $inv->investor->id) }}" style="font-weight: 700; color: #475569; font-size: 13.5px; text-decoration: none;">{{ $inv->investor->full_name }}</a>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                @if($inv->investor->investor_type === 'MD')
                                    <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 2px 6px; font-size: 9px; border-radius: 4px;">MD (5.0%)</span>
                                @else
                                    <span class="badge" style="background: rgba(14, 116, 144, 0.1); color: #0e7490; padding: 2px 6px; font-size: 9px; border-radius: 4px;">PUB (1.5%)</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 18px 24px; text-align: right; font-weight: 800; color: #1e293b; font-size: 13.5px;">
                            LSL {{ number_format($inv->principal, 2) }}
                        </td>
                        <td style="padding: 18px 24px; text-align: right; font-weight: 700; color: #10b981; font-size: 13.5px;">
                            LSL {{ number_format($inv->accruals()->where('status', 'posted')->sum('interest_cents') / 100, 2) }}
                        </td>
                        <td style="padding: 18px 24px; font-size: 12.5px; color: #475569;">
                            {{ $inv->maturity_date->format('d M, Y') }}
                        </td>
                        <td style="padding: 18px 24px;">
                            @php
                                $col = $inv->status === 'active' ? 'success' : ($inv->status === 'repaid' ? 'info' : ($inv->status === 'termination_pending' ? 'warning' : 'secondary'));
                            @endphp
                            <span class="badge bg-{{ $col }}" style="font-size: 10px; padding: 5px 10px; border-radius: 30px; color: #fff;">{{ strtoupper($inv->status) }}</span>
                        </td>
                        <td style="padding: 18px 24px; text-align: right;">
                            <a href="{{ route('admin.investments.show', $inv->id) }}" class="btn btn-sm btn-light" style="font-weight:700;"><i class="bi bi-eye"></i> Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8; font-size: 13.5px;">
                            <i class="bi bi-wallet2" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                            No investment contracts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($investments->hasPages())
        <div style="padding: 20px 24px; border-top: 1px solid #f1f5f9;">
            {{ $investments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
