@extends('borrower.layouts.app')
@section('title', 'MyFloat Emergency Cash')
@section('content')

<div class="page-hdr">
    <h1>MyFloat</h1>
    <p>Emergency cash for when you need it most.</p>
</div>

@if($user->float_frozen)
    <div class="alert a-e" style="padding: 24px; border-radius: 16px; flex-direction: column; align-items: center; text-align: center; gap: 12px;">
        <div style="width: 64px; height: 64px; background: rgba(239,68,68,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--err); font-size: 28px;">
            <i class="bi bi-snow"></i>
        </div>
        <div>
            <h3 style="font-size: 18px; font-weight: 700; color: #991b1b;">Float Access Suspended</h3>
            <p style="margin-top: 4px; opacity: 0.8;">Your MyFloat access has been frozen. Reason: <strong>{{ $user->float_freeze_reason ?? 'Contact support for details' }}</strong></p>
        </div>
    </div>
@elseif($activeFloat)
    {{-- ACTIVE FLOAT DASHBOARD --}}
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px;">
        <div class="card" style="border: none; background: linear-gradient(135deg, var(--navy), var(--navy3)); color: #fff; position: relative; overflow: hidden;">
            {{-- Background decorative elements --}}
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -20px; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.02); border-radius: 50%;"></div>
            
            <div class="card-body" style="padding: 40px; position: relative; z-index: 2;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px;">
                    <div>
                        <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.6); font-weight: 600;">Outstanding Balance</div>
                        <div style="font-size: 48px; font-weight: 800; font-family: 'Cormorant Garamond', serif; margin-top: 4px;">M {{ number_format($activeFloat->outstanding_balance, 2) }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 30px; font-size: 12px; font-weight: 700; border: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $activeFloat->status==='disbursed'?'#10b981':($activeFloat->status==='due'?'#f59e0b':'#ef4444') }}"></span>
                        {{ strtoupper($activeFloat->status) }}
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: rgba(0,0,0,0.2); border-radius: 16px; padding: 20px;">
                    <div>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.5); text-transform: uppercase; font-weight: 600;">Repayment Due</div>
                        <div style="font-size: 16px; font-weight: 600; margin-top: 2px;">{{ $activeFloat->due_date ? $activeFloat->due_date->format('d M Y') : 'Processing...' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.5); text-transform: uppercase; font-weight: 600;">Principal Amount</div>
                        <div style="font-size: 16px; font-weight: 600; margin-top: 2px;">M {{ number_format($activeFloat->principal_amount, 2) }}</div>
                    </div>
                </div>

                <div style="margin-top: 32px; display: flex; gap: 12px;">
                    @if($activeFloat->status !== 'pending')
                    <button class="btn" style="background: #fff; color: var(--navy); padding: 12px 24px; border-radius: 12px; font-weight: 700; flex: 1; justify-content: center;">
                        <i class="bi bi-credit-card-2-front"></i> Repay Now
                    </button>
                    @endif
                    <button class="btn btn-o" style="border-color: rgba(255,255,255,0.2); color: #fff; padding: 12px 24px; border-radius: 12px; flex: 1; justify-content: center;">
                        <i class="bi bi-file-text"></i> View Statement
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-hdr"><span class="card-title">Penalty Logs</span></div>
            <div class="card-body" style="padding: 0;">
                @forelse($activeFloat->penaltyLogs as $log)
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--err);">Monthly Penalty</div>
                        <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ $log->created_at->format('d M Y') }}</div>
                    </div>
                    <div style="font-weight: 700; color: var(--err);">+ M {{ number_format($log->amount, 0) }}</div>
                </div>
                @empty
                <div style="padding: 40px 20px; text-align: center; color: var(--muted);">
                    <i class="bi bi-shield-check" style="font-size: 24px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                    <span style="font-size: 12px;">No penalties applied. Good job!</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
