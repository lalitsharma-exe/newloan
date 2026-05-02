<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="description" content="@yield('meta_desc', 'Myloan Limited — Simple, Fast and Secure loans in Lesotho.')">
  <title>{{ config('app.name') }}</title>
  <link rel="shortcut icon" href="https://ik.imagekit.io/ygydr1m84/2699f0f4-26da-41ec-92ff-ce4aa8ac0f79.jpeg" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --navy: #0d1b3e;
      --navy2: #162552;
      --navy3: #1e3370;
      --blue: #2b4bad;
      --blue2: #3d60d4;
      --accent: #8cc63f;
      --accent2: #7ab033;
      --ivory: #faf8f3;
      --ivory2: #f0ece0;
      --charcoal: #111827;
      --ink: #1c2433;
      --slate: #4b5a72;
      --border: #dde3ef;
      --white: #ffffff;
      --light: #7c9ff5;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
      font-size: 16px;
      overflow-x: hidden;
    }

    body {
      font-family: 'Outfit', sans-serif;
      font-weight: 400;
      color: var(--ink);
      background: var(--white);
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
    }

    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: var(--white);
    }

    h1,
    h2,
    h3 {
      font-family: 'Cormorant Garamond', Georgia, serif;
      font-weight: 600;
      line-height: 1.15;
      letter-spacing: -.02em;
    }

    p {
      font-size: 16px;
      line-height: 1.75;
      color: var(--slate);
    }

    a {
      color: inherit;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 32px;
    }

    section {
      padding: 90px 0;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-family: 'Outfit', sans-serif;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .14em;
      text-transform: uppercase;
      color: var(--blue2);
      margin-bottom: 18px;
    }

    .eyebrow::before {
      content: '';
      width: 28px;
      height: 1px;
      background: var(--blue2);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 13px 28px;
      border-radius: 4px;
      font-family: 'Outfit', sans-serif;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .03em;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: all .25s ease;
      white-space: nowrap;
    }

    .btn-navy {
      background: var(--navy);
      color: var(--white);
    }

    .btn-navy:hover {
      background: var(--navy2);
      transform: translateY(-1px);
      box-shadow: 0 6px 24px rgba(13, 27, 62, .35);
    }

    .btn-accent {
      background: var(--accent);
      color: var(--white);
    }

    .btn-accent:hover {
      background: var(--accent2);
      transform: translateY(-1px);
    }

    .btn-outline-white {
      background: transparent;
      color: #fff;
      border: 1.5px solid rgba(255, 255, 255, .35);
    }

    .btn-outline-white:hover {
      background: rgba(255, 255, 255, .1);
      border-color: rgba(255, 255, 255, .6);
    }

    .btn-lg {
      padding: 16px 36px;
      font-size: 15px;
    }

    .btn-sm {
      padding: 8px 18px;
      font-size: 13px;
    }

    .divider {
      width: 44px;
      height: 2px;
      background: var(--accent);
      margin: 18px 0 26px;
    }

    .divider-center {
      margin: 18px auto 26px;
    }

    .reveal {
      opacity: 0;
      transform: translateY(22px);
      transition: opacity .6s ease, transform .6s ease;
    }

    .reveal.in {
      opacity: 1;
      transform: translateY(0);
    }

    .d1 {
      transition-delay: .08s
    }

    .d2 {
      transition-delay: .16s
    }

    .d3 {
      transition-delay: .24s
    }

    .d4 {
      transition-delay: .32s
    }

    /* ═══════════════════════════════════════════════════════
   HEADER — fixed pill nav
═══════════════════════════════════════════════════════ */
    #header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 900;
      padding: 10px 16px;
      transition: padding .35s ease;
    }

    #header.scrolled {
      padding: 6px 16px;
    }

    .header-pill {
      width: 100%;
      max-width: 1160px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, .2);
      border: 1px solid rgba(255, 255, 255, .4);
      border-radius: 99px;
      padding: 6px 6px 6px 14px;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      transition: all .35s ease;
      gap: 4px;
    }

    #header.scrolled .header-pill {
      background: rgba(255, 255, 255, .75);
      border-color: rgba(255, 255, 255, .6);
      box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
    }

    .header-logo {
      display: flex;
      align-items: center;
      text-decoration: none;
      margin-right: 12px;
      flex-shrink: 0;
    }

    .header-logo img {
      height: 28px;
      width: auto;
      display: block;
      object-fit: contain;
    }

    .header-nav-links {
      display: flex;
      align-items: center;
      gap: 2px;
    }

    .header-home {
      font-family: 'Outfit', sans-serif;
      font-size: 13.5px;
      font-weight: 500;
      color: rgba(13, 27, 62, .7);
      text-decoration: none;
      padding: 6px 13px;
      border-radius: 99px;
      transition: all .2s;
      white-space: nowrap;
    }

    .header-home:hover {
      color: var(--navy);
      background: rgba(13, 27, 62, .05);
    }

    .header-home.active {
      color: var(--navy);
      font-weight: 700;
    }

    .header-sp {
      flex: 1;
      min-width: 8px;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .header-signin {
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 500;
      color: rgba(13, 27, 62, .8);
      text-decoration: none;
      padding: 7px 16px;
      border-radius: 99px;
      border: 1px solid rgba(13, 27, 62, .2);
      transition: all .2s;
      display: flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
    }

    .header-signin:hover {
      color: var(--navy);
      border-color: var(--navy);
      background: rgba(13, 27, 62, .04);
    }

    .header-signin i {
      font-size: 13px;
    }

    .header-apply {
      background: var(--accent);
      color: var(--white);
      padding: 8px 20px;
      border-radius: 99px;
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .03em;
      text-decoration: none;
      transition: all .25s;
      display: flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .header-apply:hover {
      background: var(--accent2);
      box-shadow: 0 4px 14px rgba(140, 198, 63, .4);
    }

    .header-apply i {
      font-size: 12px;
    }

    /* Hamburger — hidden on desktop */
    .nav-mobile-btn {
      display: none;
      background: rgba(13, 27, 62, .06);
      border: 1px solid rgba(13, 27, 62, .1);
      color: var(--navy);
      font-size: 18px;
      cursor: pointer;
      padding: 0;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background .2s;
    }

    .nav-mobile-btn:hover {
      background: rgba(13, 27, 62, .12);
    }

    /* ── Mobile nav overlay ─────────────────────────────── */
    #mobile-nav {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 899;
      background: rgba(5, 11, 26, .97);
      padding: 80px 28px 40px;
      flex-direction: column;
      overflow-y: auto;
    }

    #mobile-nav.open {
      display: flex;
    }

    #mobile-nav a {
      font-family: 'Cormorant Garamond', serif;
      font-size: 32px;
      font-weight: 600;
      color: rgba(255, 255, 255, .75);
      text-decoration: none;
      padding: 13px 0;
      border-bottom: 1px solid rgba(255, 255, 255, .07);
      transition: color .2s;
    }

    #mobile-nav a:hover {
      color: #fff;
    }

    .mobile-close {
      position: absolute;
      top: 16px;
      right: 18px;
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .15);
      color: rgba(255, 255, 255, .6);
      font-size: 20px;
      cursor: pointer;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .2s;
    }

    .mobile-close:hover {
      background: rgba(255, 255, 255, .15);
      color: #fff;
    }

    .mobile-cta {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 28px;
    }

    /* ═══════════════════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════════════════ */
    footer {
      background: var(--charcoal);
      color: rgba(255, 255, 255, .5);
      padding: 24px 0;
      border-top: 1px solid rgba(255, 255, 255, .06);
    }

    .footer-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 28px;
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      gap: 20px;
    }

    .footer-left {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .footer-left a {
      color: rgba(255, 255, 255, .5);
      text-decoration: none;
      font-size: 13px;
      transition: color .2s;
      display: flex;
      align-items: center;
      gap: 7px;
    }

    .footer-left a:hover {
      color: rgba(255, 255, 255, .85);
    }

    .footer-left a i,
    .footer-left p i {
      color: var(--light);
      font-size: 13px;
      width: 15px;
      text-align: center;
      flex-shrink: 0;
    }

    .footer-left p {
      font-size: 13px;
      color: rgba(255, 255, 255, .35);
      display: flex;
      align-items: center;
      gap: 7px;
      line-height: 1.5;
    }

    .footer-center {
      text-align: center;
    }

    .footer-logo {
      height: 28px;
      width: auto;
      object-fit: contain;
      display: block;
      margin: 0 auto 7px;
      filter: brightness(0) invert(1) opacity(.45);
    }

    .footer-copy {
      font-size: 12px;
      color: rgba(255, 255, 255, .25);
      margin-bottom: 7px;
    }

    .footer-fb {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: rgba(255, 255, 255, .4);
      text-decoration: none;
      font-size: 12px;
      transition: color .2s;
    }

    .footer-fb:hover {
      color: #4267B2;
    }

    /* Payment logos */
    .footer-right {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 8px;
      flex-wrap: wrap;
    }

    .pay-logo {
      background: rgba(255, 255, 255, .07);
      border: 1px solid rgba(255, 255, 255, .1);
      border-radius: 6px;
      padding: 5px 10px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background .2s;
    }

    .pay-logo:hover {
      background: rgba(255, 255, 255, .12);
    }

    .pay-logo img {
      height: 15px;
      width: auto;
      object-fit: contain;
      filter: brightness(0) invert(1) opacity(.55);
    }

    .pay-logo-img-real img {
      filter: none;
      opacity: 1;
    }

    .pay-logo span {
      font-size: 10.5px;
      font-weight: 700;
      color: rgba(255, 255, 255, .55);
      letter-spacing: .03em;
      font-family: 'Outfit', sans-serif;
    }

    /* ── RESPONSIVE BREAKPOINTS ─────────────────────────── */
    @media(max-width:1024px) {
      .footer-inner {
        grid-template-columns: 1fr 1fr;
      }

      .footer-center {
        order: 3;
        grid-column: span 2;
        border-top: 1px solid rgba(255, 255, 255, .06);
        padding-top: 16px;
        margin-top: 4px;
      }
    }

    @media(max-width:768px) {
      .container {
        padding: 0 18px;
      }

      section {
        padding: 60px 0;
      }

      /* Header responsive */
      #header {
        padding: 8px 12px;
      }

      .header-pill {
        padding: 6px 6px 6px 14px;
      }

      .header-logo img {
        height: 24px;
      }

      .header-nav-links {
        display: none;
      }

      .header-signin {
        display: none;
      }

      .header-apply {
        padding: 7px 14px;
        font-size: 12px;
      }

      .header-apply .apply-text {
        display: none;
      }

      .nav-mobile-btn {
        display: flex;
      }

      /* Footer responsive */
      footer {
        padding: 20px 0;
      }

      .footer-inner {
        grid-template-columns: 1fr;
        gap: 16px;
        text-align: center;
      }

      .footer-left {
        align-items: center;
      }

      .footer-center {
        order: unset;
        border: none;
        padding: 0;
        margin: 0;
      }

      .footer-right {
        justify-content: center;
        width: 100%;
        max-width: 320px;
        margin: 0 auto;
      }
    }

    @media(max-width:480px) {
      #header {
        padding: 6px 10px;
      }

      .header-pill {
        padding: 5px 5px 5px 12px;
        gap: 3px;
      }

      .header-logo img {
        height: 22px;
      }

      .header-apply {
        padding: 6px 12px;
        font-size: 11.5px;
      }

      .pay-logo {
        height: 28px;
        padding: 4px 8px;
      }

      .pay-logo img {
        height: 13px;
      }

      .pay-logo span {
        font-size: 9.5px;
      }
    }

    @stack('page-styles')
  </style>
