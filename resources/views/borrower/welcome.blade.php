@extends('borrower.layouts.public')
@section('title', 'Home')
@section('meta_desc', 'MyLoan Limited — Simple, Fast and Secure loans for government employees, private sector workers and pensioners in Lesotho.')
@section('header-class', 'transparent')

@push('page-styles')
<style>
/* ── HERO ─────────────────────────────────────────────── */
.hero {
  position: relative; min-height: 100vh;
  display: flex; align-items: center;
  background: var(--forest); overflow: hidden;
  padding-top: 72px;
}
/* Deep layered background — shows when video hasn't loaded yet */
.hero::before {
  content: '';
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 80% 60% at 60% 40%, rgba(46,125,69,.35) 0%, transparent 70%),
    radial-gradient(ellipse 50% 80% at 10% 80%, rgba(15,61,31,.8) 0%, transparent 60%),
    linear-gradient(160deg, #081a0e 0%, #0f3d1f 45%, #1a5c2e 100%);
}
.hero-video {
  position: absolute; inset: 0; width: 100%; height: 100%;
  object-fit: cover; opacity: .32; z-index: 1;
}
.hero-overlay {
  position: absolute; inset: 0; z-index: 2;
  background: linear-gradient(160deg, rgba(8,22,12,.88) 0%, rgba(15,61,31,.62) 50%, rgba(8,22,12,.82) 100%);
}
.hero-noise {
  position: absolute; inset: 0; z-index: 3; opacity: .04;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}
.hero-inner {
  position: relative; z-index: 4;
  max-width: 1160px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: 1fr 420px; gap: 80px; align-items: center;
}
.hero-kicker {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .16em; text-transform: uppercase;
  color: var(--light); margin-bottom: 24px;
  opacity: 0; animation: fadeIn .8s .3s forwards;
}
.hero-kicker::before { content: ''; width: 22px; height: 1px; background: var(--light); }
.hero h1 {
  color: #fff; font-size: clamp(44px, 6vw, 80px);
  letter-spacing: -.03em; line-height: 1.06;
  margin-bottom: 24px;
  opacity: 0; animation: fadeIn .9s .5s forwards;
}
.hero h1 em { font-style: italic; color: var(--light); }
.hero-lead {
  font-size: 18px; color: rgba(255,255,255,.62);
  line-height: 1.75; max-width: 520px; margin-bottom: 40px;
  opacity: 0; animation: fadeIn .9s .7s forwards;
}
.hero-actions {
  display: flex; gap: 14px; flex-wrap: wrap;
  opacity: 0; animation: fadeIn .9s .9s forwards;
}
.hero-trust {
  margin-top: 56px; padding-top: 32px;
  border-top: 1px solid rgba(255,255,255,.1);
  display: flex; gap: 32px; flex-wrap: wrap;
  opacity: 0; animation: fadeIn .9s 1.1s forwards;
}
.trust-item { display: flex; align-items: center; gap: 9px; }
.trust-item i { color: var(--light); font-size: 15px; }
.trust-item span { font-size: 13px; color: rgba(255,255,255,.5); font-family: 'Outfit', sans-serif; }

