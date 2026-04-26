@extends('admin.layouts.app')
@section('title', 'Referral Management')
@section('page-title', 'Referral Management')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / Referrals
@endsection

@section('content')
@push('styles')
<style>
.ref-filter-bar {
    display: flex;
    gap: 12px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.ref-filter-bar .fg { margin-bottom: 0; flex: 1; min-width: 160px; }
.ref-filter-bar .fg:last-child { flex: 0 0 auto; }

.ref-stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}

/* Payout dropdown */
.pay-dropdown {
    position: relative;
    display: inline-block;
}
.pay-dropdown-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 8px;
    border: none;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    background: var(--ok);
    color: #fff;
    font-family: 'Inter', sans-serif;
    transition: all .2s;
}
.pay-dropdown-btn:hover { background: #059669; }
.pay-dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 12px 36px rgba(0,0,0,.12);
    min-width: 220px;
    z-index: 100;
    padding: 8px;
    animation: mIn .15s ease;
}
.pay-dropdown.open .pay-dropdown-menu { display: block; }
.pay-opt {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background .15s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-family: 'Inter', sans-serif;
}
.pay-opt:hover { background: #f8fafc; }
.pay-opt-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.pay-opt-title { font-size: 12.5px; font-weight: 600; color: var(--dark); }
.pay-opt-desc { font-size: 11px; color: var(--muted); margin-top: 1px; }
.pay-sep { height: 1px; background: var(--border); margin: 4px 0; }

.payout-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--muted);
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 4px;
    margin-top: 3px;
}

@media (max-width: 768px) {
    .ref-stat-row { grid-template-columns: repeat(2, 1fr); }
    .ref-filter-bar { flex-direction: column; }
    .ref-filter-bar .fg { min-width: 100%; }
}
</style>
@endpush

{{-- ── STATS ROW ───────────────────────────────────────── --}}
<div class="ref-stat-row">
    <div class="sc">
        <div class="si p"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="sv">{{ $referrals->total() }}</div>
            <div class="sl">Total Referrals</div>
        </div>
    </div>
    <div class="sc">
        <div class="si w"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="sv">{{ $referrals->where('status', 'pending')->count() + $referrals->where('status', 'validated')->count() }}</div>
            <div class="sl">Pending / Validated</div>
        </div>
    </div>
    <div class="sc">
        <div class="si ok"><i class="bi bi-check-circle-fill"></i></div>
        <div>
            <div class="sv">{{ $referrals->where('status', 'qualified')->count() }}</div>
            <div class="sl">Qualified (Awaiting Payout)</div>
        </div>
    </div>
    <div class="sc">
        <div class="si s"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="sv">{{ $referrals->where('status', 'paid')->count() }}</div>
            <div class="sl">Paid Out</div>
        </div>
    </div>
</div>

{{-- ── FILTERS ─────────────────────────────────────────── --}}
<div class="card mb4">
    <div class="card-body">
        <form action="{{ route('admin.referrals.index') }}" method="GET" class="ref-filter-bar">
            <div class="fg">
                <label class="fl">Search</label>
                <input type="text" name="search" class="fc" placeholder="Referrer or customer name/phone..." value="{{ request('search') }}">
            </div>
            <div class="fg">
                <label class="fl">Status</label>
                <select name="status" class="fc">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validated (Disbursed)</option>
                    <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>Qualified (1st Inst Paid)</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="fg">
                <button type="submit" class="btn btn-p" style="width:100%;">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── TABLE ───────────────────────────────────────────── --}}