</head>

<body>

  <!-- ═══ HEADER ═══════════════════════════════════════════ -->
  <header id="header" class="@yield('header-class', 'solid')">
    <div class="header-pill">

      <a href="{{ route('home') }}" class="header-logo" aria-label="MyLoan Home">
        <img src="{{ config('app.logo') }}" alt="MyLoan">
      </a>

      <nav class="header-nav-links">
        <a href="{{ route('home') }}" class="header-home {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
      </nav>

      <div class="header-sp"></div>

      <div class="header-actions">
        <a href="{{ route('borrower.login') }}" class="header-signin">
          <i class="bi bi-person-fill"></i>
          <span>Sign In</span>
        </a>
        <a href="{{ route('borrower.register') }}" class="header-apply">
          <i class="bi bi-arrow-right-circle-fill"></i>
          <span class="apply-text">Apply Now</span>
          <span class="apply-text-short" style="display:none">Apply</span>
        </a>
        <button class="nav-mobile-btn" onclick="toggleMobileNav()" aria-label="Open menu" id="hamburger-btn">
          <i class="bi bi-list" id="nav-icon"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- ═══ MOBILE NAV ═══════════════════════════════════════ -->
  <nav id="mobile-nav" role="dialog" aria-label="Mobile navigation">
    <button class="mobile-close" onclick="toggleMobileNav()" aria-label="Close menu">
      <i class="bi bi-x"></i>
    </button>
    <a href="{{ route('home') }}" onclick="toggleMobileNav()">Home</a>
    <div class="mobile-cta">
      <a href="{{ route('borrower.login') }}" class="btn btn-outline-white"
        style="justify-content:center;border-radius:99px" onclick="toggleMobileNav()">
        <i class="bi bi-person-fill"></i> Sign In
      </a>
      <a href="{{ route('borrower.register') }}" class="btn btn-accent"
        style="justify-content:center;border-radius:99px" onclick="toggleMobileNav()">
        <i class="bi bi-arrow-right-circle-fill"></i> Apply Now
      </a>
    </div>
  </nav>

  <!-- ═══ PAGE CONTENT ═════════════════════════════════════ -->
  <main>@yield('content')</main>

  <!-- ═══ FOOTER ═══════════════════════════════════════════ -->
  <footer>
    <div class="footer-inner">

      <!-- Left: contact -->
      <div class="footer-left">
        <a href="tel:+26659229149"><i class="bi bi-telephone-fill"></i>(+266) 59 229 149</a>
        <a href="mailto:info@myloan.co.ls"><i class="bi bi-envelope-fill"></i>info@myloan.co.ls</a>
        <p><i class="bi bi-geo-alt-fill"></i>L&amp;M Complex, Ha Thamae, Maseru</p>
      </div>

      <!-- Center: logo + copyright -->
      <div class="footer-center">
        <img src="{{ config('app.logo') }}" alt="MyLoan" class="footer-logo">
        <div class="footer-copy">MyLoan &copy; {{ date('Y') }} &middot; All rights reserved</div>
        <a href="https://www.facebook.com/share/18TBN7SB8y/?mibextid=wwXIfr" target="_blank" rel="noopener"
          class="footer-fb">
          <i class="bi bi-facebook" style="color:#4267B2;font-size:14px"></i> Facebook
        </a>
      </div>

      <!-- Right: payment logos -->
      <div class="footer-right">
        <!-- Visa -->
        <div class="pay-logo" title="Visa">
          <svg height="14" viewBox="0 0 80 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <text x="40" y="22" text-anchor="middle" font-family="Arial,sans-serif" font-size="22" font-weight="800"
              fill="white" font-style="italic" opacity=".7" letter-spacing="-1">VISA</text>
          </svg>
        </div>
        <!-- Mastercard -->
        <div class="pay-logo" title="Mastercard">
          <svg height="16" viewBox="0 0 38 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="14" cy="12" r="10" fill="#EB001B" opacity=".75" />
            <circle cx="24" cy="12" r="10" fill="#F79E1B" opacity=".75" />
            <path d="M19 5.5a10 10 0 0 1 0 13a10 10 0 0 1 0-13Z" fill="#FF5F00" opacity=".75" />
          </svg>
        </div>
        <!-- C-Pay -->
        <div class="pay-logo pay-logo-img-real" title="C-Pay">
          <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp_Image_2026-04-12_at_11.17.16_AM-removebg-preview.png"
            alt="C-Pay">
        </div>
        <!-- M-Pesa -->
        <div class="pay-logo pay-logo-img-real" title="M-Pesa">
          <img
            src="https://ik.imagekit.io/ygydr1m84/WhatsApp_Image_2026-04-12_at_11.19.20_AM-removebg-preview%20(1).png"
            alt="
            M-Pesa">
        </div>
        <!-- Payfast -->
        <div class="pay-logo pay-logo-img-real" title="Payfast">
          <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp_Image_2026-04-12_at_11.22.04_AM-removebg-preview.png"
            alt="Payfast">
        </div>
        <!-- CDAS -->
        <div class="pay-logo pay-logo-img-real" title="CDAS">
          <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp_Image_2026-04-15_at_8.57.51_AM-removebg-preview.png"
            alt="CDAS">
        </div>
        <!-- Experian -->
        <div class="pay-logo pay-logo-img-real" title="Experian">
          <img src="https://ik.imagekit.io/ygydr1m84/WhatsApp_Image_2026-04-15_at_8.56.58_AM-removebg-preview%20(1).png"
            alt="Experian">
        </div>
      </div>

    </div>
  </footer>

  <!-- ═══ WHATSAPP FLOAT ════════════════════════════════════ -->
  <a href="https://wa.me/26663445244" target="_blank" rel="noopener" class="whatsapp-float" title="Chat on WhatsApp"
    aria-label="Chat with us on WhatsApp">
    <svg viewBox="0 0 32 32" width="26" height="26" fill="#fff">
      <path
        d="M16.004 2.672c-7.36 0-13.332 5.972-13.332 13.332 0 2.348.616 4.644 1.788 6.664L2.672 29.328l6.82-1.788a13.28 13.28 0 006.512 1.704c7.36 0 13.332-5.972 13.332-13.332S23.364 2.672 16.004 2.672zm0 24.396a10.98 10.98 0 01-5.608-1.536l-.4-.24-4.152 1.088 1.108-4.056-.264-.416a10.95 10.95 0 01-1.684-5.836c0-6.076 4.944-11.02 11.02-11.02 6.076 0 11.02 4.944 11.02 11.02-.02 6.076-4.964 11.02-11.04 11.02v-.024zm6.04-8.252c-.332-.168-1.96-.968-2.264-1.08-.304-.112-.524-.168-.744.168-.22.332-.86 1.08-1.052 1.3-.192.22-.388.248-.72.084-.332-.168-1.404-.516-2.672-1.648-.988-.88-1.656-1.964-1.848-2.296-.196-.332-.02-.512.148-.676.148-.148.332-.388.496-.58.168-.196.22-.332.332-.556.112-.22.056-.416-.028-.58-.084-.168-.744-1.792-1.02-2.456-.268-.644-.54-.556-.744-.568-.192-.008-.416-.008-.636-.008a1.22 1.22 0 00-.884.416c-.304.332-1.16 1.132-1.16 2.76s1.188 3.2 1.356 3.42c.168.22 2.34 3.576 5.672 5.016.792.344 1.412.548 1.896.7.796.252 1.52.216 2.092.132.64-.096 1.96-.8 2.236-1.576.276-.776.276-1.44.192-1.576-.084-.14-.304-.22-.636-.388z" />
    </svg>
  </a>

  <style>
    .whatsapp-float {
      position: fixed;
      bottom: 22px;
      right: 22px;
      width: 52px;
      height: 52px;
      background: #25D366;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 16px rgba(37, 211, 102, .4), 0 2px 6px rgba(0, 0, 0, .15);
      z-index: 9999;
      text-decoration: none;
      transition: transform .25s ease, box-shadow .25s ease;
      animation: wa-pulse 2.5s infinite;
    }

    .whatsapp-float:hover {
      transform: scale(1.1);
      animation: none;
      box-shadow: 0 6px 24px rgba(37, 211, 102, .55), 0 4px 12px rgba(0, 0, 0, .2);
    }

    @keyframes wa-pulse {
      0% {
        box-shadow: 0 4px 16px rgba(37, 211, 102, .4), 0 0 0 0 rgba(37, 211, 102, .4);
      }

      70% {
        box-shadow: 0 4px 16px rgba(37, 211, 102, .4), 0 0 0 12px rgba(37, 211, 102, 0);
      }

      100% {
        box-shadow: 0 4px 16px rgba(37, 211, 102, .4), 0 0 0 0 rgba(37, 211, 102, 0);
      }
    }

    @media(max-width:480px) {
      .whatsapp-float {
        bottom: 16px;
        right: 14px;
        width: 46px;
        height: 46px;
      }

      .whatsapp-float svg {
        width: 22px;
        height: 22px;
      }
    }
  </style>

  <script>
    // Header scroll state
    const header = document.getElementById('header');
    const isTransparent = '@yield("header-class")' === 'transparent';
    function updateHeader() {
      if (isTransparent) {
        header.className = window.scrollY > 50 ? 'solid scrolled' : 'transparent';
      } else {
        header.className = window.scrollY > 50 ? 'solid scrolled' : 'solid';
      }
    }
    if (isTransparent) header.className = 'transparent';
    window.addEventListener('scroll', updateHeader, { passive: true });

    // Mobile nav
    function toggleMobileNav() {
      const nav = document.getElementById('mobile-nav');
      const icon = document.getElementById('nav-icon');
      const open = nav.classList.contains('open');
      nav.classList.toggle('open', !open);
      icon.className = open ? 'bi bi-list' : 'bi bi-x';
      document.body.style.overflow = open ? '' : 'hidden';
    }

    // Close mobile nav on Escape
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && document.getElementById('mobile-nav').classList.contains('open')) toggleMobileNav();
    });

    // Reveal on scroll
    const ro = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); ro.unobserve(e.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
  </script>

  @stack('scripts')
</body>

</html>