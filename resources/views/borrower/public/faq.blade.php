@extends('borrower.layouts.public')
@section('title','Frequently Asked Questions')
@section('meta_desc','Answers to common questions about Prosperity Loans products, application process, fees, and repayment in Lesotho.')

@push('page-styles')
<style>
.page-hero{background:var(--forest);padding:120px 0 80px;color:#fff}
.page-hero h1{color:#fff;margin-bottom:14px}
.page-hero p{color:rgba(255,255,255,.6);font-size:18px;max-width:520px}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px;font-family:'DM Sans',sans-serif}
.breadcrumb a{color:rgba(255,255,255,.5);text-decoration:none}.breadcrumb a:hover{color:#fff}
.breadcrumb i{font-size:10px}
.faq-layout{display:grid;grid-template-columns:240px 1fr;gap:56px;align-items:start}
.faq-nav{position:sticky;top:92px}
.faq-nav a{display:block;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--slate);text-decoration:none;padding:9px 14px;border-left:2px solid var(--border);margin-bottom:2px;transition:all .2s}
.faq-nav a:hover,.faq-nav a.active{color:var(--green);border-left-color:var(--green);background:rgba(26,92,46,.04)}
.faq-section{margin-bottom:56px}
.faq-section h3{font-size:24px;margin-bottom:28px;padding-bottom:14px;border-bottom:1px solid var(--border)}
.faq-item{border-bottom:1px solid var(--border);overflow:hidden}
.faq-q{width:100%;background:none;border:none;text-align:left;padding:20px 0;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:16px}
.faq-q-text{font-family:'DM Sans',sans-serif;font-size:15px;font-weight:500;color:var(--ink)}
.faq-icon{font-size:18px;color:var(--green);flex-shrink:0;transition:transform .3s}
.faq-item.open .faq-icon{transform:rotate(45deg)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s ease}
.faq-item.open .faq-a{max-height:300px;padding-bottom:18px}
.faq-a p{font-size:14px;line-height:1.8;color:var(--slate)}
@media(max-width:768px){.faq-layout{grid-template-columns:1fr}.faq-nav{display:none}}
</style>
@endpush

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>FAQ</span></div>
    <h1>Frequently Asked Questions</h1>
    <p>Everything you need to know about Prosperity Loans, our products, and the application process.</p>
  </div>
</div>
<section>
  <div class="container">
    <div class="faq-layout">
      <nav class="faq-nav">
        <a href="#applying">Applying</a>
        <a href="#products">Products &amp; Fees</a>
        <a href="#documents">Documents</a>
        <a href="#repayment">Repayment</a>
        <a href="#account">Your Account</a>
      </nav>
      <div>
        @php
        $faqs = [
          'applying' => ['Applying for a Loan', [
            ['How do I apply for a loan?', 'You can apply entirely online through our borrower portal at /portal/register. Create an account, complete the 9-step application form, upload your documents, and submit. Our team will review your application and notify you of the decision.'],
            ['How long does approval take?', 'We aim to review applications as quickly as possible. Once you have submitted all required documents, our team will process your application and you will receive a notification. Complex cases may take longer.'],
            ['Can I apply if I have an existing loan?', 'Generally you must fully repay an existing Prosperity Loans loan before applying for a new one. Please contact us directly if you have questions about your eligibility.'],
            ['Is there a minimum age requirement?', 'Yes. You must be at least 18 years of age to apply for a loan with Prosperity Loans Limited.'],
          ]],
          'products' => ['Products & Fees', [
            ['What is the interest rate?', 'Prosperity Loans charges a flat interest rate of 15% per month on the original loan principal. This means the interest amount is the same every month regardless of your outstanding balance.'],
            ['What is the initiation fee?', 'A once-off initiation fee of 40% of the loan principal is charged when a loan is granted. This fee is spread across your monthly installments and covers loan processing and administration.'],
            ['What is the admin fee?', 'A fixed monthly administration fee of M50 is charged each month for the duration of your loan term.'],
            ['Are there penalties for late payment?', 'Yes. A late payment fee of M20 is charged for every 10 days that a required installment remains unpaid. We encourage you to contact us if you are experiencing difficulty making a payment.'],
            ['What is the maximum loan I can get?', 'Government employees can borrow up to M20,000. Private sector employees and pensioners can borrow up to M4,000. Minimum loan amount is M100 for all products.'],
          ]],
          'documents' => ['Required Documents', [
            ['Do you have an office?', 'Yes, we are located in the main city center. You can view our map link on the contact page, or use the portal to do everything online.'],
            ['Can I upload documents online?', 'Yes. Our portal allows you to upload documents securely as part of the application process. Accepted formats are PDF, JPG, and PNG up to 10MB per file.'],
            ['What if my document is rejected?', 'If a document is rejected, you will receive a notification explaining the reason. Common reasons include blurry images, expired documents, or wrong document type. You can re-upload corrected documents through your portal account.'],
          ]],
          'repayment' => ['Repayment', [
            ['How do I make repayments?', 'Repayments can be made via salary deduction, debit order, mobile money, or bank transfer/deposit. Your preferred collection method is agreed at the time of application.'],
            ['Can I repay my loan early?', 'Yes, you may repay your loan at any time before the maturity date. Please note that no discount applies for early settlement — all interest and fees as agreed in the contract remain payable.'],
            ['What happens if I miss a payment?', 'Missing a payment will result in late fees being charged (M20 per 10 days). Continued non-payment may result in your account being escalated to collections. Please contact us immediately if you are struggling — we will do our best to assist.'],
            ['How do I get a settlement quotation?', 'You can request a settlement quotation through your borrower portal at any time. It will show the full outstanding amount required to close the loan on a given date.'],
          ]],
          'account' => ['Your Account', [
            ['How do I log in to my account?', 'Visit /portal/login and sign in with your registered phone number or email address and password.'],
            ['I forgot my password. What do I do?', 'On the login page, click "Forgot password?" and enter your registered phone number. Follow the instructions to reset your password.'],
            ['Can I update my personal details?', 'Yes. Log in to your portal account and navigate to the Profile section to update your personal information, employment details, bank details, and next of kin.'],
            ['How do I contact support?', 'Call us on (+266) 5694 7028 / 5724 7936 / 6321 8591, email prosperityloans1@gmail.com, or visit our office at Ha Matala, KK Building, Maseru.'],
          ]],
        ];
        @endphp

        @foreach($faqs as $id => [$title, $items])
        <div class="faq-section reveal" id="{{ $id }}">
          <h3>{{ $title }}</h3>
          @foreach($items as $fi => [$q, $a])
          <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
              <span class="faq-q-text">{{ $q }}</span>
              <i class="bi bi-plus faq-icon"></i>
            </button>
            <div class="faq-a"><p>{{ $a }}</p></div>
          </div>
          @endforeach
        </div>
        @endforeach

        <div style="background:var(--ivory2);border-radius:6px;padding:32px 36px;margin-top:20px;border:1px solid var(--border)" class="reveal">
          <h3 style="font-size:24px;margin-bottom:10px">Still Have Questions?</h3>
          <p style="margin-bottom:20px">Our team is happy to help. Reach out by phone, email, or visit us in person.</p>
          <a href="{{ route('contact') }}" class="btn btn-primary">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}
</script>
@endpush
