@extends('borrower.layouts.app')
@section('title','MyBill')
@section('content')

{{-- Background Auras --}}
<div style="position:fixed; top:20%; right:10%; width:400px; height:400px; background:rgba(59,130,246,0.04); filter:blur(100px); border-radius:50%; z-index:-1"></div>
<div style="position:fixed; bottom:10%; left:5%; width:300px; height:300px; background:rgba(16,185,129,0.03); filter:blur(80px); border-radius:50%; z-index:-1"></div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="glass-alert-light a-ok mb-4"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

{{-- Credit Limit Hero (Premium Card Style) --}}
<div class="credit-hero-card">
    <div style="position:relative; z-index:2">
        <div style="display:flex; justify-content:space-between; align-items:flex-start">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px">
                    <div style="width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 10px #10b981"></div>
                    <span style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:2px">Available Balance</span>
                </div>
                <div style="font-size:48px; font-weight:900; color:#fff; letter-spacing:-1.5px; line-height:1">
                    M {{ number_format($limit->available_amount, 2) }}
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:11px; color:rgba(255,255,255,0.5); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px">Spending Limit</div>
                <div style="font-size:22px; font-weight:800; color:#fff">M {{ number_format($limit->total_limit, 2) }}</div>
            </div>
        </div>

        @php $pct = $limit->total_limit > 0 ? min(100, ($limit->used_amount / $limit->total_limit) * 100) : 0; @endphp
        <div style="margin-top:40px">
            <div style="background:rgba(255,255,255,0.1); border-radius:100px; height:10px; overflow:hidden; border:1px solid rgba(255,255,255,0.05)">
                <div style="background:linear-gradient(90deg, #3b82f6, #60a5fa); height:100%; width:{{ 100 - $pct }}%; border-radius:100px; transition:width 1.5s ease"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-top:14px; color:rgba(255,255,255,0.7); font-weight:700">
                <span>Used: M {{ number_format($limit->used_amount, 2) }}</span>
                <span style="color:#60a5fa">{{ 100 - round($pct) }}% Credit Available</span>
            </div>
        </div>
    </div>
</div>

{{-- Featured Categories (Image Based Design) --}}
<div style="margin-bottom:40px">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px">
        <h3 style="font-size:22px; font-weight:900; color:#0f172a; margin:0; letter-spacing:-0.5px">Featured Services</h3>
        <span style="font-size:12px; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:1px">CPay Integrated</span>
    </div>
    
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px">
        
        {{-- Electricity --}}
        <a href="{{ route('borrower.mybill.purchase', 'electricity') }}" class="image-card" style="background-image: url('{{ asset('assets/mybill/electricity.png') }}')">
            <div class="image-card-overlay">
                <div style="flex:1">
                    <h4 class="card-title">Prepaid Electricity</h4>
                    <p class="card-desc">Instant LEC token generation with automatic meter lookup.</p>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center">
                    <div class="card-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706)">Get Token</div>
                    <div style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.7); text-transform:uppercase">LEC Partner</div>
                </div>
            </div>
        </a>

        {{-- Airtime --}}
        <a href="{{ route('borrower.mybill.purchase', 'airtime') }}" class="image-card" style="background-image: url('{{ asset('assets/mybill/airtime.png') }}')">
            <div class="image-card-overlay">
                <div style="flex:1">
                    <h4 class="card-title">Airtime & Data</h4>
                    <p class="card-desc">Top up Vodacom or Econet bundles directly to any number.</p>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center">
                    <div class="card-btn" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8)">Buy Bundle</div>
                    <div style="display:flex; gap:6px">
                        <div style="font-size:10px; font-weight:800; color:#fff; background:rgba(0,0,0,0.3); padding:2px 8px; border-radius:4px">VCL</div>
                        <div style="font-size:10px; font-weight:800; color:#fff; background:rgba(0,0,0,0.3); padding:2px 8px; border-radius:4px">ETL</div>
                    </div>
                </div>
            </div>
        </a>

        {{-- Insurance --}}
        <a href="{{ route('borrower.mybill.purchase', 'insurance') }}" class="image-card" style="background-image: url('{{ asset('assets/mybill/insurance.png') }}')">
            <div class="image-card-overlay">
                <div style="flex:1">
                    <h4 class="card-title">Insurance Premiums</h4>
                    <p class="card-desc">Secure your family's future by paying premiums on credit.</p>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center">
                    <div class="card-btn" style="background: linear-gradient(135deg, #10b981, #059669)">Pay Policy</div>
                    <div style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.7); text-transform:uppercase">Trusted</div>
                </div>
            </div>
        </a>

        {{-- Tickets --}}
        <a href="{{ route('borrower.mybill.purchase', 'ticket') }}" class="image-card" style="background-image: url('{{ asset('assets/mybill/tickets.png') }}')">
            <div class="image-card-overlay">
                <div style="flex:1">
                    <h4 class="card-title">Events & Tickets</h4>
                    <p class="card-desc">Don't miss out. Book concert and event tickets instantly.</p>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center">
                    <div class="card-btn" style="background: linear-gradient(135deg, #ec4899, #be185d)">Book Ticket</div>
                    <div style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.7); text-transform:uppercase">Live Events</div>
                </div>
            </div>
        </a>

    </div>