/* Loan card floating */
.hero-card {
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.1);
  backdrop-filter: blur(20px); border-radius: 12px; padding: 32px;
  opacity: 0; animation: fadeIn 1s 1s forwards;
}
.hero-card-title { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.4); margin-bottom: 20px; }
.calc-field { margin-bottom: 20px; }
.calc-field label { display: flex; justify-content: space-between; font-size: 13px; color: rgba(255,255,255,.6); font-family: 'Outfit', sans-serif; margin-bottom: 8px; }
.calc-field label span { color: var(--light); font-weight: 600; }
input[type=range] { width: 100%; height: 3px; border-radius: 99px; background: rgba(255,255,255,.15); accent-color: var(--light); cursor: pointer; outline: none; }
.calc-result-row { background: rgba(76,175,105,.1); border: 1px solid rgba(76,175,105,.2); border-radius: 8px; padding: 16px 20px; margin-top: 22px; display: flex; align-items: center; justify-content: space-between; }
.calc-result-label { font-size: 12px; color: rgba(255,255,255,.5); font-family: 'Outfit', sans-serif; }
.calc-result-value { font-family: 'Cormorant Garamond', serif; font-size: 36px; font-weight: 700; color: #fff; }
.calc-result-value span { font-size: 16px; color: var(--light); }
.hero-card .btn { width: 100%; justify-content: center; margin-top: 16px; }

/* Stats bar */
.hero-stats-bar {
  position: relative; z-index: 4; width: 100%;
  background: rgba(6,16,9,.92);
  backdrop-filter: blur(16px);
  border-top: 1px solid rgba(76,175,105,.15);
  margin-top: 60px;
}
.stats-inner {
  max-width: 1160px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: repeat(4,1fr);
}
.stat-item {
  padding: 26px 20px; border-right: 1px solid rgba(255,255,255,.07);
  text-align: center;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.stat-item:last-child { border-right: none; }
.stat-num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 32px; font-weight: 700; color: #fff; line-height: 1;
}
.stat-lbl {
  font-family: 'Outfit', sans-serif;
  font-size: 11px; color: rgba(255,255,255,.4);
  letter-spacing: .1em; text-transform: uppercase; margin-top: 6px;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

/* ── HOW IT WORKS ─────────────────────────────────────── */
.steps-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 2px; margin-top: 60px; background: var(--border); border-radius: 8px; overflow: hidden; }
.step-item { background: var(--white); padding: 36px 28px; position: relative; transition: background .3s; }
.step-item:hover { background: var(--ivory); }
.step-number {
  font-family: 'Cormorant Garamond', serif; font-size: 72px; font-weight: 700;
  color: rgba(26,92,46,.08); line-height: 1; margin-bottom: 20px;
  display: block; transition: color .3s;
}
.step-item:hover .step-number { color: rgba(26,92,46,.14); }
.step-item h3 { font-size: 20px; margin-bottom: 10px; color: var(--ink); }
.step-item p { font-size: 14px; line-height: 1.7; }

/* ── PRODUCTS ─────────────────────────────────────────── */
.products-sec { background: var(--ivory); }
.product-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 24px; margin-top: 56px; }
.product-tile {
  background: var(--white); border-radius: 8px;
  border: 1px solid var(--border); overflow: hidden;
  transition: transform .3s, box-shadow .3s;
}
.product-tile:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(15,61,31,.1); }
.product-tile-top { height: 6px; background: linear-gradient(90deg, var(--green), var(--light)); }
.product-tile-body { padding: 32px 30px 36px; }
.product-tile-icon { width: 48px; height: 48px; border-radius: 8px; background: rgba(26,92,46,.07); display: flex; align-items: center; justify-content: center; color: var(--green); font-size: 22px; margin-bottom: 22px; }
.product-tile h3 { font-size: 24px; color: var(--ink); margin-bottom: 6px; }
.product-range { font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600; color: var(--gold); letter-spacing: .04em; margin-bottom: 16px; display: block; }
.product-tile p { font-size: 14px; line-height: 1.75; margin-bottom: 28px; }
.product-tile-cta { font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600; color: var(--green); text-decoration: none; display: flex; align-items: center; gap: 6px; transition: gap .2s; }
.product-tile:hover .product-tile-cta { gap: 10px; }

