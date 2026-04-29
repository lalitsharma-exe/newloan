@extends('borrower.layouts.app')
@section('title','MyBill — ' . ucfirst($category))
@section('content')

@php
  $icons = ['electricity'=>'lightning-charge-fill','airtime'=>'phone-fill','insurance'=>'shield-fill','ticket'=>'ticket-perforated-fill'];
  $colors = ['electricity'=>'#f59e0b','airtime'=>'#0ea5e9','insurance'=>'#8b5cf6','ticket'=>'#ec4899'];
  $tints = ['electricity'=>'rgba(245,158,11,0.04)','airtime'=>'rgba(14,165,233,0.04)','insurance'=>'rgba(139,92,246,0.04)','ticket'=>'rgba(236,72,153,0.04)'];
  $selectedColor = $colors[$category];
  $selectedTint = $tints[$category];
@endphp

<div style="max-width:600px; margin:0 auto">
    
    {{-- Back link --}}
    <a href="{{ route('borrower.mybill.index') }}" class="glass-back-link">
        <i class="bi bi-arrow-left"></i> <span>Back to Dashboard</span>
    </a>

    {{-- Form Card (Light Glass) --}}
    <div class="light-glass-card" style="background: {{ $selectedTint }}; border-color: {{ $selectedColor }}20">
        <div style="text-align:center; margin-bottom:32px">
            <div class="category-icon-wrap" style="background: {{ $selectedColor }}15; color: {{ $selectedColor }}">
                <i class="bi bi-{{ $icons[$category] }}"></i>
            </div>
            <h2 style="font-size:24px; font-weight:900; color:#0f172a; margin:0; letter-spacing:-1px">Purchase {{ ucfirst($category) }}</h2>
            <div style="font-size:14px; color:#64748b; margin-top:4px">Enter details to generate your credit quote</div>
        </div>

        <form method="POST" action="{{ route('borrower.mybill.quote') }}" id="purchaseForm">
            @csrf
            <input type="hidden" name="bill_category" value="{{ $category }}">

            {{-- Category Specific Fields --}}
            @if($category === 'electricity')
                <div class="fg">
                    <label class="fl">Meter Number</label>
                    <div style="position:relative">
                        <input type="text" name="meter_number" class="glass-input" placeholder="Enter 11-digit meter number" required>
                        <i class="bi bi-hash" style="position:absolute; right:15px; top:12px; color:#94a3b8"></i>
                    </div>
                </div>
            @elseif($category === 'airtime')
                <div class="fg">
                    <label class="fl">Phone Number</label>
                    <input type="text" name="phone_number" class="glass-input" placeholder="e.g. 58123456" required>
                </div>
                <div class="fg">
                    <label class="fl">Network Operator</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                        <label class="network-opt">
                            <input type="radio" name="airtime_type" value="vodacom" checked>
                            <div class="opt-box">
                                <img src="/assets/logos/vodacom.png" style="height:14px; opacity:0.8">
                                <span>Vodacom</span>
                            </div>
                        </label>
                        <label class="network-opt">
                            <input type="radio" name="airtime_type" value="econet">
                            <div class="opt-box">
                                <img src="/assets/logos/econet.png" style="height:14px; opacity:0.8">
                                <span>Econet</span>
                            </div>
                        </label>
                    </div>
                </div>
            @endif

            <div class="fg">
                <label class="fl">Purchase Amount (M)</label>
                <div style="position:relative">
                    <input type="number" name="bill_value" class="glass-input" style="font-size:24px; font-weight:800; padding-left:45px" placeholder="0.00" min="10" max="{{ $limit->available_amount }}" required>
                    <span style="position:absolute; left:18px; top:13px; font-weight:800; color:#0f172a; font-size:20px">M</span>
                </div>
                <div style="font-size:11px; color:#94a3b8; margin-top:8px; display:flex; justify-content:space-between">
                    <span>Min: M 10.00</span>
                    <span>Max Available: <strong>M {{ number_format($limit->available_amount, 2) }}</strong></span>
                </div>
            </div>

            {{-- Repayment Tier Selection --}}
            <div class="fg" style="margin-top:24px">
                <label class="fl">Choose Payment Plan</label>
                <div style="display:grid; gap:12px">
                    <label class="tier-opt">
                        <input type="radio" name="tier" value="30" checked>
                        <div class="tier-box">
                            <div style="flex:1">
                                <div style="font-weight:800; color:#0f172a; font-size:15px">Standard Tier</div>
                                <div style="font-size:12px; color:#64748b">Pay 30% upfront now, balance on payday.</div>
                            </div>
                            <div class="tier-badge">30% FEE</div>
                        </div>
                    </label>
                    <label class="tier-opt">
                        <input type="radio" name="tier" value="40">
                        <div class="tier-box">
                            <div style="flex:1">
                                <div style="font-weight:800; color:#0f172a; font-size:15px">No-Upfront Tier</div>
                                <div style="font-size:12px; color:#64748b">Pay nothing now, full amount on payday.</div>
                            </div>
                            <div class="tier-badge" style="background:#f1f5f9; color:#64748b">40% FEE</div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="glass-submit-btn" style="background: {{ $selectedColor }}">
                Calculate Quote <i class="bi bi-arrow-right" style="margin-left:8px"></i>
            </button>
        </form>
    </div>

    {{-- Security Notice --}}
    <div style="text-align:center; margin-top:30px; color:#94a3b8; font-size:12px">
        <i class="bi bi-shield-lock-fill" style="margin-right:4px"></i> Securely processed by MyLoan Payment Systems
    </div>
</div>

<style>
.light-glass-card {
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(0,0,0,0.05);
    border-radius: 32px;
    padding: 40px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.04);
}
.glass-back-link {
    display: inline-flex; align-items: center; gap: 8px;
    color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 700;
    margin-bottom: 24px; transition: all 0.3s;
}
.glass-back-link:hover { color: #0f172a; transform: translateX(-4px); }

.category-icon-wrap {
    width: 64px; height: 64px; border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 28px;
}

.glass-input {
    width: 100%; padding: 14px 18px;
    background: rgba(255,255,255,0.8);
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px; font-family: inherit; font-size: 15px;
    transition: all 0.3s; outline: none;
}
.glass-input:focus {
    background: #fff; border-color: {{ $selectedColor }};
    box-shadow: 0 0 0 4px {{ $selectedColor }}15;
}

.network-opt input, .tier-opt input { display: none; }
.opt-box, .tier-box {
    padding: 16px; border-radius: 16px;
    background: rgba(255,255,255,0.6);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex; align-items: center; gap: 12px;
    cursor: pointer; transition: all 0.3s;
}
.network-opt input:checked + .opt-box, .tier-opt input:checked + .tier-box {
    background: #fff; border-color: {{ $selectedColor }};
    box-shadow: 0 10px 20px rgba(0,0,0,0.04);
}
.tier-box { gap: 16px; }
.tier-badge {
    font-size: 10px; font-weight: 800; padding: 4px 10px;
    border-radius: 6px; background: {{ $selectedColor }}15; color: {{ $selectedColor }};
}

.glass-submit-btn {
    width: 100%; padding: 18px; border: none; border-radius: 18px;
    color: #fff; font-size: 16px; font-weight: 900; cursor: pointer;
    margin-top: 32px; transition: all 0.3s;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
}
.glass-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(0,0,0,0.15); }
</style>

@endsection
