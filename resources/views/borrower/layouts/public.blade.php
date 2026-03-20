<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="@yield('meta_desc','MyLoan Limited — Simple, Fast and Secure loans in Lesotho.')">
<title>@yield('title','MyLoan') — MyLoan Limited</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ─────────────────────────────────────────────────────────
   DESIGN SYSTEM — Navy Blue Theme
───────────────────────────────────────────────────────── */
:root {
  --navy:     #0d1b3e;
  --navy2:    #162552;
  --navy3:    #1e3370;
  --blue:     #2b4bad;
  --blue2:    #3d60d4;
  --accent:   #c9a84c;
  --accent2:  #e0c06a;
  --ivory:    #faf8f3;
  --ivory2:   #f0ece0;
  --charcoal: #111827;
  --ink:      #1c2433;
  --slate:    #4b5a72;
  --border:   #dde3ef;
  --white:    #ffffff;
  --light:    #7c9ff5;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; font-size: 16px; }
body {
  font-family: 'Outfit', sans-serif;
  font-weight: 400; color: var(--ink);
  background: var(--white);
  line-height: 1.6; -webkit-font-smoothing: antialiased;
}
h1, h2, h3 { font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 600; line-height: 1.15; letter-spacing: -.02em; }
h1 { font-size: clamp(40px, 5.5vw, 72px); }
h2 { font-size: clamp(26px, 3.5vw, 48px); }
h3 { font-size: clamp(18px, 2vw, 24px); font-weight: 500; }
h4 { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; }
p { font-size: 16px; line-height: 1.75; color: var(--slate); }
a { color: inherit; }

.container { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
section { padding: 90px 0; }

/* EYEBROW */
.eyebrow {
  display: inline-flex; align-items: center; gap: 10px;
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .14em; text-transform: uppercase; color: var(--blue2);
  margin-bottom: 18px;
}
.eyebrow::before { content: ''; width: 28px; height: 1px; background: var(--blue2); }

/* BUTTONS */
.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 13px 28px; border-radius: 4px;
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600;
  letter-spacing: .03em; text-decoration: none; cursor: pointer;
  border: none; transition: all .25s ease; white-space: nowrap;
}
.btn-navy   { background: var(--navy); color: var(--white); }
.btn-navy:hover { background: var(--navy2); transform: translateY(-1px); box-shadow: 0 6px 24px rgba(13,27,62,.35); }
.btn-accent { background: var(--accent); color: var(--white); }
.btn-accent:hover { background: #a88030; transform: translateY(-1px); }
.btn-outline-white { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.35); }
.btn-outline-white:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.6); }
.btn-lg { padding: 16px 36px; font-size: 15px; }
.btn-sm { padding: 8px 18px; font-size: 13px; }

/* DIVIDER */
.divider { width: 44px; height: 2px; background: var(--accent); margin: 18px 0 26px; }
.divider-center { margin: 18px auto 26px; }

/* ANIMATIONS */
.reveal { opacity: 0; transform: translateY(22px); transition: opacity .6s ease, transform .6s ease; }
.reveal.in { opacity: 1; transform: translateY(0); }
.d1{transition-delay:.08s}.d2{transition-delay:.16s}.d3{transition-delay:.24s}.d4{transition-delay:.32s}

