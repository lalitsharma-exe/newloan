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
:root {
  --navy:    #0d1b3e;
  --navy2:   #162552;
  --navy3:   #1e3370;
  --blue:    #2b4bad;
  --blue2:   #3d60d4;
  --accent:  #8cc63f;
  --accent2: #7ab033;
  --ivory:   #faf8f3;
  --ivory2:  #f0ece0;
  --charcoal:#111827;
  --ink:     #1c2433;
  --slate:   #4b5a72;
  --border:  #dde3ef;
  --white:   #ffffff;
  --light:   #7c9ff5;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; font-size: 16px; }
body {
  font-family: 'Outfit', sans-serif;
  font-weight: 400; color: var(--ink);
  background: var(--white);
  line-height: 1.6; -webkit-font-smoothing: antialiased;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
main { flex: 1; display: flex; flex-direction: column;  background: var(--charcoal);}
h1, h2, h3 { font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 600; line-height: 1.15; letter-spacing: -.02em; }
p { font-size: 16px; line-height: 1.75; color: var(--slate); }
a { color: inherit; }

.container { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
section { padding: 90px 0; }

.eyebrow {
  display: inline-flex; align-items: center; gap: 10px;
  font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .14em; text-transform: uppercase; color: var(--blue2); margin-bottom: 18px;
}
.eyebrow::before { content: ''; width: 28px; height: 1px; background: var(--blue2); }

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
.btn-accent:hover { background: var(--accent2); transform: translateY(-1px); }
.btn-outline-white { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.35); }
.btn-outline-white:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.6); }
.btn-lg { padding: 16px 36px; font-size: 15px; }
.btn-sm { padding: 8px 18px; font-size: 13px; }

.divider { width: 44px; height: 2px; background: var(--accent); margin: 18px 0 26px; }
.divider-center { margin: 18px auto 26px; }

.reveal { opacity: 0; transform: translateY(22px); transition: opacity .6s ease, transform .6s ease; }
.reveal.in { opacity: 1; transform: translateY(0); }
.d1{transition-delay:.08s}.d2{transition-delay:.16s}.d3{transition-delay:.24s}.d4{transition-delay:.32s}

/* ─────────────────────────────────────────────────────────
   HEADER — Pill style (like screenshot reference)
───────────────────────────────────────────────────────── */
#header {
  position: fixed; top: 0; left: 0; right: 0; z-index: 900;
  height: 72px; display: flex; align-items: center;
  padding: 0 24px;
  transition: all .35s ease;
}
#header.transparent { background: transparent; }
#header.solid { background: rgba(7,14,36,.92); backdrop-filter: blur(20px); }

/* The pill container — like screenshot */
.header-pill {
  width: 100%; max-width: 1160px; margin: 0 auto;
  display: flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 99px;
  padding: 8px 8px 8px 20px;
  backdrop-filter: blur(16px);
  transition: all .35s;
}
#header.transparent .header-pill {
  background: rgba(5,11,26,.65);
  border-color: rgba(255,255,255,.12);
}
#header.solid .header-pill {
  background: rgba(255,255,255,.05);
  border-color: rgba(255,255,255,.08);
}

.header-logo { display: flex; align-items: center; text-decoration: none; margin-right: 16px; flex-shrink: 0; }
.header-logo img { height: 32px; width: auto; display: block; object-fit: contain; }

.header-home {
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 400;
  color: rgba(255,255,255,.65); text-decoration: none;
  padding: 6px 14px; border-radius: 99px; transition: all .2s;
}
.header-home:hover { color: #fff; background: rgba(255,255,255,.08); }
.header-home.active { color: #fff; }

.header-sp { flex: 1; }

/* Sign In — outlined pill */
.header-signin {
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 500;
  color: rgba(255,255,255,.7); text-decoration: none;
  padding: 8px 18px; border-radius: 99px;
  border: 1px solid rgba(255,255,255,.2);
  transition: all .2s; display: flex; align-items: center; gap: 6px;
  margin-right: 6px;
}
.header-signin:hover { color: #fff; border-color: rgba(255,255,255,.45); background: rgba(255,255,255,.06); }

/* Apply Now — gold pill */
.header-apply {
  background: var(--accent); color: var(--white);
  padding: 9px 22px; border-radius: 99px;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 700;
  letter-spacing: .04em; text-decoration: none; transition: all .25s;
  display: flex; align-items: center; gap: 6px;
}
.header-apply:hover { background: var(--accent2); transform: scale(1.02); box-shadow: 0 4px 14px rgba(140,198,63,.4); }

.nav-mobile-btn { display: none; background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; padding: 6px; margin-left: 6px; }

/* Mobile nav */
#mobile-nav {
  display: none; position: fixed; inset: 0; z-index: 899;
  background: rgba(7,14,36,.98); padding: 90px 32px 40px;
  flex-direction: column;
}
#mobile-nav a {
  font-family: 'Cormorant Garamond', serif; font-size: 36px; font-weight: 600;
  color: rgba(255,255,255,.8); text-decoration: none;
  padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.06); transition: color .2s;
}
#mobile-nav a:hover { color: #fff; }
.mobile-close { position: absolute; top: 20px; right: 22px; background: none; border: none; color: rgba(255,255,255,.5); font-size: 26px; cursor: pointer; }

