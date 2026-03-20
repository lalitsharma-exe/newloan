@extends('borrower.layouts.public')
@section('title','Privacy Policy')
@section('meta_desc','MyLoan Limited Privacy Policy — how we collect, use, and protect your personal information.')

@push('page-styles')
<style>
.page-hero{background:var(--forest);padding:120px 0 80px;color:#fff}
.page-hero h1{color:#fff;margin-bottom:14px}
.page-hero p{color:rgba(255,255,255,.6);font-size:18px}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px;font-family:'Outfit',sans-serif}
.breadcrumb a{color:rgba(255,255,255,.5);text-decoration:none}.breadcrumb a:hover{color:#fff}
.breadcrumb i{font-size:10px}
.legal-layout{display:grid;grid-template-columns:220px 1fr;gap:56px;align-items:start}
.legal-nav{position:sticky;top:92px}
.legal-nav a{display:block;font-size:13px;color:var(--slate);text-decoration:none;padding:8px 12px;border-left:2px solid var(--border);margin-bottom:2px;font-family:'Outfit',sans-serif;transition:all .2s}
.legal-nav a:hover{color:var(--green);border-left-color:var(--green)}
.legal-body h2{font-size:26px;margin-top:48px;margin-bottom:14px;padding-top:48px;border-top:1px solid var(--border)}
.legal-body h2:first-child{margin-top:0;padding-top:0;border-top:none}
.legal-body p{font-size:15px;line-height:1.85;color:var(--slate);margin-bottom:14px}
.legal-body ul{padding-left:20px;margin-bottom:14px}
.legal-body ul li{font-size:15px;line-height:1.85;color:var(--slate);margin-bottom:6px}
.last-updated{font-family:'Outfit',sans-serif;font-size:12px;color:var(--slate);margin-bottom:36px;padding:10px 14px;background:var(--ivory2);border-radius:4px;border-left:3px solid var(--gold)}
@media(max-width:768px){.legal-layout{grid-template-columns:1fr}.legal-nav{display:none}}
</style>
@endpush

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>Privacy Policy</span></div>
    <h1>Privacy Policy</h1>
    <p>How MyLoan collects, uses, and protects your personal information.</p>
  </div>
</div>

<section>
  <div class="container">
    <div class="legal-layout">
      <nav class="legal-nav">
        <a href="#collection">Information We Collect</a>
        <a href="#use">How We Use It</a>
        <a href="#sharing">Sharing</a>
        <a href="#security">Security</a>
        <a href="#rights">Your Rights</a>
        <a href="#contact">Contact</a>
      </nav>
      <div class="legal-body">
        <div class="last-updated">Last updated: January 2025</div>

        <h2 id="collection">1. Information We Collect</h2>
        <p>When you apply for a loan or use our portal, MyLoan Limited collects the following types of personal information:</p>
        <ul>
          <li><strong>Identity information</strong> — Full name, national ID number, date of birth, gender</li>
          <li><strong>Contact information</strong> — Phone number, email address, residential address</li>
          <li><strong>Employment information</strong> — Employer name, employment number, job title, payslips</li>
          <li><strong>Financial information</strong> — Bank account details, bank statements, income and expenditure details</li>
          <li><strong>Documents</strong> — Copies of national ID, payslips, and bank statements you upload</li>
        </ul>

        <h2 id="use">2. How We Use Your Information</h2>
        <p>We use your personal information to:</p>
        <ul>
          <li>Process and assess your loan application</li>
          <li>Verify your identity and employment</li>
          <li>Manage your loan account and process repayments</li>
          <li>Communicate with you about your account, application status, and payment reminders</li>
          <li>Comply with our legal and regulatory obligations under the Financial Institutions Act 2012</li>
          <li>Conduct credit bureau checks and report your repayment behaviour</li>
          <li>Prevent fraud and protect our clients and business</li>
        </ul>

        <h2 id="sharing">3. Sharing Your Information</h2>
        <p>We do not sell your personal information to third parties. We may share your information with:</p>
        <ul>
          <li><strong>Credit bureaus</strong> — As required by law, we report loan and repayment information to credit bureaus operating in Lesotho</li>
          <li><strong>Regulators</strong> — The Central Bank of Lesotho and other regulatory authorities as required</li>
          <li><strong>Service providers</strong> — Third-party providers who assist us in operating our systems (subject to confidentiality obligations)</li>
          <li><strong>Debt collection agencies</strong> — If your account becomes seriously delinquent, we may refer it to a collection agency</li>
        </ul>

        <h2 id="security">4. How We Protect Your Information</h2>
        <p>MyLoan uses reasonable technical and organisational measures to protect your personal information against unauthorised access, loss, or misuse. Our online portal uses encryption (HTTPS) for all data transmission.</p>
        <p>However, no method of transmission over the internet is 100% secure. We encourage you to use a strong, unique password for your portal account and to never share it with anyone.</p>

        <h2 id="rights">5. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
          <li>Access the personal information we hold about you</li>
          <li>Request correction of inaccurate information</li>
          <li>Request deletion of your information (subject to our legal obligations to retain certain records)</li>
          <li>Withdraw consent where processing is based on consent</li>
        </ul>
        <p>To exercise any of these rights, please contact us using the details below.</p>

        <h2 id="contact">6. Contact</h2>
        <p>If you have any questions about this Privacy Policy or how we handle your personal information, please contact us:</p>
        <ul>
          <li>Phone: (+266) 58 478 799</li>
          <li>Email: info@myloan.co.ls</li>
          <li>Address: L&amp;M Complex, Ha Thamae, Maseru, Lesotho</li>
        </ul>
      </div>
    </div>
  </div>
</section>
@endsection