</div>

{{-- Recent Transactions --}}
<div class="recent-list-glass">
    <div style="padding:24px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(0,0,0,0.05)">
        <span style="font-size:16px; font-weight:900; color:#0f172a">Recent Activity</span>
        <a href="{{ route('borrower.mybill.history') }}" style="font-size:13px; color:#3b82f6; text-decoration:none; font-weight:800">History & Details</a>
    </div>

    <div style="padding:12px">
        @forelse($loans as $loan)
        <a href="{{ route('borrower.mybill.show', $loan) }}" class="txn-item">
            <div class="txn-icon" style="background: rgba(15,23,42,0.04)">
                <i class="bi bi-{{ $loan->category_icon }}" style="color: #64748b"></i>
            </div>
            <div style="flex:1">
                <div style="font-weight:800; font-size:15px; color:#1e293b">{{ ucfirst($loan->bill_category) }}</div>
                <div style="font-size:12px; color:#94a3b8; font-weight:600">{{ $loan->created_at->format('d M, H:i') }} · #{{ $loan->loan_number }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:900; font-size:18px; color:#0f172a">M {{ number_format($loan->bill_value, 2) }}</div>
                <div style="font-size:10px; font-weight:900; color:{{ $loan->status === 'settled' ? '#10b981' : '#f59e0b' }}; text-transform:uppercase; letter-spacing:1px">{{ $loan->status }}</div>
            </div>
        </a>
        @empty
        <div style="padding:80px 20px; text-align:center; color:#94a3b8">
            <div style="font-size:14px; font-weight:600">No activity recorded</div>
        </div>
        @endforelse
    </div>
</div>

<style>
/* Hero Card */
.credit-hero-card {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    border-radius: 32px;
    padding: 45px;
    margin-bottom: 45px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 60px -15px rgba(30,27,75,0.4);
}
.credit-hero-card::after {
    content: ''; position: absolute; top: -50%; right: -20%; width: 300px; height: 300px;
    background: rgba(59,130,246,0.1); filter: blur(60px); border-radius: 50%;
}

/* Image Based Cards */
.image-card {
    display: block;
    height: 240px;
    border-radius: 28px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    text-decoration: none;
    transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
.image-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0,0,0,0.25);
}
.image-card-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%);
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    backdrop-filter: blur(0px);
    transition: backdrop-filter 0.5s;
}
.image-card:hover .image-card-overlay {
    backdrop-filter: blur(2px);
}

.card-title { font-size: 20px; font-weight: 900; color: #fff; margin: 0 0 4px; letter-spacing: -0.5px; }
.card-desc { font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 20px; line-height: 1.4; max-width: 80%; }

.card-btn {
    padding: 10px 20px;
    border-radius: 100px;
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

/* Recent List Glass */
.recent-list-glass {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.8);
    border-radius: 32px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.04);
}

.txn-item {
    display: flex; align-items: center; gap: 18px; padding: 16px 20px;
    border-radius: 20px; text-decoration: none; transition: all 0.3s; margin-bottom: 6px;
}
.txn-item:hover { background: #fff; transform: scale(1.01); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
.txn-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
}

.glass-alert-light {
    padding: 16px 20px; border-radius: 18px; font-size: 14px; font-weight: 800;
    backdrop-filter: blur(10px); display: flex; align-items: center; gap: 12px;
    background: rgba(16, 185, 129, 0.08); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2);
}
</style>

@endsection