/* ─────────────────────────────────────────────────────────
   FOOTER — 3-column with real payment logos
───────────────────────────────────────────────────────── */
footer {
  background: var(--charcoal); color: rgba(255,255,255,.5);
  padding: 28px 0; border-top: 1px solid rgba(255,255,255,.06);
}
.footer-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 32px;
  display: grid; grid-template-columns: 1fr auto 1fr;
  align-items: center; gap: 24px;
}
.footer-left { display: flex; flex-direction: column; gap: 5px; }
.footer-left a { color: rgba(255,255,255,.5); text-decoration: none; font-size: 13px; transition: color .2s; display: flex; align-items: center; gap: 7px; }
.footer-left a:hover { color: rgba(255,255,255,.85); }
.footer-left a i { color: var(--light); font-size: 13px; width: 14px; }
.footer-left p { font-size: 13px; color: rgba(255,255,255,.35); display: flex; align-items: center; gap: 7px; }
.footer-left p i { color: var(--light); font-size: 13px; width: 14px; }

.footer-center { text-align: center; }
.footer-logo { height: 30px; width: auto; object-fit: contain; display: block; margin: 0 auto 8px; filter: brightness(0) invert(1) opacity(.5); }
.footer-copy { font-size: 12px; color: rgba(255,255,255,.25); margin-bottom: 8px; }
.footer-fb { display: inline-flex; align-items: center; gap: 6px; color: rgba(255,255,255,.4); text-decoration: none; font-size: 12px; transition: color .2s; }
.footer-fb:hover { color: #4267B2; }

/* Payment logos row */
.footer-right { display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
.pay-logo {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
  border-radius: 6px; padding: 6px 12px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.pay-logo:hover { background: rgba(255,255,255,.12); }
.pay-logo img { height: 16px; width: auto; object-fit: contain; filter: brightness(0) invert(1) opacity(.6); }
.pay-logo span {
  font-size: 11px; font-weight: 700; color: rgba(255,255,255,.55);
  letter-spacing: .04em; font-family: 'Outfit', sans-serif;
}

@media (max-width: 1024px) { .footer-inner { grid-template-columns: 1fr 1fr; gap: 20px; } .footer-center { order: 2; grid-column: span 2; } }
@media (max-width: 768px) {
  .container, .section { padding: 0 20px; }
  section { padding: 64px 0; }
  #header { padding: 0 16px; }
  .header-home { display: none; }
  .nav-mobile-btn { display: block; }
  .header-pill { padding: 6px 6px 6px 14px; }
  .footer-inner { grid-template-columns: 1fr; gap: 18px; text-align: center; }
  .footer-left { align-items: center; }
  .footer-right { justify-content: center; }
  .footer-center { order: unset; }
}

@stack('page-styles')
</style>
</head>
<body>

<!-- HEADER — Pill style -->
<header id="header" class="@yield('header-class','solid')">
  <div class="header-pill">
    <a href="{{ route('home') }}" class="header-logo">
      <img src="{{ config('app.logo') }}" alt="MyLoan">
    </a>

    <a href="{{ route('home') }}" class="header-home {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>

    <div class="header-sp"></div>

    <!-- <a href="https://www.facebook.com/share/18TBN7SB8y/?mibextid=wwXIfr" target="_blank" class="header-signin" style="padding: 8px 12px; border: none; margin-right: 2px;" title="Follow us on Facebook">
      <i class="bi bi-facebook" style="color:#4267B2; font-size: 16px; margin: 0;"></i>
    </a>
    <a href="{{ route('admin.login') }}" class="header-signin" style="padding: 8px 12px; border: none; margin-right: 2px;" title="Admin Portal">
      <i class="bi bi-shield-lock-fill" style="font-size: 15px; margin: 0; color: rgba(255,255,255,.5);"></i>
    </a> -->
    <a href="{{ route('borrower.login') }}" class="header-signin">
      <i class="bi bi-person-fill"></i> Sign In
    </a>
    <a href="{{ route('borrower.register') }}" class="header-apply">
      Apply Now
    </a>

    <button class="nav-mobile-btn" onclick="toggleMobileNav()" aria-label="Menu">
      <i class="bi bi-list" id="nav-icon"></i>
    </button>
  </div>
</header>

<!-- MOBILE NAV -->
<nav id="mobile-nav">
  <button class="mobile-close" onclick="toggleMobileNav()"><i class="bi bi-x"></i></button>
  <a href="{{ route('home') }}" onclick="toggleMobileNav()">Home</a>
  <div style="margin-top:32px;display:flex;flex-direction:column;gap:12px">
    <a href="{{ route('borrower.login') }}" class="btn btn-outline-white" style="justify-content:center;border-radius:99px" onclick="toggleMobileNav()"><i class="bi bi-person-fill"></i> Sign In</a>
    <a href="{{ route('borrower.register') }}" class="btn btn-accent" style="justify-content:center;border-radius:99px" onclick="toggleMobileNav()">Apply Now</a>
  </div>
</nav>

<!-- PAGE CONTENT -->
<main>
@yield('content')
</main>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <!-- Left: contact -->
    <div class="footer-left">
      <a href="tel:+26658478799"><i class="bi bi-telephone-fill"></i>(+266) 58 478 799</a>
      <a href="mailto:info@myloan.co.ls"><i class="bi bi-envelope-fill"></i>info@myloan.co.ls</a>
      <p><i class="bi bi-geo-alt-fill"></i>L&amp;M Complex, Ha Thamae, Maseru</p>
    </div>

    <!-- Center: logo + copyright + Facebook -->
    <div class="footer-center">
      <img src="{{ config('app.logo') }}" alt="MyLoan" class="footer-logo">
      <div class="footer-copy">MyLoan © {{ date('Y') }} · All rights reserved</div>
      <a href="https://www.facebook.com/share/18TBN7SB8y/?mibextid=wwXIfr" target="_blank" class="footer-fb">
        <i class="bi bi-facebook" style="color:#4267B2;font-size:15px"></i> Facebook
      </a>
    </div>

    <!-- Right: payment method logos -->
    <div class="footer-right">
      <!-- Visa -->
      <div class="pay-logo" title="Visa">
        <svg height="16" viewBox="0 0 48 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M20.5 1L16.5 15H12.5L16.5 1H20.5Z" fill="white" opacity=".6"/>
          <path d="M34 1.3C33.1 1 31.8.7 30.2.7 26.4.7 23.7 2.6 23.7 5.3c0 2 1.9 3.1 3.4 3.8 1.5.7 2 1.2 2 1.8 0 1-1.2 1.4-2.3 1.4-1.5 0-2.3-.2-3.5-.7l-.5-.2-.5 3c.8.4 2.4.7 4 .7 4 0 6.6-1.9 6.6-4.8 0-1.6-1-2.8-3.2-3.8-1.3-.7-2.1-1.1-2.1-1.8 0-.6.7-1.2 2.2-1.2 1.2 0 2.1.3 2.8.5l.3.1.5-2.9Z" fill="white" opacity=".6"/>
          <path d="M39.5 1h-3c-.9 0-1.6.3-2 1.1L28.5 15h4l.8-2.1h4.9l.5 2.1H42L39.5 1Zm-4.7 9.2 1.5-4.1.5-1.4.3 1.4 1 4.1H34.8Z" fill="white" opacity=".6"/>
          <path d="M9.5 1 5.8 10.5 5.4 8.7C4.7 6.5 2.7 4 .5 2.9L3.9 15h4L13.5 1H9.5Z" fill="white" opacity=".6"/>
          <path d="M3 1H.1L0 1.3C2.3 1.9 4.3 3.4 5.4 5.1l-1-3.8C4.2 1.2 3.6 1 3 1Z" fill="white" opacity=".5"/>
        </svg>
      </div>
      <!-- Mastercard -->
      <div class="pay-logo" title="Mastercard">
        <svg height="18" viewBox="0 0 38 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="14" cy="12" r="10" fill="#EB001B" opacity=".7"/>
          <circle cx="24" cy="12" r="10" fill="#F79E1B" opacity=".7"/>
          <path d="M19 5.5a10 10 0 0 1 0 13A10 10 0 0 1 19 5.5Z" fill="#FF5F00" opacity=".7"/>
        </svg>
      </div>
      <!-- CDAS -->
      <div class="pay-logo" title="CDAS">
        <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-03-22%20at%205.48.11%20PM%20(1).jpeg" alt="CDAS" style="filter:none;opacity:1">
      </div>
      <!-- M-Pesa -->
      <div class="pay-logo" title="M-Pesa">
        <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp%20Image%202026-03-22%20at%205.48.11%20PM.jpeg" alt="M-Pesa" style="filter:none;opacity:1">
      </div>
      <!-- CPay -->
      <div class="pay-logo" title="CPay">
        <span style="font-size:11px;font-weight:bold;color:#1e3370">CPay</span>
      </div>
      <!-- EcoCash -->
      <div class="pay-logo" title="EcoCash">
        <span style="font-size:11px;font-weight:bold;color:#0071bc">EcoCash</span>
      </div>
    </div>
  </div>
</footer>

<script>
const header = document.getElementById('header');
const isTransparent = document.body.dataset.transparentHeader === '1';
function updateHeader() {
  if (isTransparent) header.className = window.scrollY > 40 ? 'solid' : 'transparent';
}
if (isTransparent) { header.className = 'transparent'; window.addEventListener('scroll', updateHeader); }

function toggleMobileNav() {
  const nav = document.getElementById('mobile-nav');
  const icon = document.getElementById('nav-icon');
  const open = nav.style.display === 'flex';
  nav.style.display = open ? 'none' : 'flex';
  icon.className = open ? 'bi bi-list' : 'bi bi-x';
  document.body.style.overflow = open ? '' : 'hidden';
}

const ro = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); ro.unobserve(e.target); } });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
</script>