/* ── ABOUT STRIP ──────────────────────────────────────── */
.about-strip { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
.about-image-stack { position: relative; height: 500px; }
.about-img-main {
  width: 78%; height: 400px; object-fit: cover;
  border-radius: 6px; position: absolute; bottom: 0; right: 0;
  filter: grayscale(20%);
}
.about-img-accent {
  width: 52%; height: 250px; object-fit: cover;
  border-radius: 6px; position: absolute; top: 0; left: 0;
  filter: grayscale(20%); border: 6px solid var(--white);
  box-shadow: 0 12px 40px rgba(0,0,0,.12);
}
.about-licence {
  display: flex; align-items: flex-start; gap: 12px;
  background: var(--ivory2); border-left: 3px solid var(--gold);
  padding: 18px 20px; border-radius: 0 6px 6px 0; margin-top: 28px;
}
.about-licence i { color: var(--gold); font-size: 18px; margin-top: 2px; flex-shrink: 0; }
.about-licence p { font-size: 13px; line-height: 1.7; color: var(--slate); }

/* ── WHY ──────────────────────────────────────────────── */
.why-sec { background: var(--forest); color: #fff; }
.why-sec h2 { color: #fff; }
.why-sec .ssub { color: rgba(255,255,255,.5); }
.why-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1px; margin-top: 56px; background: rgba(255,255,255,.07); }
.why-item { background: var(--forest); padding: 36px 32px; transition: background .3s; }
.why-item:hover { background: rgba(46,125,69,.25); }
.why-item-icon { color: var(--light); font-size: 24px; margin-bottom: 18px; }
.why-item h4 { font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 10px; letter-spacing: 0; text-transform: none; }
.why-item p { font-size: 14px; color: rgba(255,255,255,.45); line-height: 1.7; }

/* ── TESTIMONIALS ─────────────────────────────────────── */
.testi-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 24px; margin-top: 56px; }
.testi-card { padding: 36px 32px; border: 1px solid var(--border); border-radius: 6px; transition: border-color .3s, box-shadow .3s; }
.testi-card:hover { border-color: rgba(26,92,46,.3); box-shadow: 0 12px 36px rgba(15,61,31,.08); }
.testi-quote { font-family: 'Cormorant Garamond', serif; font-size: 60px; color: var(--light); line-height: .8; margin-bottom: 18px; }
.testi-text { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-style: italic; color: var(--ink); line-height: 1.65; margin-bottom: 24px; }
.testi-author { display: flex; align-items: center; gap: 12px; padding-top: 20px; border-top: 1px solid var(--border); }
.testi-av { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg,var(--green),var(--mid)); display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-weight: 700; font-size: 18px; color: #fff; flex-shrink: 0; }
.testi-name { font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600; color: var(--ink); }
.testi-role { font-size: 12px; color: var(--slate); }

/* ── CTA STRIP ────────────────────────────────────────── */
.cta-strip { background: var(--green); color: #fff; padding: 80px 0; }
.cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 32px; flex-wrap: wrap; }
.cta-strip h2 { font-size: clamp(28px,3.5vw,44px); color: #fff; max-width: 520px; }
.cta-strip p { font-size: 16px; color: rgba(255,255,255,.7); margin-top: 8px; }

/* ── RESPONSIVE ─────────────────────────────────────── */
@media(max-width:900px){
  .hero-inner{grid-template-columns:1fr}
  .hero-card{display:none}
  .stats-inner{flex-wrap:wrap}
  .stat-item{flex:1 1 50%;border-bottom:1px solid rgba(255,255,255,.06)}
  .steps-grid{grid-template-columns:1fr 1fr}
  .product-grid,.why-grid,.testi-grid{grid-template-columns:1fr}
  .about-strip{grid-template-columns:1fr}
  .about-image-stack{height:280px}
  .cta-inner{flex-direction:column;align-items:flex-start}
}
@media(max-width:580px){
  .steps-grid{grid-template-columns:1fr}
  .hero-trust{gap:20px}
}
</style>
@endpush

@section('content')
<div data-transparent-header="1" id="page-root">

{{-- ── HERO ─────────────────────────────────────────────── --}}
<section class="hero">
  {{-- VIDEO: Upload your file to /storage/app/public/video/hero.mp4 then run php artisan storage:link --}}
  <video class="hero-video" autoplay muted loop playsinline preload="auto">
    <source src="{{ asset('storage/video/hero.mp4') }}" type="video/mp4">
    {{-- Fallback: video not required — hero has rich CSS gradient background --}}
  </video>
  <div class="hero-overlay"></div>
  <div class="hero-noise"></div>

  <div style="position:relative;z-index:4;width:100%">
    <div class="hero-inner">
      <div>
        <div class="hero-kicker">Licensed · Central Bank of Lesotho</div>
        <h1>Financial<br>Solutions Built<br>for <em>Lesotho</em></h1>
        <p class="hero-lead">Simple, transparent loans for government employees, private sector workers, and pensioners. Apply online in minutes — funds delivered directly to you.</p>
        <div class="hero-actions">
          <a href="{{ route('borrower.register') }}" class="btn btn-gold btn-lg">Apply for a Loan</a>
          <a href="{{ route('products') }}" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.3);color:#fff">View Products</a>
        </div>
        <div class="hero-trust">
          <div class="trust-item"><i class="bi bi-patch-check-fill"></i><span>CBL Regulated</span></div>
          <div class="trust-item"><i class="bi bi-shield-lock-fill"></i><span>Secure Portal</span></div>
          <div class="trust-item"><i class="bi bi-lightning-charge-fill"></i><span>Fast Approval</span></div>
          <div class="trust-item"><i class="bi bi-eye-slash-fill"></i><span>No Hidden Fees</span></div>
        </div>
      </div>

      {{-- Inline calculator card --}}
      <div class="hero-card">
        <div class="hero-card-title">Estimate Your Repayment</div>
        <div class="calc-field">
          <label>Loan Amount <span id="hc-amt-label">M 5,000</span></label>
          <input type="range" id="hc-amt" min="500" max="20000" step="500" value="5000" oninput="hcalc()">
        </div>
        <div class="calc-field">
          <label>Term <span id="hc-term-label">3 months</span></label>
          <input type="range" id="hc-term" min="1" max="6" step="1" value="3" oninput="hcalc()">
        </div>
        <div class="calc-result-row">
          <div>
            <div class="calc-result-label">Est. Monthly Payment</div>
            <div class="calc-result-value"><span>M</span> <span id="hc-monthly">4,067</span></div>
          </div>
          <div style="text-align:right">
            <div class="calc-result-label">Total Repayable</div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;color:rgba(255,255,255,.65)" id="hc-total">M 12,200</div>
          </div>
        </div>
        <a href="{{ route('borrower.register') }}" class="btn btn-gold">Apply for this Amount</a>
        <div style="text-align:center;margin-top:12px;font-size:11px;color:rgba(255,255,255,.3);font-family:'Outfit',sans-serif">Estimate only · 15% flat · 40% initiation · M50 admin/mo</div>
      </div>
    </div>

    {{-- Stats bar --}}
    <div class="hero-stats-bar">
      <div class="stats-inner">
        <div class="stat-item"><div class="stat-num">M20,000</div><div class="stat-lbl">Maximum Loan</div></div>
        <div class="stat-item"><div class="stat-num">3</div><div class="stat-lbl">Loan Products</div></div>
        <div class="stat-item"><div class="stat-num">Fast</div><div class="stat-lbl">Approval Process</div></div>
        <div class="stat-item"><div class="stat-num">CBL</div><div class="stat-lbl">Licensed &amp; Regulated</div></div>
      </div>
    </div>
  </div>
</section>

{{-- ── HOW IT WORKS ─────────────────────────────────────── --}}
<section>
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:560px;margin:0 auto">
      <div class="eyebrow" style="justify-content:center">Simple Process</div>
      <h2>Four Steps to Your Loan</h2>
      <div class="divider divider-center"></div>
      <p>From application to funds — our process is designed to be clear, fast, and completely transparent.</p>
    </div>
    <div class="steps-grid">
      @foreach([
        ['01','Register','Create your free account in under two minutes using your phone number and national ID.'],
        ['02','Apply','Complete the online application and upload your supporting documents securely.'],
        ['03','Get Approved','Our team reviews your application. You receive a decision notification promptly.'],
        ['04','Receive Funds','Approved funds are transferred directly to your bank account or mobile money wallet.'],
      ] as $i => [$n, $t, $d])
      <div class="step-item reveal d{{ $i+1 }}">
        <span class="step-number">{{ $n }}</span>
        <h3>{{ $t }}</h3>
        <p>{{ $d }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── PRODUCTS ─────────────────────────────────────────── --}}
