@extends('borrower.layouts.app')
@section('title', 'Refer & Earn')

@push('styles')
<style>
/* ── REFERRAL HERO (Premium Style) ─────────────────────── */
.ref-hero {
    background-image: url('/assets/images/referral_hero.jpg');
    background-size: cover;
    background-position: right center;
    border-radius: 28px;
    padding: 0;
    position: relative;
    overflow: hidden;
    margin-bottom: 35px;
    color: #fff;
    box-shadow: 0 25px 60px -15px rgba(30, 27, 75, 0.4);
    height: 420px;
}
.ref-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(30, 27, 75, 0.9) 0%, rgba(30, 27, 75, 0.4) 40%, transparent 100%);
    display: flex;
    align-items: center;
    padding: 50px 40px;
    z-index: 2;
}
.ref-hero-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    position: relative;
    z-index: 3;
    width: 100%;
}
.ref-hero-text h2 {
    font-size: 48px;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 15px;
    letter-spacing: -2px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.ref-hero-text p {
    font-size: 16px;
    color: rgba(255,255,255,0.9);
    max-width: 420px;
    line-height: 1.6;
    font-weight: 600;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.ref-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(140, 198, 63, 0.9);
    color: #1e1b4b;
    padding: 8px 18px;
    border-radius: 100px;
    font-weight: 900;
    font-size: 11px;
    margin-bottom: 22px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    box-shadow: 0 4px 15px rgba(140,198,63,0.3);
}
.ref-hero-amount {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 30px;
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    flex-shrink: 0;
    min-width: 170px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.ref-hero-amount .val {
    font-size: 52px;
    color: var(--accent);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -3px;
}
.ref-hero-amount .lbl {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: #fff;
    margin-top: 8px;
    font-weight: 900;
}

/* ── STAT CARDS (Glassmorphism) ────────────────────────── */
.ref-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 35px;
}
.ref-stat {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 28px;
    padding: 26px;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.03);
}
.ref-stat:hover {
    transform: translateY(-8px);
    background: #fff;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
}
.ref-stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 22px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}
.ref-stat .stat-val {
    font-size: 30px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: -1px;
    line-height: 1.2;
}
.ref-stat .stat-lbl {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

/* ── CONTENT GRID ──────────────────────────────────────── */
.ref-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 30px;
    align-items: start;
}

.premium-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.03);
}

