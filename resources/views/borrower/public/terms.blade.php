@extends('borrower.layouts.public')
@section('title','Terms & Conditions')
@section('meta_desc','Prosperity Loans Limited Terms and Conditions of use for our website and borrower portal.')

@push('page-styles')
<style>
.page-hero{background:var(--forest);padding:120px 0 80px;color:#fff}
.page-hero h1{color:#fff;margin-bottom:14px}
.page-hero p{color:rgba(255,255,255,.6);font-size:18px}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px;font-family:'DM Sans',sans-serif}
.breadcrumb a{color:rgba(255,255,255,.5);text-decoration:none}.breadcrumb a:hover{color:#fff}
.breadcrumb i{font-size:10px}
.legal-body{max-width:800px;margin:0 auto}
.legal-body h2{font-size:24px;margin-top:44px;margin-bottom:12px;padding-top:44px;border-top:1px solid var(--border)}
.legal-body h2:first-child{margin-top:0;padding-top:0;border-top:none}
.legal-body p{font-size:15px;line-height:1.85;color:var(--slate);margin-bottom:14px}
.legal-body ul{padding-left:20px;margin-bottom:14px}
.legal-body ul li{font-size:15px;line-height:1.85;color:var(--slate);margin-bottom:6px}
.last-updated{font-family:'DM Sans',sans-serif;font-size:12px;color:var(--slate);margin-bottom:36px;padding:10px 14px;background:var(--ivory2);border-radius:4px;border-left:3px solid var(--gold)}
</style>
@endpush

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>Terms &amp; Conditions</span></div>
    <h1>Terms &amp; Conditions</h1>
    <p>Please read these terms carefully before using our website or portal.</p>
  </div>
</div>

<section>
  <div class="container">
    <div class="legal-body">
      <div class="last-updated">Last updated: January 2025</div>

      <h2>1. Acceptance of Terms</h2>
      <p>By accessing or using the Prosperity Loans Limited website and borrower portal, you agree to be bound by these Terms and Conditions. If you do not agree, please do not use our services.</p>

      <h2>2. About Prosperity Loans Limited</h2>
      <p>Prosperity Loans Limited is a Credit Only Micro Finance Institution Tier II, licensed under the Financial Institutions Act 2012 and regulated by the Central Bank of Lesotho. We are incorporated and operate in the Kingdom of Lesotho.</p>

      <h2>3. Eligibility</h2>
      <p>To apply for a loan through Prosperity Loans, you must:</p>
      <ul>
        <li>Be at least 18 years of age</li>
        <li>Be a resident of the Kingdom of Lesotho</li>
        <li>Be employed or in receipt of a regular pension income</li>
        <li>Provide truthful and accurate information throughout the application process</li>
      </ul>

      <h2>4. Loan Agreement</h2>
      <p>Any loan approved by Prosperity Loans Limited is subject to a separate Loan Agreement which sets out the specific terms, interest rates, fees, repayment schedule, and obligations for that loan. The Loan Agreement, once signed or accepted electronically, constitutes a legally binding contract between you and Prosperity Loans Limited.</p>

      <h2>5. Accurate Information</h2>
      <p>You agree to provide accurate, complete, and up-to-date information when applying for a loan and throughout the life of your account. Providing false or misleading information is a material breach of your loan agreement and may result in immediate recall of the loan and/or legal proceedings.</p>

      <h2>6. Portal Use</h2>
      <p>You are responsible for maintaining the confidentiality of your portal login credentials. You agree not to share your username and password with any other person. Prosperity Loans will not be liable for any loss arising from unauthorised access to your account due to your failure to protect your credentials.</p>

      <h2>7. Intellectual Property</h2>
      <p>All content on this website — including text, graphics, logos, and software — is the property of Prosperity Loans Limited and is protected by applicable intellectual property laws. You may not reproduce or distribute any content without our prior written consent.</p>

      <h2>8. Limitation of Liability</h2>
      <p>Prosperity Loans Limited is not liable for any indirect, incidental, or consequential damages arising from your use of this website or portal. Our total liability to you shall not exceed the value of any fees you have paid to us in the preceding 3 months.</p>

      <h2>9. Changes to Terms</h2>
      <p>We reserve the right to update these Terms and Conditions at any time. Continued use of our services after any changes constitutes your acceptance of the revised terms. We will endeavour to notify registered users of significant changes.</p>

      <h2>10. Governing Law</h2>
      <p>These Terms and Conditions are governed by the laws of the Kingdom of Lesotho. Any disputes arising shall be subject to the jurisdiction of the courts of Lesotho.</p>

      <h2>11. Contact</h2>
      <p>For questions about these Terms, please contact us at info@prosperityloans.co.ls or call (+266) 58 478 799.</p>
    </div>
  </div>
</section>
@endsection
