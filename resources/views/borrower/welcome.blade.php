@extends('borrower.layouts.public')
@section('title','Home')
@section('header-class','transparent')

@push('page-styles')
<style>
/* ─── FULL-SCREEN HERO ─────────────────────────────────── */
.hero {
  position: relative;
  height: 100vh; min-height: 600px; max-height: 1000px;
  display: flex; flex-direction: column;
  overflow: hidden;
}

/* Rich navy gradient — always visible (video fallback) */
.hero::before {
  content: '';
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 70% 80% at 20% 50%, rgba(30,51,112,.7) 0%, transparent 65%),
    radial-gradient(ellipse 60% 60% at 80% 20%, rgba(43,75,173,.3) 0%, transparent 60%),
    linear-gradient(160deg, #070e24 0%, #0d1b3e 40%, #162552 75%, #0d1b3e 100%);
}

.hero-video {
  position: absolute; inset: 0; z-index: 1;
  width: 100%; height: 100%;
  object-fit: cover; opacity: .18;
}

.hero-overlay {
  position: absolute; inset: 0; z-index: 2;
  background: linear-gradient(160deg,
    rgba(7,14,36,.92) 0%,
    rgba(13,27,62,.78) 45%,
    rgba(7,14,36,.88) 100%);
}

/* Subtle animated orbs */
.hero-orb {
  position: absolute; z-index: 2; border-radius: 50%; pointer-events: none;
  animation: orb 12s ease-in-out infinite;
}
.hero-orb-1 { width: 500px; height: 500px; top: -100px; left: -100px; background: radial-gradient(circle, rgba(43,75,173,.2) 0%, transparent 70%); animation-delay: 0s; }
.hero-orb-2 { width: 400px; height: 400px; bottom: -80px; right: 30%; background: radial-gradient(circle, rgba(30,51,112,.25) 0%, transparent 70%); animation-delay: 4s; }
.hero-orb-3 { width: 300px; height: 300px; top: 30%; right: 50px; background: radial-gradient(circle, rgba(201,168,76,.08) 0%, transparent 70%); animation-delay: 8s; }
@keyframes orb { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(20px,-20px) scale(1.08)} }

/* Main content area — fills viewport above footer */
.hero-body {
  position: relative; z-index: 3;
  flex: 1;
  display: grid;
  grid-template-columns: 1fr 440px;
  gap: 48px;
  align-items: center;
  max-width: 1200px; margin: 0 auto; padding: 80px 32px 32px;
  width: 100%;
}

/* LEFT — Message */
.hero-message { padding-right: 16px; }