/* ── LINK BOX ──────────────────────────────────────────── */
.ref-link-box {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.3s;
}
.ref-link-box:focus-within {
    background: #fff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}
.ref-link-input {
    background: transparent;
    border: none;
    outline: none;
    flex: 1;
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    min-width: 0;
}
.ref-copy-btn {
    background: #1e1b4b;
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 14px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}
.ref-copy-btn:hover { background: #3b82f6; transform: translateY(-2px); }

/* ── RESPONSIVE ────────────────────────────────────────── */
@media (max-width: 768px) {
    .ref-hero { height: 480px; border-radius: 24px; background-position: 70% center; }
    .ref-hero-overlay { position: absolute; background: linear-gradient(to bottom, rgba(30, 27, 75, 0.2) 0%, rgba(30, 27, 75, 0.95) 80%); align-items: flex-end; padding: 30px 20px; text-align: center; }
    .ref-hero-inner { flex-direction: column; }
    .ref-hero-text h2 { font-size: 32px; margin-bottom: 10px; }
    .ref-hero-text p { font-size: 14px; max-width: none; }
    .ref-hero-amount { width: 100%; min-width: 0; padding: 20px; margin-top: 15px; }
    .ref-hero-amount .val { font-size: 40px; }
    .ref-stats { grid-template-columns: 1fr; gap: 15px; }
    .ref-grid { grid-template-columns: 1fr; }
    .ref-stat { padding: 22px; }
}
@media (min-width: 769px) and (max-width: 960px) {
    .ref-stats { grid-template-columns: 1fr 1fr 1fr; }
    .ref-grid { grid-template-columns: 1fr 260px; }
}
</style>
@endpush

@section('content')
<div class="page-hdr">
    <h1>Refer & Earn</h1>
</div>

{{-- ── HERO BANNER ──────────────────────────────────────── --}}
<div class="ref-hero">
    <div class="ref-hero-overlay">
        <div class="ref-hero-inner">
            <div class="ref-hero-text">
                <div class="ref-hero-pill">
                    <i class="bi bi-stars"></i> Partner Rewards
                </div>
                <h2>Turn Friendships<br>into Earnings</h2>
                <p>Earn <strong>M50.00</strong> for every friend you refer. Share your link and watch your earnings grow as they join the MyLoan family.</p>
            </div>
            <div class="ref-hero-amount">
                <div class="val">M50</div>
                <div class="lbl">Per Referral</div>
            </div>
        </div>
    </div>
</div>

{{-- ── STAT CARDS ───────────────────────────────────────── --}}
<div class="ref-stats">
    <div class="ref-stat">
        <div class="ref-stat-icon" style="background:rgba(43,75,173,.1); color:var(--blue);">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="stat-val">M {{ number_format($stats['total_earned'], 2) }}</div>
        <div class="stat-lbl">Total Earned</div>
    </div>
    <div class="ref-stat">
        <div class="ref-stat-icon" style="background:rgba(16,185,129,.1); color:var(--ok);">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-val">{{ count($referrals) }}</div>
        <div class="stat-lbl">Friends Referred</div>
    </div>
    <div class="ref-stat">
        <div class="ref-stat-icon" style="background:rgba(245,158,11,.1); color:var(--warn);">
            <i class="bi bi-wallet-fill"></i>
        </div>
        <div class="stat-val">M {{ number_format($stats['balance'], 2) }}</div>
        <div class="stat-lbl">Available Balance</div>
    </div>
</div>

@if($stats['balance'] >= 250)
<div class="alert a-i" style="margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; padding: 20px;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="font-size: 24px; color: var(--blue);"><i class="bi bi-gift"></i></div>
        <div>
            <div style="font-weight: 700; font-size: 16px;">Payout Milestone Reached!</div>
            <div style="font-size: 13px; opacity: 0.8;">You have M{{ number_format($stats['balance'], 2) }} available for payout.</div>
        </div>
    </div>
    <form action="{{ route('borrower.referrals.payout-request') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-p">Request Payout Now</button>
    </form>
</div>
@endif

{{-- ── MAIN + SIDEBAR ──────────────────────────────────── --}}
<div class="ref-grid">
    <div>
        {{-- Share Card --}}
        <div class="premium-card" style="margin-bottom:20px;">
            <div class="card-hdr" style="background:rgba(0,0,0,0.02); padding:20px 24px; border-bottom:1px solid rgba(0,0,0,0.05)">
                <span class="card-title" style="font-weight:900; color:#0f172a">Share Your Link</span>
                <div class="ref-share">
                    <a href="https://wa.me/?text={{ urlencode('Get a loan easily with MyLoan! Apply here: ' . $user->referral_link) }}" target="_blank" class="ref-share-btn wa" title="Share on WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($user->referral_link) }}" target="_blank" class="ref-share-btn fb" title="Share on Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                </div>
            </div>
            <div class="card-body" style="padding:24px;">
                @if(!$user->canRefer())
                <div class="alert a-w" style="margin-bottom:20px; border-radius:16px;">
                    <i class="bi bi-info-circle-fill"></i>
                    <div><strong>Action Required:</strong> You need an active or closed loan to be eligible for rewards. Once your first loan is disbursed, you can start earning!</div>
                </div>
                @endif

                <div class="ref-link-box">
                    <i class="bi bi-link-45deg" style="font-size:20px; color:#64748b;"></i>
                    <input type="text" id="referralLink" class="ref-link-input" value="{{ $user->referral_link }}" readonly>
                    <button class="ref-copy-btn" id="copyBtn" onclick="copyReferralLink()">
                        <i class="bi bi-copy"></i> Copy Link
                    </button>
                </div>
                <div id="copyAlert" style="display:none; margin-top:12px; font-size:13px; color:#10b981; font-weight:800; text-align:center;">
                    <i class="bi bi-check2-circle"></i> Referral link copied successfully!
                </div>
            </div>
        </div>

        {{-- Referral History --}}
        <div class="premium-card">
            <div class="card-hdr" style="background:rgba(0,0,0,0.02); padding:20px 24px; border-bottom:1px solid rgba(0,0,0,0.05)">
                <span class="card-title" style="font-weight:900; color:#0f172a">Recent Referrals</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="dt">
                    <thead>
                        <tr>
                            <th>Friend</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th style="text-align:right;">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrals as $ref)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--blue2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($ref->referred->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600; font-size:13px;">{{ $ref->referred->name }}</div>
                                        <div style="font-size:11px; color:var(--muted);">{{ $ref->referred->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @switch($ref->status)
                                    @case('pending')
                                        <span class="ref-status pending"><i class="bi bi-circle"></i> Registered</span>
                                        @break
                                    @case('validated')
                                        <span class="ref-status validated"><i class="bi bi-check-all"></i> Loan Active</span>
                                        @break
                                    @case('qualified')
                                        <span class="ref-status qualified"><i class="bi bi-cash-stack"></i> Qualified</span>
                                        @break
                                    @case('payout_pending')
                                        <span class="ref-status payout_pending"><i class="bi bi-clock-history"></i> Payout Requested</span>
                                        @break
                                    @case('paid')
                                        <span class="ref-status paid"><i class="bi bi-check-circle-fill"></i> Paid</span>
                                        @break
                                    @default
                                        <span class="ref-status rejected"><i class="bi bi-x-circle"></i> Rejected</span>
                                @endswitch
                            </td>
                            <td style="font-size:12.5px; color:var(--muted);">{{ $ref->created_at->format('d M Y') }}</td>
                            <td style="text-align:right; font-weight:700;">
                                @if($ref->status === 'paid')
                                    <span style="color:var(--ok);">M {{ number_format($ref->amount, 2) }}</span>
                                @elseif($ref->status === 'qualified')
                                    <span style="color:var(--blue);">M 50.00</span>
                                @else
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:40px 20px; color:var(--muted);">
                                <i class="bi bi-people" style="font-size:36px; opacity:.3; display:block; margin-bottom:8px;"></i>
                                No referrals yet. Share your link to start earning!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="premium-card" style="background:rgba(30, 27, 75, 0.02)">
            <div class="card-hdr" style="background:rgba(0,0,0,0.02); padding:16px 20px; border-bottom:1px solid rgba(0,0,0,0.05)">
                <span class="card-title" style="font-size:15px; font-weight:900; color:#0f172a">How to Earn</span>
            </div>
            <div class="card-body">
                <div class="ref-step">
                    <div class="ref-step-num">1</div>
                    <div>
                        <div style="font-weight:600; font-size:13px; margin-bottom:3px;">Share Link</div>
                        <div style="font-size:12px; color:var(--muted); line-height:1.5;">Invite friends using your unique referral code.</div>
                    </div>
                </div>
                <div class="ref-step">
                    <div class="ref-step-num">2</div>
                    <div>
                        <div style="font-weight:600; font-size:13px; margin-bottom:3px;">They Join</div>
                        <div style="font-size:12px; color:var(--muted); line-height:1.5;">They register and take their first loan with us.</div>
                    </div>
                </div>
                <div class="ref-step">
                    <div class="ref-step-num">3</div>
                    <div>
                        <div style="font-weight:600; font-size:13px; margin-bottom:3px;">Get Reward</div>
                        <div style="font-size:12px; color:var(--muted); line-height:1.5;">Once they pay their 1st installment, you get M50!</div>
                    </div>
                </div>

                <div style="margin-top:20px; padding:12px; background:#fff; border:1px solid var(--border); border-radius:8px; font-size:12px; color:var(--muted); line-height:1.5;">
                    <i class="bi bi-info-circle" style="margin-right:4px;"></i>
                    Reward is paid for the <strong>first loan only</strong> of each new customer you refer.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    const btn   = document.getElementById('copyBtn');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);

    btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
    btn.classList.add('copied');
    document.getElementById('copyAlert').style.display = 'block';

    setTimeout(() => {
        btn.innerHTML = '<i class="bi bi-copy"></i> Copy';
        btn.classList.remove('copied');
        document.getElementById('copyAlert').style.display = 'none';
    }, 3000);
}
</script>
@endsection