<section class="products-sec" id="products">
  <div class="container">
    <div class="reveal">
      <div class="eyebrow">Our Products</div>
      <h2>Loans Designed<br>for Every Sector</h2>
      <div class="divider"></div>
      <p class="ssub">We offer targeted lending solutions built around the specific needs of each employment group in Lesotho.</p>
    </div>
    <div class="product-grid">
      @foreach([
        ['Government Employee Loan','M100 – M20,000','briefcase','Permanent employees in government ministries, departments and agencies. Teachers, nurses, police, civil servants, and all public sector workers with regular government salaries.'],
        ['Private Sector Loan','M100 – M4,000','building','Permanent and contract employees in private companies and organisations with stable monthly salaries and verifiable bank or mobile money payment history.'],
        ['Pensioner Loan','M100 – M4,000','heart','Life after retirement should be peaceful and dignified. Unexpected expenses should not cause stress. Affordable loans specially designed for pensioners.'],
      ] as $i => [$name, $range, $ic, $desc])
      <div class="product-tile reveal d{{ $i+1 }}">
        <div class="product-tile-top"></div>
        <div class="product-tile-body">
          <div class="product-tile-icon"><i class="bi bi-{{ $ic }}-fill"></i></div>
          <h3>{{ $name }}</h3>
          <span class="product-range">{{ $range }}</span>
          <p>{{ $desc }}</p>
          <a href="{{ route('products') }}" class="product-tile-cta">Learn more <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── ABOUT ─────────────────────────────────────────────── --}}