<!-- FLOATING WHATSAPP BUTTON -->
<a href="https://wa.me/26663445244" target="_blank" rel="noopener" class="whatsapp-float" title="Chat with us on WhatsApp">
  <svg viewBox="0 0 32 32" width="28" height="28" fill="#fff">
    <path d="M16.004 2.672c-7.36 0-13.332 5.972-13.332 13.332 0 2.348.616 4.644 1.788 6.664L2.672 29.328l6.82-1.788a13.28 13.28 0 006.512 1.704c7.36 0 13.332-5.972 13.332-13.332S23.364 2.672 16.004 2.672zm0 24.396a10.98 10.98 0 01-5.608-1.536l-.4-.24-4.152 1.088 1.108-4.056-.264-.416a10.95 10.95 0 01-1.684-5.836c0-6.076 4.944-11.02 11.02-11.02 6.076 0 11.02 4.944 11.02 11.02-.02 6.076-4.964 11.02-11.04 11.02v-.024zm6.04-8.252c-.332-.168-1.96-.968-2.264-1.08-.304-.112-.524-.168-.744.168-.22.332-.86 1.08-1.052 1.3-.192.22-.388.248-.72.084-.332-.168-1.404-.516-2.672-1.648-.988-.88-1.656-1.964-1.848-2.296-.196-.332-.02-.512.148-.676.148-.148.332-.388.496-.58.168-.196.22-.332.332-.556.112-.22.056-.416-.028-.58-.084-.168-.744-1.792-1.02-2.456-.268-.644-.54-.556-.744-.568-.192-.008-.416-.008-.636-.008a1.22 1.22 0 00-.884.416c-.304.332-1.16 1.132-1.16 2.76s1.188 3.2 1.356 3.42c.168.22 2.34 3.576 5.672 5.016.792.344 1.412.548 1.896.7.796.252 1.52.216 2.092.132.64-.096 1.96-.8 2.236-1.576.276-.776.276-1.44.192-1.576-.084-.14-.304-.22-.636-.388z"/>
  </svg>
</a>
<style>
.whatsapp-float {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 56px;
  height: 56px;
  background: #25D366;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(37,211,102,.4), 0 2px 6px rgba(0,0,0,.15);
  z-index: 9999;
  text-decoration: none;
  transition: transform .25s ease, box-shadow .25s ease;
  animation: wa-pulse 2s infinite;
}
.whatsapp-float:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 24px rgba(37,211,102,.5), 0 4px 12px rgba(0,0,0,.2);
  animation: none;
}
@keyframes wa-pulse {
  0%   { box-shadow: 0 4px 16px rgba(37,211,102,.4), 0 0 0 0 rgba(37,211,102,.4); }
  70%  { box-shadow: 0 4px 16px rgba(37,211,102,.4), 0 0 0 14px rgba(37,211,102,0); }
  100% { box-shadow: 0 4px 16px rgba(37,211,102,.4), 0 0 0 0 rgba(37,211,102,0); }
}
@media (max-width: 768px) {
  .whatsapp-float { bottom: 20px; right: 16px; width: 50px; height: 50px; }
  .whatsapp-float svg { width: 24px; height: 24px; }
}
</style>

@stack('scripts')
</body>
</html>