@elseif($user->float_eligible)
    {{-- ELIGIBLE: PROMO BANNER --}}
    <div class="card" style="border: none; border-radius: 24px; overflow: hidden; background: #000; position: relative; min-height: 400px; display: flex; align-items: center;">
        <img src="{{ asset('emergency_float_banner_1778866066374.png') }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.7;">
        
        <div style="position: relative; z-index: 2; padding: 60px; max-width: 600px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--accent); color: #fff; padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 700; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(140, 198, 63, 0.4);">
                <i class="bi bi-lightning-fill"></i> INSTANT ELIGIBILITY
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 52px; font-weight: 700; color: #fff; line-height: 1; margin-bottom: 16px;">M500 in your wallet, <span style="color: var(--accent);">instantly.</span></h2>
            <p style="font-size: 18px; color: rgba(255,255,255,0.8); margin-bottom: 32px; line-height: 1.6;">Unexpected expenses? Get an emergency cash float of M500 and pay it back with your next salary.</p>
            
            <div style="display: flex; gap: 24px; margin-bottom: 40px;">
                <div style="display: flex; align-items: center; gap: 10px; color: #fff;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="bi bi-check2"></i></div>
                    <span style="font-size: 14px; font-weight: 500;">M125 Fixed Fee</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: #fff;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="bi bi-check2"></i></div>
                    <span style="font-size: 14px; font-weight: 500;">No Collateral</span>
                </div>
            </div>

            <button onclick="openApplyModal()" class="btn btn-lg" style="background: var(--accent); color: #fff; font-weight: 700; padding: 16px 40px; border-radius: 16px; font-size: 18px;">
                Apply for M500 Now
            </button>
        </div>
    </div>
@else
    {{-- NOT ELIGIBLE --}}
    <div class="card" style="text-align: center; padding: 60px 40px; border: 2px dashed var(--border); background: #fafbff;">
        <div style="width: 80px; height: 80px; background: #fff; border-radius: 50%; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: var(--muted); font-size: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            <i class="bi bi-lightning"></i>
        </div>
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: var(--navy);">MyFloat Access Restricted</h2>
        <p style="max-width: 500px; margin: 12px auto 32px; color: var(--muted); line-height: 1.6;">MyFloat is an exclusive emergency cash facility for selected customers. Maintain a good repayment history to unlock this feature.</p>
        <div style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; background: #fff; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; font-weight: 600; color: var(--muted);">
            <i class="bi bi-info-circle"></i> Repay your active loans on time to improve eligibility.
        </div>
    </div>
@endif

{{-- APPLICATION MODAL --}}
<div id="applyModal" style="display:none; position:fixed; inset:0; background:rgba(13,27,62,0.8); backdrop-filter:blur(10px); z-index:1000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; width:100%; max-width:500px; border-radius:24px; overflow:hidden; animation: modalSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        <div style="padding:32px; background: linear-gradient(135deg, var(--navy), var(--navy3)); color:#fff; position:relative;">
            <button onclick="closeApplyModal()" style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.1); border:none; width:32px; height:32px; border-radius:50%; color:#fff; cursor:pointer; font-size:20px; display:flex; align-items:center; justify-content:center;">&times;</button>
            <div style="width:48px; height:48px; background:var(--accent); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:16px; box-shadow: 0 8px 20px rgba(0,0,0,0.2);">
                <i class="bi bi-lightning-fill"></i>
            </div>
            <h3 style="font-size:24px; font-weight:700; margin-bottom:4px;">Request Emergency Float</h3>
            <p style="font-size:14px; opacity:0.7;">Get M500 instantly in your wallet.</p>
        </div>

        <form action="{{ route('borrower.float.apply') }}" method="POST" style="padding:32px;">
            @csrf
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Repayment Amount</div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--navy); margin-top: 2px;">M 625.00</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Service Fee</div>
                    <div style="font-size: 16px; font-weight: 600; color: var(--accent); margin-top: 2px;">M 125.00</div>
                </div>
            </div>

            <div class="fg">
                <label class="fl">Why do you need this float?</label>
                <select name="purpose" class="fc" required style="height: 48px; border-radius: 12px; padding: 0 16px;">
                    <option value="">Select a purpose...</option>
                    <option value="Electricity / Water">Electricity / Water</option>
                    <option value="Medical Emergency">Medical Emergency</option>
                    <option value="Transport / Fuel">Transport / Fuel</option>
                    <option value="Grocery / Food">Grocery / Food</option>
                    <option value="School Related">School Related</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="padding: 16px; background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; margin-bottom: 24px;">
                <label style="display: flex; gap: 12px; cursor: pointer;">
                    <input type="checkbox" required style="width: 20px; height: 20px; accent-color: var(--accent); margin-top: 2px;">
                    <span style="font-size: 13px; color: #92400e; line-height: 1.5;">I acknowledge that <strong>M625</strong> will be due on my next payday. Late repayment will attract a monthly penalty of <strong>M125</strong>.</span>
                </label>
            </div>

            <button type="submit" class="btn" style="width: 100%; height: 52px; background: var(--navy); color: #fff; border-radius: 14px; font-weight: 700; font-size: 16px; justify-content: center; box-shadow: 0 10px 20px rgba(13, 27, 62, 0.2);">
                Confirm & Request M500
            </button>
        </form>
    </div>
</div>

<style>
@keyframes modalSlideUp {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
function openApplyModal() {
    document.getElementById('applyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeApplyModal() {
    document.getElementById('applyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}
</script>

@endsection