<div class="card">
    <div class="card-hdr">
        <span class="card-title">All Referrals</span>
        <span class="muted">{{ $referrals->total() }} record{{ $referrals->total() !== 1 ? 's' : '' }}</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dt">
            <thead>
                <tr>
                    <th>Referrer</th>
                    <th>Referred Customer</th>
                    <th>Linked Loan</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $ref)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="av av-sm">{{ strtoupper(substr($ref->referrer->name ?? 'R', 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:600;">{{ $ref->referrer->name }}</div>
                                <div class="muted" style="font-size:12px;">{{ $ref->referrer->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="av av-sm" style="background:linear-gradient(135deg,#059669,#10b981);">{{ strtoupper(substr($ref->referred->name ?? 'C', 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:600;">{{ $ref->referred->name }}</div>
                                <div class="muted" style="font-size:12px;">{{ $ref->referred->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($ref->loan)
                            <a href="{{ route('admin.loans.show', $ref->loan) }}" style="color:var(--pl); font-weight:600; text-decoration:none;">
                                {{ $ref->loan->loan_number }}
                            </a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td style="font-weight:600;">M {{ number_format($ref->amount, 2) }}</td>
                    <td>
                        @switch($ref->status)
                            @case('pending')   <span class="badge bs">Pending</span>   @break
                            @case('validated') <span class="badge bi">Validated</span> @break
                            @case('qualified') <span class="badge bok">Qualified</span> @break
                            @case('paid')
                                <span class="badge bp">Paid</span>
                                @if($ref->payout_method)
                                <div class="payout-tag">
                                    @if($ref->payout_method === 'loan_credit')
                                        <i class="bi bi-bank"></i> Loan Credit
                                    @elseif($ref->payout_method === 'mpesa')
                                        <i class="bi bi-phone"></i> M-Pesa
                                    @else
                                        <i class="bi bi-cash"></i> Manual
                                    @endif
                                </div>
                                @endif
                                @break
                            @case('rejected')  <span class="badge be" title="{{ $ref->rejected_reason }}">Rejected</span>  @break
                        @endswitch
                    </td>
                    <td class="muted" style="font-size:12.5px;">{{ $ref->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        @if($ref->status === 'qualified')
                        <div class="pay-dropdown" id="payDrop{{ $ref->id }}">
                            <button type="button" class="pay-dropdown-btn" onclick="togglePayDrop({{ $ref->id }})">
                                <i class="bi bi-wallet2"></i> Pay Out <i class="bi bi-chevron-down" style="font-size:10px;"></i>
                            </button>
                            <div class="pay-dropdown-menu">
                                {{-- Option A: Credit to Loan --}}
                                <form action="{{ route('admin.referrals.credit-loan', $ref) }}" method="POST" onsubmit="return confirm('Credit M{{ number_format($ref->amount, 2) }} to {{ $ref->referrer->name }}\'s active loan?')">
                                    @csrf
                                    <button type="submit" class="pay-opt">
                                        <div class="pay-opt-icon" style="background:rgba(43,75,173,.1); color:var(--pl);">
                                            <i class="bi bi-bank"></i>
                                        </div>
                                        <div>
                                            <div class="pay-opt-title">Credit to Loan</div>
                                            <div class="pay-opt-desc">Deduct M{{ number_format($ref->amount, 2) }} from their loan balance</div>
                                        </div>
                                    </button>
                                </form>

                                @if($mpesaConfigured)
                                <div class="pay-sep"></div>
                                {{-- Option B: M-Pesa --}}
                                <form action="{{ route('admin.referrals.pay-mpesa', $ref) }}" method="POST" onsubmit="return confirm('Send M{{ number_format($ref->amount, 2) }} to {{ $ref->referrer->name }} ({{ $ref->referrer->phone }}) via M-Pesa?')">
                                    @csrf
                                    <button type="submit" class="pay-opt">
                                        <div class="pay-opt-icon" style="background:rgba(16,185,129,.1); color:var(--ok);">
                                            <i class="bi bi-phone"></i>
                                        </div>
                                        <div>
                                            <div class="pay-opt-title">Send via M-Pesa</div>
                                            <div class="pay-opt-desc">B2C to {{ $ref->referrer->phone }}</div>
                                        </div>
                                    </button>
                                </form>
                                @endif

                                <div class="pay-sep"></div>
                                {{-- Option C: Manual --}}
                                <form action="{{ route('admin.referrals.mark-paid', $ref) }}" method="POST" onsubmit="return confirm('Confirm: You have already paid M{{ number_format($ref->amount, 2) }} to {{ $ref->referrer->name }} outside the system?')">
                                    @csrf
                                    <button type="submit" class="pay-opt">
                                        <div class="pay-opt-icon" style="background:rgba(100,116,139,.1); color:var(--muted);">
                                            <i class="bi bi-cash"></i>
                                        </div>
                                        <div>
                                            <div class="pay-opt-title">Mark as Paid (Manual)</div>
                                            <div class="pay-opt-desc">Already paid via cash, bank, etc.</div>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @elseif($ref->status === 'paid')
                            <span class="muted" style="font-size:12px;"><i class="bi bi-check-circle-fill" style="color:var(--ok);"></i> Done</span>
                        @else
                            <span class="muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty">
                        <i class="bi bi-gift"></i>
                        No referrals found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($referrals->hasPages())
    <div style="padding:14px 22px; border-top:1px solid var(--border); display:flex; justify-content:center;">
        {{ $referrals->withQueryString()->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function togglePayDrop(id) {
    const el = document.getElementById('payDrop' + id);
    // Close all other dropdowns
    document.querySelectorAll('.pay-dropdown.open').forEach(d => {
        if (d.id !== 'payDrop' + id) d.classList.remove('open');
    });
    el.classList.toggle('open');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.pay-dropdown')) {
        document.querySelectorAll('.pay-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
</script>
@endpush
@endsection