.hero-kicker {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 11px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase;
  color: var(--accent); margin-bottom: 22px;
  opacity: 0; animation: rise .7s .3s forwards;
}
.hero-kicker::before { content:''; width:20px; height:1px; background:var(--accent); }
.hero-kicker span { width:7px; height:7px; border-radius:50%; background:var(--accent); animation: blink 2s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

.hero h1 {
  color: #fff;
  font-size: clamp(38px, 5vw, 68px);
  letter-spacing: -.03em; line-height: 1.08;
  margin-bottom: 20px;
  opacity: 0; animation: rise .8s .5s forwards;
}
.hero h1 em { font-style: italic; color: var(--accent2); }

.hero-sub {
  font-size: 17px; color: rgba(255,255,255,.58);
  line-height: 1.75; max-width: 480px; margin-bottom: 36px;
  opacity: 0; animation: rise .8s .7s forwards;
}

.hero-cta {
  opacity: 0; animation: rise .8s .9s forwards;
}
.btn-hero {
  display: inline-flex; align-items: center; gap: 10px;
  background: var(--accent); color: #fff;
  padding: 16px 36px; border-radius: 4px;
  font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 700;
  letter-spacing: .04em; text-decoration: none;
  transition: all .3s; box-shadow: 0 8px 28px rgba(201,168,76,.3);
}
.btn-hero:hover { background: #a88030; transform: translateY(-2px); box-shadow: 0 14px 36px rgba(201,168,76,.4); }

.hero-trust {
  display: flex; gap: 24px; flex-wrap: wrap;
  margin-top: 40px; padding-top: 28px;
  border-top: 1px solid rgba(255,255,255,.08);
  opacity: 0; animation: rise .8s 1.1s forwards;
}
.trust-pill {
  display: flex; align-items: center; gap: 7px;
  font-size: 12px; color: rgba(255,255,255,.45);
  font-family: 'Outfit', sans-serif;
}
.trust-pill i { color: var(--light); font-size: 13px; }

@keyframes rise { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

/* RIGHT — Calculator card */
.calc-card {
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.1);
  backdrop-filter: blur(24px);
  border-radius: 12px; padding: 28px 26px;
  opacity: 0; animation: rise 1s 1s forwards;
}
.calc-card-title {
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .14em; text-transform: uppercase;
  color: rgba(255,255,255,.35); margin-bottom: 22px;
  display: flex; align-items: center; justify-content: space-between;
}
.calc-card-title span { color: var(--accent); font-size: 10px; }

.cf { margin-bottom: 18px; }
.cf-header { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: rgba(255,255,255,.55); font-family: 'Outfit', sans-serif; margin-bottom: 9px; }
.cf-val { color: #fff; font-weight: 700; font-size: 14px; }
input[type=range] { width: 100%; height: 3px; border-radius: 99px; background: rgba(255,255,255,.12); accent-color: var(--accent); cursor: pointer; outline: none; }

.calc-product {
  margin-bottom: 18px;
  display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;
}
.prod-btn {
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
  border-radius: 4px; padding: 7px 4px; cursor: pointer;
  font-family: 'Outfit', sans-serif; font-size: 10px; font-weight: 600;
  color: rgba(255,255,255,.5); text-align: center; transition: all .2s; letter-spacing: .03em;
  line-height: 1.3;
}
.prod-btn:hover, .prod-btn.active { background: rgba(201,168,76,.15); border-color: rgba(201,168,76,.4); color: var(--accent); }

.calc-result {
  background: rgba(201,168,76,.08);
  border: 1px solid rgba(201,168,76,.2);
  border-radius: 8px; padding: 16px 18px; margin-top: 18px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.cr-item { }
.cr-lbl { font-size: 10px; font-weight: 600; color: rgba(255,255,255,.4); letter-spacing: .1em; text-transform: uppercase; margin-bottom: 4px; }
.cr-val { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; color: #fff; line-height: 1; }
.cr-val.accent { color: var(--accent2); }
.cr-small { font-size: 13px; color: rgba(255,255,255,.5); font-family: 'Outfit', sans-serif; margin-top: 8px; }

.calc-apply {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; margin-top: 16px; padding: 13px;
  background: var(--accent); color: #fff; border: none; border-radius: 4px;
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700;
  letter-spacing: .04em; cursor: pointer; text-decoration: none;
  transition: all .25s;
}
.calc-apply:hover { background: #a88030; transform: translateY(-1px); }
.calc-disclaimer { text-align: center; margin-top: 10px; font-size: 10px; color: rgba(255,255,255,.25); font-family: 'Outfit', sans-serif; }

/* Stats strip at the very bottom of hero */
.hero-stats {
  position: relative; z-index: 3;
  background: rgba(5,10,26,.88);
  backdrop-filter: blur(16px);
  border-top: 1px solid rgba(255,255,255,.06);
  flex-shrink: 0;
}
.stats-grid {
  max-width: 1200px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: repeat(4,1fr);
}
.stat-cell {
  padding: 18px 20px; border-right: 1px solid rgba(255,255,255,.06);
  text-align: center;
}
.stat-cell:last-child { border-right: none; }
.stat-n { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; color: #fff; line-height: 1; }
.stat-l { font-family: 'Outfit', sans-serif; font-size: 10px; color: rgba(255,255,255,.35); letter-spacing: .1em; text-transform: uppercase; margin-top: 4px; }

/* ── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:960px) {
  .hero-body { grid-template-columns: 1fr; padding: 90px 20px 24px; }
  .calc-card { display: none; }
  .hero { height: auto; min-height: 100vh; }
  .stats-grid { grid-template-columns: repeat(2,1fr); }
  .stat-cell:nth-child(2) { border-right: none; }
  .stat-cell:nth-child(3) { border-top: 1px solid rgba(255,255,255,.06); }
}
@media(max-width:480px) {
  .hero-trust { gap: 16px; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')
<div data-transparent-header="1">

<section class="hero">
  <!-- Background layers -->
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-orb hero-orb-3"></div>

  <!-- Video (plays when available) -->
  <video class="hero-video" autoplay muted loop playsinline preload="auto">
    <source src="https://ik.imagekit.io/ygydr1m84/videoplayback.mp4" type="video/mp4">
  </video>
  <div class="hero-overlay"></div>

  <!-- Main content -->
  <div class="hero-body">

    <!-- LEFT: Message -->
    <div class="hero-message">
      <div class="hero-kicker">
        <span></span>Licensed · Central Bank of Lesotho
      </div>
      <h1>Fast, Secure<br>Loans for <em>Lesotho</em></h1>
      <p class="hero-sub">
        Government employees, private sector workers, and pensioners.
        Apply online in minutes — funds delivered directly to you.
      </p>
      <div class="hero-cta">
        <a href="{{ route('borrower.register') }}" class="btn-hero">
          <i class="bi bi-play-fill"></i> Apply Now — Free
        </a>
      </div>
      <div class="hero-trust">
        <div class="trust-pill"><i class="bi bi-patch-check-fill"></i> CBL Regulated</div>
        <div class="trust-pill"><i class="bi bi-shield-lock-fill"></i> Secure Portal</div>
        <div class="trust-pill"><i class="bi bi-lightning-charge-fill"></i> Fast Approval</div>
        <div class="trust-pill"><i class="bi bi-eye-slash-fill"></i> No Hidden Fees</div>
      </div>
    </div>

    <!-- RIGHT: Calculator -->
    <div class="calc-card">
      <div class="calc-card-title">
        Loan Calculator
        <span>Estimate only</span>
      </div>

      <!-- Product selector -->
      <div style="margin-bottom:16px">
        <div class="cf-header" style="margin-bottom:8px">Select Product</div>
        <div class="calc-product">
          <button class="prod-btn active" onclick="setProduct(this,'govt',20000,6)" data-max="20000" data-maxterm="6">Government<br>Loan</button>
          <button class="prod-btn" onclick="setProduct(this,'priv',4000,6)" data-max="4000" data-maxterm="6">Private<br>Sector</button>
          <button class="prod-btn" onclick="setProduct(this,'pen',4000,6)" data-max="4000" data-maxterm="6">Pensioner<br>Loan</button>
        </div>
      </div>

      <div class="cf">
        <div class="cf-header">Loan Amount <span class="cf-val" id="c-amt-lbl">M 5,000</span></div>
        <input type="range" id="c-amt" min="100" max="20000" step="100" value="5000" oninput="calc()">
      </div>
      <div class="cf">
        <div class="cf-header">Loan Term <span class="cf-val" id="c-term-lbl">3 months</span></div>
        <input type="range" id="c-term" min="1" max="6" step="1" value="3" oninput="calc()">
      </div>

      <div class="calc-result">
        <div class="cr-item">
          <div class="cr-lbl">Monthly Payment</div>
          <div class="cr-val accent" id="c-monthly">M 4,067</div>
        </div>
        <div class="cr-item">
          <div class="cr-lbl">Total Repayable</div>
          <div class="cr-val" id="c-total">M 12,200</div>
        </div>
        <div class="cr-item" style="grid-column:span 2">
          <div class="cr-small" id="c-breakdown">Principal M5,000 · Initiation M2,000 · Interest M2,250 · Admin M150</div>
        </div>
      </div>

      <a href="{{ route('borrower.register') }}" class="calc-apply">
        <i class="bi bi-arrow-right-circle-fill"></i> Apply for this Loan
      </a>
      <div class="calc-disclaimer">15% flat rate · 40% initiation fee · M50/month admin</div>
    </div>

  </div>

  <!-- Stats bar -->
  <div class="hero-stats">
    <div class="stats-grid">
      <div class="stat-cell"><div class="stat-n">M20k</div><div class="stat-l">Max Loan</div></div>
      <div class="stat-cell"><div class="stat-n">3</div><div class="stat-l">Products</div></div>
      <div class="stat-cell"><div class="stat-n">Fast</div><div class="stat-l">Approval</div></div>
      <div class="stat-cell"><div class="stat-n">CBL</div><div class="stat-l">Licensed</div></div>
    </div>
  </div>

</section>

</div>
@endsection

@push('scripts')
<script>
document.body.dataset.transparentHeader = '1';

// Product limits
const limits = { govt:{max:20000,maxterm:6}, priv:{max:4000,maxterm:6}, pen:{max:4000,maxterm:6} };
let currentProduct = 'govt';

function setProduct(btn, key, max, maxterm) {
  document.querySelectorAll('.prod-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentProduct = key;
  const amt = document.getElementById('c-amt');
  amt.max = max;
  if (parseFloat(amt.value) > max) amt.value = max;
  document.getElementById('c-term').max = maxterm;
  calc();
}

function calc() {
  const a    = parseFloat(document.getElementById('c-amt').value);
  const t    = parseInt(document.getElementById('c-term').value);
  const rate = 0.15;
  document.getElementById('c-amt-lbl').textContent  = 'M ' + a.toLocaleString();
  document.getElementById('c-term-lbl').textContent = t + (t===1?' month':' months');
  const init     = Math.round(a * 0.40);
  const interest = Math.round(a * rate * t);
  const admin    = 50 * t;
  const total    = a + init + interest + admin;
  const monthly  = Math.round(total / t);
  document.getElementById('c-monthly').textContent  = 'M ' + monthly.toLocaleString();
  document.getElementById('c-total').textContent    = 'M ' + total.toLocaleString();
  document.getElementById('c-breakdown').textContent = `Principal M${a.toLocaleString()} · Initiation M${init.toLocaleString()} · Interest M${interest.toLocaleString()} · Admin M${admin.toLocaleString()}`;
}
calc();
</script>
@endpush
