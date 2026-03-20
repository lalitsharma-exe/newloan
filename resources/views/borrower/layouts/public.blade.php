<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="@yield('meta_desc','MyLoan Limited — Simple, Fast and Secure loans for Lesotho. Government, Private Sector and Pensioner loans up to M20,000.')">
<title>@yield('title','MyLoan') — MyLoan Limited</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ─────────────────────────────────────────────────────────
   DESIGN SYSTEM
   Fonts:   Cormorant Garamond (display/headings) + Outfit (body)
   Palette: Deep forest green + warm ivory + charcoal
   Tone:    Refined, trustworthy, mature — like a private bank
───────────────────────────────────────────────────────── */
:root {
  --forest:   #0f3d1f;
  --green:    #1a5c2e;
  --mid:      #2e7d45;
  --light:    #4caf69;
  --ivory:    #faf8f3;
  --ivory2:   #f2efe7;
  --charcoal: #1a1f1c;
  --ink:      #2c3430;
  --slate:    #5a6b61;
  --border:   #dde5df;
  --gold:     #b8913a;
  --white:    #ffffff;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; font-size: 16px; }
body {
  font-family: 'Outfit', sans-serif;
  font-weight: 400;
  color: var(--ink);
  background: var(--white);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

/* ── TYPOGRAPHY ─────────────────────────────────────────── */
.display { font-family: 'Cormorant Garamond', Georgia, serif; }
h1, h2, h3 { font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 600; line-height: 1.15; letter-spacing: -.02em; }
h1 { font-size: clamp(40px, 5.5vw, 74px); }
h2 { font-size: clamp(28px, 3.8vw, 52px); }
h3 { font-size: clamp(20px, 2.2vw, 28px); font-weight: 500; }
h4 { font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
p { font-size: 16px; line-height: 1.75; color: var(--slate); }
a { color: inherit; }

/* ── LAYOUT HELPERS ─────────────────────────────────────── */
.container { max-width: 1160px; margin: 0 auto; padding: 0 32px; }
.container-narrow { max-width: 860px; margin: 0 auto; padding: 0 32px; }
section { padding: 100px 0; }

/* ── EYEBROW LABEL ──────────────────────────────────────── */
.eyebrow {
  display: inline-flex; align-items: center; gap: 10px;
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .14em; text-transform: uppercase; color: var(--mid);
  margin-bottom: 18px;
}
.eyebrow::before { content: ''; width: 28px; height: 1px; background: var(--mid); }

/* ── BUTTONS ────────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 13px 28px; border-radius: 4px;
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600;
  letter-spacing: .03em; text-decoration: none; cursor: pointer;
  border: none; transition: all .25s ease; white-space: nowrap;
}
.btn-primary { background: var(--green); color: var(--white); }
.btn-primary:hover { background: var(--forest); transform: translateY(-1px); box-shadow: 0 6px 24px rgba(15,61,31,.3); }
.btn-outline { background: transparent; color: var(--green); border: 1.5px solid var(--green); }
.btn-outline:hover { background: var(--green); color: var(--white); }
.btn-white { background: var(--white); color: var(--green); }
.btn-white:hover { background: var(--ivory); transform: translateY(-1px); }
.btn-gold { background: var(--gold); color: var(--white); }
.btn-sm { padding: 9px 20px; font-size: 13px; }
.btn-lg { padding: 16px 36px; font-size: 15px; }

/* ── DIVIDER ────────────────────────────────────────────── */
.divider { width: 48px; height: 2px; background: var(--gold); margin: 20px 0 28px; }
.divider-center { margin: 20px auto 28px; }

/* ── BADGE ──────────────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 12px; border-radius: 2px;
  font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
}
.badge-green { background: rgba(26,92,46,.1); color: var(--green); }
.badge-gold  { background: rgba(184,145,58,.1); color: var(--gold); }
.badge-slate { background: rgba(90,107,97,.1); color: var(--slate); }

/* ── REVEAL ANIMATIONS ──────────────────────────────────── */
.reveal { opacity: 0; transform: translateY(24px); transition: opacity .65s ease, transform .65s ease; }
.reveal.in { opacity: 1; transform: translateY(0); }
.reveal-left { opacity: 0; transform: translateX(-24px); transition: opacity .65s ease, transform .65s ease; }
.reveal-left.in { opacity: 1; transform: translateX(0); }
.d1 { transition-delay: .08s; }
.d2 { transition-delay: .16s; }
.d3 { transition-delay: .24s; }
.d4 { transition-delay: .32s; }

/* ─────────────────────────────────────────────────────────
   HEADER
───────────────────────────────────────────────────────── */
#header {
  position: fixed; top: 0; left: 0; right: 0; z-index: 900;
  height: 72px; transition: all .35s ease;
}
#header.transparent { background: transparent; border-bottom: 1px solid transparent; }
#header.solid {
  background: rgba(10,28,14,.96);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(76,175,105,.12);
}
.header-inner {
  height: 72px; display: flex; align-items: center; gap: 0;
  max-width: 1160px; margin: 0 auto; padding: 0 32px;
}
/* Logo */
.logo { display: flex; align-items: center; text-decoration: none; }
.logo img { display: block; }
/* Nav links */
.nav-links { display: flex; align-items: center; gap: 2px; flex: 1; }
.nav-links a {
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 400;
  color: rgba(255,255,255,.65); text-decoration: none;
  padding: 8px 14px; border-radius: 4px; transition: all .2s;
  letter-spacing: .01em;
}
.nav-links a:hover { color: #fff; background: rgba(255,255,255,.07); }
.nav-links a.active { color: #fff; }
.nav-actions { display: flex; align-items: center; gap: 10px; }
.nav-login {
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 500;
  color: rgba(255,255,255,.7); text-decoration: none;
  padding: 8px 16px; transition: color .2s;
}
.nav-login:hover { color: #fff; }
.nav-apply {
  background: var(--gold); color: var(--white);
  padding: 9px 22px; border-radius: 4px;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
  letter-spacing: .04em; text-decoration: none; transition: all .25s;
}
.nav-apply:hover { background: #a07a2e; transform: translateY(-1px); }
.nav-mobile-btn { display: none; background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; padding: 6px; }

/* Mobile nav */
#mobile-nav {
  display: none; position: fixed; inset: 0; z-index: 899;
  background: rgba(10,28,14,.97); padding: 100px 32px 40px;
  flex-direction: column;
}
#mobile-nav a {
  font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 600;
  color: rgba(255,255,255,.8); text-decoration: none;
  padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.06);
  transition: color .2s;
}
#mobile-nav a:hover { color: #fff; }
.mobile-close { position: absolute; top: 22px; right: 24px; background: none; border: none; color: rgba(255,255,255,.6); font-size: 26px; cursor: pointer; }

/* ─────────────────────────────────────────────────────────
   FOOTER
───────────────────────────────────────────────────────── */
footer {
  background: var(--charcoal);
  color: rgba(255,255,255,.55);
  padding: 80px 0 0;
}
.footer-grid {
  display: grid;
  grid-template-columns: 2.2fr 1fr 1fr 1.2fr;
  gap: 56px;
  padding-bottom: 60px;
  border-bottom: 1px solid rgba(255,255,255,.07);
}
.footer-brand .logo-name { font-size: 24px; color: #fff; }
.footer-desc {
  font-size: 14px; color: rgba(255,255,255,.42);
  line-height: 1.8; margin: 16px 0 24px; max-width: 280px;
}
.footer-contact-item {
  display: flex; align-items: center; gap: 10px;
  font-size: 14px; color: rgba(255,255,255,.5); text-decoration: none;
  margin-bottom: 12px; transition: color .2s;
}
.footer-contact-item:hover { color: var(--light); }
.footer-contact-item i { color: var(--light); font-size: 14px; width: 18px; flex-shrink: 0; }
.footer-col-title {
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  color: rgba(255,255,255,.35); letter-spacing: .14em; text-transform: uppercase;
  margin-bottom: 22px;
}
.footer-col a {
  display: block; font-size: 14px; color: rgba(255,255,255,.5);
  text-decoration: none; margin-bottom: 12px; transition: color .2s;
  font-family: 'Outfit', sans-serif;
}
.footer-col a:hover { color: var(--light); }
.footer-cbk {
  background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
  border-radius: 6px; padding: 16px 18px;
  font-size: 12px; color: rgba(255,255,255,.35); line-height: 1.75;
  margin-top: 4px;
}
.footer-bottom {
  padding: 24px 0; display: flex; align-items: center;
  justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.footer-copy { font-size: 13px; color: rgba(255,255,255,.3); }
.footer-social { display: flex; gap: 8px; }
.footer-social a {
  width: 34px; height: 34px; border-radius: 4px;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,.45); font-size: 14px; text-decoration: none; transition: all .2s;
}
.footer-social a:hover { background: rgba(76,175,105,.18); border-color: rgba(76,175,105,.3); color: var(--light); }

/* ─────────────────────────────────────────────────────────
   RESPONSIVE
───────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .footer-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
}
@media (max-width: 768px) {
  .container, .container-narrow { padding: 0 20px; }
  section { padding: 72px 0; }
  .header-inner { padding: 0 20px; }
  .nav-links, .nav-actions { display: none; }
  .nav-mobile-btn { display: block; margin-left: auto; }
  .footer-grid { grid-template-columns: 1fr; gap: 36px; }
  footer { padding: 56px 0 0; }
  .footer-bottom { flex-direction: column; align-items: flex-start; gap: 14px; }
}
@media (max-width: 480px) {
  h1 { font-size: 36px; }
  h2 { font-size: 28px; }
}

@stack('page-styles')
</style>
</head>
<body>

<!-- HEADER -->
<header id="header" class="@yield('header-class','solid')">
  <div class="header-inner">
    <a href="{{ route('home') }}" class="logo" style="margin-right:48px">
      <img src="{{ asset('storage/video/logo.webp') }}" alt="MyLoan" style="height:40px;width:auto;display:block;object-fit:contain">
    </a>

    <nav class="nav-links">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
      <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>
      <a href="{{ route('products') }}" class="{{ request()->routeIs('products') ? 'active' : '' }}">Products</a>
      <a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a>
      <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
    </nav>

    <div class="nav-actions">
      <a href="{{ route('borrower.login') }}" class="nav-login">Sign In</a>
      <a href="{{ route('borrower.register') }}" class="nav-apply">Apply Now</a>
    </div>

    <button class="nav-mobile-btn" onclick="toggleMobileNav()" aria-label="Menu">
      <i class="bi bi-list" id="nav-icon"></i>
    </button>
  </div>
</header>

<!-- MOBILE NAV -->
<nav id="mobile-nav">
  <button class="mobile-close" onclick="toggleMobileNav()"><i class="bi bi-x"></i></button>
  <a href="{{ route('home') }}" onclick="toggleMobileNav()">Home</a>
  <a href="{{ route('about') }}" onclick="toggleMobileNav()">About</a>
  <a href="{{ route('products') }}" onclick="toggleMobileNav()">Products</a>
  <a href="{{ route('faq') }}" onclick="toggleMobileNav()">FAQ</a>
  <a href="{{ route('contact') }}" onclick="toggleMobileNav()">Contact</a>
  <div style="margin-top: 32px; display: flex; flex-direction: column; gap: 12px;">
    <a href="{{ route('borrower.login') }}" class="btn btn-outline" style="justify-content:center" onclick="toggleMobileNav()">Sign In</a>
    <a href="{{ route('borrower.register') }}" class="btn btn-gold" style="justify-content:center" onclick="toggleMobileNav()">Apply Now</a>
  </div>
</nav>

<!-- PAGE CONTENT -->
@yield('content')

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="{{ route('home') }}" style="display:inline-block;margin-bottom:16px">
          <img src="{{ asset('storage/video/logo.webp') }}" alt="MyLoan" style="height:44px;width:auto;display:block;object-fit:contain;filter:brightness(0) invert(1)">
        </a>
        <p class="footer-desc">Simple, Fast and Secure financial solutions for the people of Lesotho. Licensed and regulated by the Central Bank of Lesotho.</p>
        <a href="tel:+26658478799" class="footer-contact-item"><i class="bi bi-telephone-fill"></i> (+266) 58 478 799</a>
        <a href="mailto:info@myloan.co.ls" class="footer-contact-item"><i class="bi bi-envelope-fill"></i> info@myloan.co.ls</a>
        <a href="#" class="footer-contact-item"><i class="bi bi-geo-alt-fill"></i> L&amp;M Complex, Ha Thamae, Maseru</a>
      </div>

      <!-- Products -->
      <div class="footer-col">
        <div class="footer-col-title">Products</div>
        <a href="{{ route('products') }}">Government Loan</a>
        <a href="{{ route('products') }}">Private Sector Loan</a>
        <a href="{{ route('products') }}">Pensioner Loan</a>
      </div>

      <!-- Company -->
      <div class="footer-col">
        <div class="footer-col-title">Company</div>
        <a href="{{ route('about') }}">About Us</a>
        <a href="{{ route('faq') }}">FAQ</a>
        <a href="{{ route('contact') }}">Contact Us</a>
        <a href="{{ route('privacy') ?? '#' }}">Privacy Policy</a>
        <a href="{{ route('terms') ?? '#' }}">Terms &amp; Conditions</a>
      </div>

      <!-- Licence -->
      <div class="footer-col">
        <div class="footer-col-title">Licensed &amp; Regulated</div>
        <div class="footer-cbk">
          Myloan Limited is licensed under the Financial Institutions Act 2012 and Financial Institutions (Amendment) Regulations 2014, as amended in 2018, as a Credit Only Micro Finance Institution Tier II and regulated by the Central Bank of Lesotho.
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <div class="footer-copy">© {{ date('Y') }} MyLoan Limited. All rights reserved.</div>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
        <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>

<script>
// Header scroll
const header = document.getElementById('header');
const isTransparentPage = document.body.dataset.transparentHeader === '1';
function updateHeader() {
  if (isTransparentPage) {
    header.className = window.scrollY > 60 ? 'solid' : 'transparent';
  }
}
if (isTransparentPage) { header.className = 'transparent'; window.addEventListener('scroll', updateHeader); }

// Mobile nav
function toggleMobileNav() {
  const nav = document.getElementById('mobile-nav');
  const icon = document.getElementById('nav-icon');
  const open = nav.style.display === 'flex';
  nav.style.display = open ? 'none' : 'flex';
  icon.className = open ? 'bi bi-list' : 'bi bi-x';
  document.body.style.overflow = open ? '' : 'hidden';
}

// Reveal on scroll
const ro = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); ro.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal, .reveal-left').forEach(el => ro.observe(el));
</script>

@stack('scripts')
</body>
</html>