<section id="about">
  <div class="container">
    <div class="about-strip">
      <div class="about-image-stack reveal-left">
        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&q=80" alt="MyLoan team" class="about-img-main">
        <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&q=80" alt="MyLoan office" class="about-img-accent">
      </div>
      <div class="reveal">
        <div class="eyebrow">About MyLoan</div>
        <h2>Delivering the Best Customer Experience</h2>
        <div class="divider"></div>
        <p style="margin-bottom:18px"><strong style="color:var(--ink)">Our Mission</strong> — To provide simple, fast, and secure financial solutions that empower people to meet their financial needs.</p>
        <p><strong style="color:var(--ink)">Our Vision</strong> — To become a leading and trusted financial services provider in Lesotho, known for integrity, transparency, and customer care.</p>
        <div class="about-licence">
          <i class="bi bi-patch-check-fill"></i>
          <p>Myloan Limited is licensed under the Financial Institutions Act 2012 and Financial Institutions (Amendment) Regulations 2014, as amended in 2018, as a <strong>Credit Only Micro Finance Institution Tier II</strong> and regulated by the Central Bank of Lesotho.</p>
        </div>
        <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap">
          <a href="{{ route('about') }}" class="btn btn-primary">Our Full Story</a>
          <a href="{{ route('contact') }}" class="btn btn-outline">Get in Touch</a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── WHY ───────────────────────────────────────────────── --}}
