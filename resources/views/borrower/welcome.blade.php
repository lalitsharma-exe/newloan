@extends('borrower.layouts.public')
@section('title','Home')
@section('header-class','transparent')

@push('page-styles')
<style>
/* ═══════════════════════════════════════════════════════
   HERO — true full-screen, zero gap to footer
═══════════════════════════════════════════════════════ */
.hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin: 0;
  padding: 0;
}

/* Dark navy base */
.hero::before {
  content: '';
  position: absolute; inset: 0; z-index: 0;
  background: linear-gradient(160deg, #050f06 0%, #0a2f1c 40%, #0f4527 100%);
}

/* Background video */
.hero-video {
  position: absolute; inset: 0; z-index: 1;
  width: 100%; height: 100%;
  object-fit: cover;
  opacity: .5;
}

/* Dark overlay on top of video */
.hero-overlay {
  position: absolute; inset: 0; z-index: 2;
  background: linear-gradient(160deg,
    rgba(5,15,6,.75) 0%,
    rgba(15,69,39,.58) 45%,
    rgba(5,15,6,.70) 100%);
}

/* Decorative orbs */
.hero-orb {
  position: absolute; z-index: 2; border-radius: 50%; pointer-events: none;
  animation: orb 14s ease-in-out infinite;
}
.hero-orb-1 {
  width: 560px; height: 560px; top: -140px; left: -120px;
  background: radial-gradient(circle, rgba(26,107,60,.1) 0%, transparent 70%);
}
.hero-orb-2 {
  width: 380px; height: 380px; bottom: -80px; right: 18%;
  background: radial-gradient(circle, rgba(26,107,60,.14) 0%, transparent 70%);
  animation-delay: 5s;
}
@keyframes orb {
  0%,100% { transform: translate(0,0) scale(1); }
  50%      { transform: translate(16px,-16px) scale(1.05); }
}

/* Main 2-column grid */
.hero-body {
  position: relative; z-index: 3;
  flex: 1;
  display: grid;
  grid-template-columns: 1fr 430px;
  gap: 40px;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 88px 32px 36px; /* top accounts for fixed header height */
  width: 100%;
}

/* ── LEFT: Text content ─────────────────────────────── */
.hero-kicker {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 11px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase;
  color: var(--accent); margin-bottom: 20px;
  opacity: 0; animation: rise .7s .3s forwards;
}
.hero-kicker::before { content: ''; width: 18px; height: 1px; background: var(--accent); }
.hero-kicker-dot {
  width: 6px; height: 6px; border-radius: 50%; background: var(--accent);
  animation: blink 2s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

.hero h1 {
  color: #fff;
  font-size: clamp(34px, 4.8vw, 64px);
  letter-spacing: -.03em; line-height: 1.07; margin-bottom: 18px;
  opacity: 0; animation: rise .8s .5s forwards;
}
.hero h1 em { font-style: italic; color: var(--accent2); }

.hero-sub {
  font-size: 16.5px; color: rgba(255,255,255,.68);
  line-height: 1.75; max-width: 460px; margin-bottom: 32px;
  opacity: 0; animation: rise .8s .7s forwards;
}

.hero-cta { 
  opacity: 0; animation: rise .8s .9s forwards; 
  display: flex; gap: 16px; align-items: center;
}
.btn-hero {
  display: inline-flex; align-items: center; gap: 10px;
  background: var(--accent); color: #fff;
  padding: 15px 34px; border-radius: 4px;
  font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 700;
  letter-spacing: .04em; text-decoration: none;
  transition: all .3s; box-shadow: 0 8px 28px rgba(201,148,58,.3);
}
.btn-hero:hover {
  background: var(--accent2); transform: translateY(-2px);
  box-shadow: 0 14px 36px rgba(201,148,58,.4);
}
.btn-hero-outline {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.05); color: #fff;
  padding: 13px 32px; border: 2px solid rgba(255,255,255,.2); border-radius: 4px;
  font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 700;
  letter-spacing: .04em; text-decoration: none;
  transition: all .3s; backdrop-filter: blur(4px);
}
.btn-hero-outline:hover {
  border-color: #fff; background: rgba(255,255,255,0.1); transform: translateY(-2px);
}

.hero-trust {
  display: flex; gap: 20px; flex-wrap: wrap;
  margin-top: 36px; padding-top: 24px;
  border-top: 1px solid rgba(255,255,255,.1);
  opacity: 0; animation: rise .8s 1.1s forwards;
}
.trust-pill {
  display: flex; align-items: center; gap: 7px;
  font-size: 12px; color: rgba(255,255,255,.58);
  font-family: 'DM Sans', sans-serif;
}
.trust-pill i { color: var(--accent); font-size: 13px; flex-shrink: 0; }

@keyframes rise {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── RIGHT: Calculator card ─────────────────────────── */
.calc-card {
  background: rgba(5,15,6,.78);
  border: 1px solid rgba(255,255,255,.12);
  backdrop-filter: blur(28px);
  -webkit-backdrop-filter: blur(28px);
  border-radius: 14px; padding: 26px 24px;
  opacity: 0; animation: rise 1s 1s forwards;
}
.calc-card-title {
  font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .13em; text-transform: uppercase;
  color: rgba(255,255,255,.4); margin-bottom: 20px;
  display: flex; align-items: center; justify-content: space-between;
}
.calc-card-title span { color: var(--accent); font-size: 10px; }

.cf { margin-bottom: 16px; }
.cf-header {
  display: flex; justify-content: space-between; align-items: center;
  font-size: 12.5px; color: rgba(255,255,255,.55);
  font-family: 'DM Sans', sans-serif; margin-bottom: 8px;
}
.cf-val { color: #fff; font-weight: 700; font-size: 13.5px; }
input[type=range] {
  width: 100%; height: 3px; border-radius: 99px;
  background: rgba(255,255,255,.15); accent-color: var(--accent);
  cursor: pointer; outline: none;
}

.calc-product { 
  margin-bottom: 16px; 
  display: grid; 
  grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); 
  gap: 6px; 
}
.prod-btn {
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
  border-radius: 5px; padding: 7px 4px; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: 10px; font-weight: 600;
  color: rgba(255,255,255,.5); text-align: center;
  transition: all .2s; letter-spacing: .03em; line-height: 1.3;
}
.prod-btn:hover, .prod-btn.active {
  background: rgba(201,148,58,.15); border-color: rgba(201,148,58,.4); color: var(--accent);
}

.calc-result {
  background: rgba(201,148,58,.07); border: 1px solid rgba(201,148,58,.18);
  border-radius: 9px; padding: 15px 17px; margin-top: 16px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.cr-lbl {
  font-size: 9.5px; font-weight: 600; color: rgba(255,255,255,.38);
  letter-spacing: .1em; text-transform: uppercase; margin-bottom: 4px;
}
.cr-val {
  font-family: 'Playfair Display', serif; font-size: 26px;
  font-weight: 700; color: #fff; line-height: 1;
}
.cr-val.accent { color: var(--accent); }
.cr-small {
  font-size: 11.5px; color: rgba(255,255,255,.4);
  font-family: 'DM Sans', sans-serif; margin-top: 6px; line-height: 1.4;
}

.calc-apply {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; margin-top: 14px; padding: 12px;
  background: var(--accent); color: #fff; border: none; border-radius: 5px;
  font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 700;
  letter-spacing: .04em; cursor: pointer; text-decoration: none; transition: all .25s;
}
.calc-apply:hover { background: var(--accent2); transform: translateY(-1px); }
.calc-disclaimer {
  text-align: center; margin-top: 8px; font-size: 10px;
  color: rgba(255,255,255,.28); font-family: 'DM Sans', sans-serif;
}

/* ── MOBILE CALC STRIP (shown below hero text on mobile) ── */
.hero-mobile-calc {
  display: none;
  position: relative; z-index: 3;
  background: rgba(5,15,6,.85);
  border-top: 1px solid rgba(255,255,255,.1);
  padding: 20px 20px;
}

/* ═══════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════ */
@media(max-width:960px) {
  .hero {
    height: auto;
    min-height: 100vh;
  }
  .hero-body {
    grid-template-columns: 1fr;
    padding: 100px 20px 60px;
    gap: 0;
  }
  /* Hide desktop calc card on tablet/mobile */
  .calc-card { display: none; }
  /* Show mobile calc strip */
  .hero-mobile-calc { display: block; }
}

@media(max-width:640px) {
  .hero-body { padding: 90px 18px 28px; width: 100%; max-width: 100vw; overflow: hidden; }
  .hero h1 { font-size: clamp(30px, 8vw, 46px); }
  .hero-sub { font-size: 15px; margin-bottom: 28px; }
  .btn-hero { padding: 13px 28px; font-size: 14px; }
  .hero-trust { gap: 14px; margin-top: 28px; padding-top: 20px; }
  .trust-pill { font-size: 11.5px; }
  .calc-product { grid-template-columns: 1fr; }
}

@media(max-width:380px) {
  .hero-body { padding: 84px 16px 24px; }
  .hero-trust { flex-direction: column; gap: 10px; }
  .btn-hero, .btn-hero-outline { width: 100%; justify-content: center; }
  .hero-cta { flex-direction: column; gap: 12px; }
}
</style>
@endpush

@section('content')
{{-- Wrapper: flex column so hero fills screen and footer sits flush --}}
<div style="display:flex;flex-direction:column;flex:1" data-transparent-header="1">

  <section class="hero">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>

    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="{{ config('app.background_video') }}" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>

    <div class="hero-body">

      {{-- LEFT: headline + CTA --}}
      <div>
        <div class="hero-kicker">
          <div class="hero-kicker-dot"></div>Licensed by Central Bank of Lesotho
        </div>
        <h1>Affordable loan<br>for <em>everyone in Lesotho</em></h1>
        <p class="hero-sub">
          Government employees, private sector workers, and pensioners.
          Apply online in minutes — funds delivered directly to you.
        </p>
        <div class="hero-cta">
          <a href="{{ route('borrower.register') }}" class="btn-hero">
            <i class="bi bi-play-fill"></i> Apply Now
          </a>
          <a href="{{ route('borrower.login') }}" class="btn-hero-outline">
            <i class="bi bi-person-circle"></i> Sign In
          </a>
        </div>
        <div class="hero-trust">
          <div class="trust-pill"><i class="bi bi-patch-check-fill"></i> CBL Regulated</div>
          <div class="trust-pill"><i class="bi bi-shield-lock-fill"></i> Secure Portal</div>
          <div class="trust-pill"><i class="bi bi-lightning-charge-fill"></i> Fast Approval</div>
          <div class="trust-pill"><i class="bi bi-eye-slash-fill"></i> No Hidden Fees</div>
        </div>
      </div>

      {{-- RIGHT: Calculator card (desktop only) --}}
      <div class="calc-card">
        <div class="calc-card-title">Loan Calculator <span>Estimate only</span></div>

        <div style="margin-bottom:14px">
          <div class="cf-header" style="margin-bottom:7px">Select Product</div>
          <div class="calc-product">
            <button class="prod-btn active" onclick="setProduct(this,'govt',20000,6)">Government<br>Loan</button>
            <button class="prod-btn" onclick="setProduct(this,'priv',4000,6)">Private<br>Sector</button>
            <button class="prod-btn" onclick="setProduct(this,'pen',4000,6)">Pensioner<br>Loan</button>
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
          <div><div class="cr-lbl">Monthly Payment</div><div class="cr-val accent" id="c-monthly">M 4,067</div></div>
          <div><div class="cr-val" id="c-total">M 12,200</div></div>
          <div style="grid-column:span 2"><div class="cr-small" id="c-breakdown">Principal M5,000 · Initiation M2,000 · Interest M2,250 · Admin M150</div></div>
        </div>

        <a href="{{ route('borrower.register') }}" class="calc-apply">
          <i class="bi bi-arrow-right-circle-fill"></i> Apply for this Loan
        </a>
        <div class="calc-disclaimer">20% monthly interest (reducing balance) · No initiation fee · No admin fee</div>
      </div>

    </div>
  </section>

  {{-- Mobile calculator strip (shown under hero text on phones) --}}
  <div class="hero-mobile-calc">
    <div style="max-width:480px;margin:0 auto">
      <div style="font-size:10.5px;font-weight:600;letter-spacing:.13em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:14px;display:flex;justify-content:space-between">
        Loan Calculator <span style="color:var(--accent)">Estimate only</span>
      </div>

      {{-- Product selector --}}
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:16px">
        <button class="prod-btn active" onclick="setProductM(this,'govt',20000,6)">Government<br>Loan</button>
        <button class="prod-btn" onclick="setProductM(this,'priv',4000,6)">Private<br>Sector</button>
        <button class="prod-btn" onclick="setProductM(this,'pen',4000,6)">Pensioner<br>Loan</button>
      </div>

      <div class="cf">
        <div class="cf-header">Amount <span class="cf-val" id="m-amt-lbl">M 5,000</span></div>
        <input type="range" id="m-amt" min="100" max="20000" step="100" value="5000" oninput="calcM()">
      </div>
      <div class="cf">
        <div class="cf-header">Term <span class="cf-val" id="m-term-lbl">3 months</span></div>
        <input type="range" id="m-term" min="1" max="6" step="1" value="3" oninput="calcM()">
      </div>

      <div class="calc-result">
        <div><div class="cr-lbl">Monthly Payment</div><div class="cr-val accent" id="m-monthly">M 2,374</div></div>
        <div><div class="cr-lbl">Total Repayable</div><div class="cr-val" id="m-total">M 7,122</div></div>
        <div style="grid-column:span 2"><div class="cr-small" id="m-breakdown">Principal M5,000 · Interest M2,122 · No Fees</div></div>
      </div>

      <a href="{{ route('borrower.register') }}" class="calc-apply" style="margin-top:12px">
        <i class="bi bi-arrow-right-circle-fill"></i> Apply for this Loan
      </a>
      <div class="calc-disclaimer" style="color:rgba(255,255,255,.28)">20% monthly interest (reducing balance) · No initiation fee · No admin fee</div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
document.body.dataset.transparentHeader = '1';

// ── Desktop calculator ───────────────────────────────
let currentMax = 20000;

function setProduct(btn, key, max, maxterm) {
  document.querySelectorAll('.calc-card .prod-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentMax = max;
  const amt = document.getElementById('c-amt');
  amt.max = max;
  if (parseFloat(amt.value) > max) amt.value = max;
  document.getElementById('c-term').max = maxterm;
  if (parseInt(document.getElementById('c-term').value) > maxterm) document.getElementById('c-term').value = maxterm;
  calc();
}

function calc() {
  const a = parseFloat(document.getElementById('c-amt').value);
  const t = parseInt(document.getElementById('c-term').value);
  document.getElementById('c-amt-lbl').textContent  = 'M ' + a.toLocaleString();
  document.getElementById('c-term-lbl').textContent = t + (t === 1 ? ' month' : ' months');
  const r = 0.20;
  const monthly = (r > 0 && t > 0) ? Math.round((a * r * Math.pow(1 + r, t)) / (Math.pow(1 + r, t) - 1)) : Math.round(a / t);
  const total   = monthly * t;
  const interest = total - a;
  document.getElementById('c-monthly').textContent   = 'M ' + monthly.toLocaleString();
  document.getElementById('c-total').textContent     = 'M ' + total.toLocaleString();
  document.getElementById('c-breakdown').textContent =
    `Principal M${a.toLocaleString()} · Interest M${interest.toLocaleString()} · No Extra Fees`;
}

// ── Mobile calculator ────────────────────────────────
let mobileMax = 20000;

function setProductM(btn, key, max, maxterm) {
  document.querySelectorAll('.hero-mobile-calc .prod-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  mobileMax = max;
  const amt = document.getElementById('m-amt');
  amt.max = max;
  if (parseFloat(amt.value) > max) amt.value = max;
  document.getElementById('m-term').max = maxterm;
  if (parseInt(document.getElementById('m-term').value) > maxterm) document.getElementById('m-term').value = maxterm;
  calcM();
}

function calcM() {
  const a = parseFloat(document.getElementById('m-amt').value);
  const t = parseInt(document.getElementById('m-term').value);
  document.getElementById('m-amt-lbl').textContent  = 'M ' + a.toLocaleString();
  document.getElementById('m-term-lbl').textContent = t + (t === 1 ? ' month' : ' months');
  const r = 0.20;
  const monthly = (r > 0 && t > 0) ? Math.round((a * r * Math.pow(1 + r, t)) / (Math.pow(1 + r, t) - 1)) : Math.round(a / t);
  const total   = monthly * t;
  const interest = total - a;
  document.getElementById('m-monthly').textContent  = 'M ' + monthly.toLocaleString();
  document.getElementById('m-total').textContent    = 'M ' + total.toLocaleString();
  document.getElementById('m-breakdown').textContent =
    `Principal M${a.toLocaleString()} · Initiation M${init.toLocaleString()} · Interest M${interest.toLocaleString()} · Admin M${admin.toLocaleString()}`;
}

calc();
calcM();
</script>
@endpush