/* ─────────────────────────────────────────────────────────
   HEADER — Minimal conversion nav
───────────────────────────────────────────────────────── */
#header {
  position: fixed; top: 0; left: 0; right: 0; z-index: 900;
  height: 64px; transition: all .35s ease;
}
#header.transparent {
  background: transparent;
  border-bottom: 1px solid transparent;
}
#header.solid {
  background: rgba(10,20,48,.96);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.header-inner {
  height: 64px; display: flex; align-items: center;
  max-width: 1200px; margin: 0 auto; padding: 0 32px;
}
.header-logo {
  display: flex; align-items: center; text-decoration: none; margin-right: 32px;
}
.header-logo img { height: 36px; width: auto; display: block; object-fit: contain; }
.header-home {
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 400;
  color: rgba(255,255,255,.6); text-decoration: none;
  padding: 6px 12px; border-radius: 4px; transition: all .2s;
}
.header-home:hover { color: #fff; background: rgba(255,255,255,.07); }
.header-sp { flex: 1; }
.header-portal {
  background: var(--accent); color: var(--white);
  padding: 9px 22px; border-radius: 4px;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
  letter-spacing: .04em; text-decoration: none; transition: all .25s;
  display: flex; align-items: center; gap: 7px;
}
.header-portal:hover { background: #a88030; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(201,168,76,.4); }
.nav-mobile-btn { display: none; background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; padding: 6px; margin-left: 12px; }

/* Mobile nav */
#mobile-nav {
  display: none; position: fixed; inset: 0; z-index: 899;
  background: rgba(10,20,48,.98); padding: 90px 32px 40px;
  flex-direction: column; gap: 4px;
}
.mobile-nav-home { font-family: 'Cormorant Garamond', serif; font-size: 36px; font-weight: 600; color: rgba(255,255,255,.8); text-decoration: none; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
.mobile-close { position: absolute; top: 20px; right: 22px; background: none; border: none; color: rgba(255,255,255,.5); font-size: 26px; cursor: pointer; }

/* ─────────────────────────────────────────────────────────
   FOOTER — 3-column minimal
───────────────────────────────────────────────────────── */
footer {
  background: var(--charcoal); color: rgba(255,255,255,.5);
  padding: 24px 0; border-top: 1px solid rgba(255,255,255,.06);
}
.footer-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: 1fr auto 1fr;
  align-items: center; gap: 24px;
}
.footer-left { display: flex; flex-direction: column; gap: 4px; }
.footer-left p { font-size: 13px; color: rgba(255,255,255,.4); line-height: 1.5; }
.footer-left a { color: rgba(255,255,255,.5); text-decoration: none; font-size: 13px; transition: color .2s; }
.footer-left a:hover { color: rgba(255,255,255,.8); }
.footer-center { text-align: center; }
.footer-copy { font-size: 12px; color: rgba(255,255,255,.3); margin-bottom: 8px; }
.footer-fb {
  display: inline-flex; align-items: center; gap: 6px;
  color: rgba(255,255,255,.45); text-decoration: none; font-size: 13px;
  transition: color .2s;
}
.footer-fb:hover { color: #4267B2; }
.footer-right { display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
.pay-badge {
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
  border-radius: 4px; padding: 5px 10px; font-size: 11px; font-weight: 600;
  color: rgba(255,255,255,.45); letter-spacing: .04em; white-space: nowrap;
  display: flex; align-items: center; gap: 5px;
}
.pay-badge i { font-size: 12px; }

@media (max-width: 900px) {
  .footer-inner { grid-template-columns: 1fr; gap: 16px; text-align: center; }
  .footer-right { justify-content: center; }
  .footer-left { align-items: center; }
}
@media (max-width: 768px) {
  .container { padding: 0 20px; }
  section { padding: 64px 0; }
  .header-inner { padding: 0 20px; }
  .header-home { display: none; }
  .nav-mobile-btn { display: block; }
}

@stack('page-styles')
</style>
</head>
<body>

<!-- HEADER -->
<header id="header" class="@yield('header-class','solid')">
  <div class="header-inner">
    <a href="{{ route('home') }}" class="header-logo">
      <img src="https://ik.imagekit.io/ygydr1m84/png.webp" alt="MyLoan">
    </a>
    <a href="{{ route('home') }}" class="header-home">Home</a>
    <div class="header-sp"></div>
    <a href="{{ route('borrower.login') }}" class="header-portal">
      <i class="bi bi-person-fill"></i> Go to Portal
    </a>
    <button class="nav-mobile-btn" onclick="toggleMobileNav()" aria-label="Menu">
      <i class="bi bi-list" id="nav-icon"></i>
    </button>
  </div>
</header>

<!-- MOBILE NAV -->
<nav id="mobile-nav">
  <button class="mobile-close" onclick="toggleMobileNav()"><i class="bi bi-x"></i></button>
  <a href="{{ route('home') }}" class="mobile-nav-home" onclick="toggleMobileNav()">Home</a>
  <div style="margin-top:32px;display:flex;flex-direction:column;gap:12px">
    <a href="{{ route('borrower.login') }}" class="btn btn-accent" style="justify-content:center" onclick="toggleMobileNav()"><i class="bi bi-person-fill"></i> Sign In to Portal</a>
    <a href="{{ route('borrower.register') }}" class="btn btn-outline-white" style="justify-content:center" onclick="toggleMobileNav()">Apply Now</a>
  </div>
</nav>

<!-- PAGE CONTENT -->
@yield('content')

<!-- FOOTER — 3-column compact -->
<footer>
  <div class="footer-inner">
    <!-- Left: address + contact -->
    <div class="footer-left">
      <a href="tel:+26658478799"><i class="bi bi-telephone-fill" style="color:var(--light);margin-right:5px"></i> (+266) 58 478 799</a>
      <a href="mailto:info@myloan.co.ls"><i class="bi bi-envelope-fill" style="color:var(--light);margin-right:5px"></i> info@myloan.co.ls</a>
      <p><i class="bi bi-geo-alt-fill" style="color:var(--light);margin-right:5px"></i> L&amp;M Complex, Ha Thamae, Maseru</p>
    </div>

    <!-- Center: copyright + Facebook -->
    <div class="footer-center">
      <div class="footer-copy">MyLoan © {{ date('Y') }} · All rights reserved</div>
      <a href="#" class="footer-fb">
        <i class="bi bi-facebook" style="font-size:16px;color:#4267B2"></i> Follow us on Facebook
      </a>
    </div>

    <!-- Right: payment methods -->
    <div class="footer-right">
      <div class="pay-badge"><i class="bi bi-credit-card-fill"></i> Visa / Mastercard</div>
      <div class="pay-badge"><i class="bi bi-phone-fill"></i> M-Pesa</div>
      <div class="pay-badge"><i class="bi bi-phone-fill"></i> CPay</div>
      <div class="pay-badge"><i class="bi bi-bank"></i> Stop Order</div>
      <div class="pay-badge"><i class="bi bi-bank2"></i> Debit Order</div>
    </div>
  </div>
</footer>

<script>
// Header transparent handling
const header = document.getElementById('header');
const isTransparent = document.body.dataset.transparentHeader === '1';
function updateHeader() {
  if (isTransparent) header.className = window.scrollY > 40 ? 'solid' : 'transparent';
}
if (isTransparent) { header.className = 'transparent'; window.addEventListener('scroll', updateHeader); }

// Mobile nav
function toggleMobileNav() {
  const nav = document.getElementById('mobile-nav');
  const icon = document.getElementById('nav-icon');
  const open = nav.style.display === 'flex';
  nav.style.display = open ? 'none' : 'flex';
  icon.className = open ? 'bi bi-list' : 'bi bi-x';
  document.body.style.overflow = open ? '' : 'hidden';
}

// Reveal
const ro = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); ro.unobserve(e.target); } });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
</script>

@stack('scripts')
</body>
</html>
