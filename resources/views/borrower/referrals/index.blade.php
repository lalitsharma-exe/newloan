@extends('borrower.layouts.app')
@section('title', 'Refer & Earn')

@push('styles')
<style>
/* ── REFERRAL HERO ─────────────────────────────────────── */
.ref-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy3) 100%);
    border-radius: 14px;
    padding: 40px 30px;
    position: relative;
    overflow: hidden;
    margin-bottom: 22px;
    color: #fff;
    box-shadow: 0 8px 30px rgba(7,14,36,.2);
}
.ref-hero::before {
    content: '';
    position: absolute;
    top: -60%;
    right: -15%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
    opacity: 0.12;
    filter: blur(40px);
}
.ref-hero-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    position: relative;
    z-index: 1;
}
.ref-hero-text h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px;
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 10px;
}
.ref-hero-text p {
    font-size: 14px;
    color: rgba(255,255,255,.7);
    max-width: 400px;
    line-height: 1.6;
}
.ref-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(140,198,63,.15);
    color: var(--accent);
    padding: 5px 14px;
    border-radius: 100px;
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 14px;
    border: 1px solid rgba(140,198,63,.2);
}
.ref-hero-amount {
    background: rgba(255,255,255,.08);
    backdrop-filter: blur(8px);
    padding: 22px 28px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.1);
    text-align: center;
    flex-shrink: 0;
}
.ref-hero-amount .val {
    font-size: 36px;
    color: var(--accent);
    font-family: 'Cormorant Garamond', serif;
    font-weight: 700;
    line-height: 1;
}
.ref-hero-amount .lbl {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: rgba(255,255,255,.5);
    margin-top: 4px;
}

/* ── STAT CARDS ────────────────────────────────────────── */
.ref-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
.ref-stat {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 16px;
    transition: transform .2s, box-shadow .2s;
}
.ref-stat:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(13,27,62,.06);
}
.ref-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    margin-bottom: 12px;
}
.ref-stat .stat-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--navy);
}
.ref-stat .stat-lbl {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* ── CONTENT GRID (main + sidebar) ─────────────────────── */
.ref-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}

/* ── LINK BOX ──────────────────────────────────────────── */
.ref-link-box {
    background: var(--bg);
    border: 1.5px dashed var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ref-link-input {
    background: transparent;
    border: none;
    outline: none;
    flex: 1;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    min-width: 0;
}
.ref-copy-btn {
    background: var(--navy);
    color: #fff;
    border: none;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
    font-family: 'Outfit', sans-serif;
    display: flex;
    align-items: center;
    gap: 5px;
}
.ref-copy-btn:hover { background: var(--blue); }
.ref-copy-btn.copied { background: var(--ok); }

/* ── SHARE BUTTONS ─────────────────────────────────────── */
.ref-share {
    display: flex;
    gap: 8px;
}
.ref-share-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    text-decoration: none;
    transition: transform .2s;
}
.ref-share-btn.wa { background: #e7f7ef; color: #25d366; }
.ref-share-btn.fb { background: #e7f0ff; color: #1877f2; }
.ref-share-btn:hover { transform: scale(1.1); }

/* ── STATUS PILLS ──────────────────────────────────────── */
.ref-status {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ref-status.pending   { background: #fef3c7; color: #92400e; }
.ref-status.validated { background: #dcfce7; color: #166534; }
.ref-status.qualified { background: #dbeafe; color: #1e40af; }
.ref-status.paid      { background: #f0fdf4; color: #15803d; }
.ref-status.rejected  { background: #fef2f2; color: #991b1b; }

/* ── HOW-TO STEPS ──────────────────────────────────────── */
.ref-step {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.ref-step:last-child { margin-bottom: 0; }
.ref-step-num {
    width: 28px;
    height: 28px;
    background: #fff;
    border: 1.5px solid var(--border);
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: var(--navy);
    flex-shrink: 0;
}

/* ── RESPONSIVE ────────────────────────────────────────── */
@media (max-width: 768px) {
    .ref-hero-inner { flex-direction: column; text-align: center; }
    .ref-hero-text p { max-width: none; }
    .ref-hero-amount { display: none; }
    .ref-hero-text h2 { font-size: 26px; }
    .ref-hero { padding: 30px 20px; }
    .ref-stats { grid-template-columns: 1fr; }
    .ref-grid { grid-template-columns: 1fr; }
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
    <div class="ref-hero-inner">
        <div class="ref-hero-text">
            <div class="ref-hero-pill">
                <i class="bi bi-stars"></i> Exclusive Rewards
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
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-val">{{ $stats['qualified'] }}</div>
        <div class="stat-lbl">Pending Payouts</div>
    </div>
</div>

{{-- ── MAIN + SIDEBAR ──────────────────────────────────── --}}
<div class="ref-grid">
    <div>
        {{-- Share Card --}}
        <div class="card" style="margin-bottom:18px;">
            <div class="card-hdr">
                <span class="card-title">Share Your Link</span>
                <div class="ref-share">
                    <a href="https://wa.me/?text={{ urlencode('Get a loan easily with MyLoan! Apply here: ' . $user->referral_link) }}" target="_blank" class="ref-share-btn wa" title="Share on WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($user->referral_link) }}" target="_blank" class="ref-share-btn fb" title="Share on Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(!$user->canRefer())
                <div class="alert a-w" style="margin-bottom:14px;">
                    <i class="bi bi-info-circle-fill"></i>
                    <div><strong>Action Required:</strong> You need an active or closed loan to be eligible for rewards. Once your first loan is disbursed, you can start earning!</div>
                </div>
                @endif

                <div class="ref-link-box">
                    <i class="bi bi-link-45deg" style="font-size:18px; color:var(--muted);"></i>
                    <input type="text" id="referralLink" class="ref-link-input" value="{{ $user->referral_link }}" readonly>
                    <button class="ref-copy-btn" id="copyBtn" onclick="copyReferralLink()">
                        <i class="bi bi-copy"></i> Copy
                    </button>
                </div>
                <div id="copyAlert" style="display:none; margin-top:8px; font-size:12px; color:var(--ok); font-weight:600;">
                    <i class="bi bi-check2-circle"></i> Link copied to clipboard!
                </div>
            </div>
        </div>

        {{-- Referral History --}}
        <div class="card">
            <div class="card-hdr">
                <span class="card-title">Referral History</span>
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
        <div class="card" style="background:#fafbff;">
            <div class="card-hdr">
                <span class="card-title">How to Earn</span>
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