<section class="why-sec">
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:560px;margin:0 auto">
      <div class="eyebrow" style="justify-content:center;color:var(--light)"><span style="background:var(--light)"></span>Why Choose Us</div>
      <h2>Your Trusted Financial Partner</h2>
      <div class="divider divider-center" style="background:var(--gold)"></div>
      <p style="color:rgba(255,255,255,.5)">Everything we do is built on the principles of trust, transparency, and respect for our clients.</p>
    </div>
    <div class="why-grid">
      @foreach([
        ['lightning-charge-fill','Fast Processing','Applications reviewed promptly with minimal waiting from submission to approval decision.'],
        ['shield-check-fill','Licensed &amp; Secure','Fully regulated by the Central Bank of Lesotho. Your data and funds are fully protected.'],
        ['phone-fill','Apply from Anywhere','Our digital portal lets you apply from your phone, at home, at work — anytime, anywhere.'],
        ['people-fill','Dedicated Support','Our team guides you through every step. Reach us by phone, email, or visit our office.'],
        ['cash-coin','No Hidden Charges','All fees and charges are disclosed upfront before you commit to anything.'],
        ['graph-up','Build Your Future','Timely repayments build your credit profile and improve eligibility for larger loans.'],
      ] as $i => [$ic, $t, $d])
      <div class="why-item reveal d{{ ($i%3)+1 }}">
        <div class="why-item-icon"><i class="bi bi-{{ $ic }}"></i></div>
        <h4>{{ $t }}</h4>
        <p>{!! $d !!}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── TESTIMONIALS ─────────────────────────────────────── --}}
<section>
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:480px;margin:0 auto">
      <div class="eyebrow" style="justify-content:center">Client Stories</div>
      <h2>What Our Clients Say</h2>
      <div class="divider divider-center"></div>
    </div>
    <div class="testi-grid">
      @foreach([
        ['Thabo M.','Government Employee, Maseru','The process was incredibly smooth. I applied on Monday and had money in my account by Wednesday. MyLoan treated me with complete professionalism.'],
        ['Lineo K.','Private Sector, Leribe','I was cautious about taking a loan, but the team was patient and transparent. I knew every fee before signing. That honesty made all the difference.'],
        ['Maria S.','Pensioner, Mafeteng','As a pensioner, I worried no lender would take me seriously. MyLoan listened, explained everything clearly, and gave me a repayment plan I can manage.'],
      ] as $i => [$name, $role, $text])
      <div class="testi-card reveal d{{ $i+1 }}">
        <div class="testi-quote">&ldquo;</div>
        <div class="testi-text">{{ $text }}</div>
        <div class="testi-author">
          <div class="testi-av">{{ substr($name,0,1) }}</div>
          <div><div class="testi-name">{{ $name }}</div><div class="testi-role">{{ $role }}</div></div>
          <div style="margin-left:auto;color:var(--gold);font-size:13px">★★★★★</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── CTA STRIP ─────────────────────────────────────────── --}}
<section class="cta-strip">
  <div class="container">
    <div class="cta-inner">
      <div class="reveal">
        <h2>Ready to Take the First Step?</h2>
        <p>Apply online in minutes. Transparent terms. Funds delivered directly to you.</p>
      </div>
      <div class="reveal d2" style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="{{ route('borrower.register') }}" class="btn btn-gold btn-lg">Apply Now — It's Free</a>
        <a href="{{ route('contact') }}" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.35);color:#fff">Contact Us</a>
      </div>
    </div>
  </div>
</section>

</div>
@endsection

@push('scripts')
<script>
// Hero transparent header
document.body.dataset.transparentHeader = '1';

// Hero calculator
function hcalc() {
  const a = parseFloat(document.getElementById('hc-amt').value);
  const t = parseInt(document.getElementById('hc-term').value);
  const r = 0.15;
  document.getElementById('hc-amt-label').textContent  = 'M ' + a.toLocaleString();
  document.getElementById('hc-term-label').textContent = t + (t===1?' month':' months');
  const init    = a * 0.40;
  const interest= a * r * t;
  const admin   = 50 * t;
  const total   = a + init + interest + admin;
  const monthly = Math.round(total / t);
  document.getElementById('hc-monthly').textContent = monthly.toLocaleString();
  document.getElementById('hc-total').textContent   = 'M ' + Math.round(total).toLocaleString();
}
hcalc();
</script>
@endpush
