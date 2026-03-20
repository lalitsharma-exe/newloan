@extends('borrower.layouts.public')
@section('title', 'About Us')
@section('meta_desc', 'Learn about MyLoan Limited — our mission, vision, and commitment to providing simple, fast and secure financial solutions in Lesotho.')

@push('page-styles')
<style>
.page-hero{background:var(--forest);padding:120px 0 80px;color:#fff}
.page-hero h1{color:#fff;margin-bottom:14px}
.page-hero p{color:rgba(255,255,255,.6);font-size:18px;max-width:520px}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px;font-family:'Outfit',sans-serif}
.breadcrumb a{color:rgba(255,255,255,.5);text-decoration:none;transition:color .2s}.breadcrumb a:hover{color:#fff}
.breadcrumb i{font-size:10px}
.value-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:48px}
.value-card{padding:36px 30px;border:1px solid var(--border);border-radius:6px;transition:border-color .3s,box-shadow .3s}
.value-card:hover{border-color:rgba(26,92,46,.3);box-shadow:0 8px 30px rgba(15,61,31,.08)}
.value-card i{font-size:28px;color:var(--green);margin-bottom:16px;display:block}
.value-card h3{font-size:22px;margin-bottom:10px}
.value-card p{font-size:14px;line-height:1.75}
.team-note{background:var(--ivory2);border-radius:6px;padding:36px 40px;margin-top:60px;display:flex;gap:40px;align-items:center}
.team-note-text h3{font-size:28px;margin-bottom:10px}
.team-note-text p{font-size:15px;line-height:1.8}
.reg-box{background:var(--charcoal);color:#fff;border-radius:6px;padding:36px 40px;margin-top:60px}
.reg-box h3{color:#fff;font-size:26px;margin-bottom:16px}
.reg-box p{color:rgba(255,255,255,.55);font-size:15px;line-height:1.8}
@media(max-width:768px){.value-grid{grid-template-columns:1fr}.team-note{flex-direction:column;gap:20px;padding:28px}.reg-box{padding:28px}}
</style>
@endpush

@section('content')

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb">
      <a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>About Us</span>
    </div>
    <h1>About MyLoan</h1>
    <p>We exist to make financial services accessible, transparent, and dignified for every person in Lesotho.</p>
  </div>
</div>

<section>
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:start">
      <div class="reveal">
        <div class="eyebrow">Who We Are</div>
        <h2>Delivering the Best Customer Experience</h2>
        <div class="divider"></div>
        <p style="margin-bottom:18px">MyLoan Limited is a registered microfinance institution based in Maseru, Lesotho. We were founded with a single purpose: to provide simple, accessible, and honest financial solutions to working Basotho who need them most.</p>
        <p style="margin-bottom:18px">We serve government employees, private sector workers, and pensioners — people with stable incomes who deserve fair and transparent credit products without the complexity of traditional banks.</p>
        <p>Every loan we offer comes with full fee disclosure, a clear repayment schedule, and a dedicated support team to answer your questions.</p>
      </div>
      <div class="reveal d2">
        <div style="background:var(--ivory);border-radius:6px;padding:36px 32px;border:1px solid var(--border)">
          <div class="eyebrow">Our Mission</div>
          <p style="font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--ink);font-style:italic;line-height:1.55;margin-bottom:24px">&ldquo;To provide simple, fast, and secure financial solutions that empower people to meet their financial needs.&rdquo;</p>
          <hr style="border:none;border-top:1px solid var(--border);margin-bottom:24px">
          <div class="eyebrow">Our Vision</div>
          <p style="font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--ink);font-style:italic;line-height:1.55">&ldquo;To become a leading and trusted financial services provider in Lesotho.&rdquo;</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background:var(--ivory);padding:80px 0">
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:520px;margin:0 auto">
      <div class="eyebrow" style="justify-content:center">Our Values</div>
      <h2>What We Stand For</h2>
      <div class="divider divider-center"></div>
    </div>
    <div class="value-grid">
      @foreach([
        ['patch-check-fill','Transparency','We disclose every fee and charge before you sign. No hidden costs, no surprises — ever.'],
        ['heart-fill','Respect','Every client is treated with dignity. We listen, explain, and never rush or pressure you.'],
        ['lightning-charge-fill','Efficiency','Your time is valuable. We work hard to process applications as quickly as possible.'],
        ['shield-check-fill','Integrity','We operate within the law and hold ourselves accountable to the highest ethical standards.'],
        ['people-fill','Inclusion','Our products are designed for working Basotho across all sectors, backgrounds, and income levels.'],
        ['graph-up','Empowerment','We believe access to fair credit should improve lives — not trap people in cycles of debt.'],
      ] as $i => [$ic,$t,$d])
      <div class="value-card reveal d{{ ($i%3)+1 }}">
        <i class="bi bi-{{ $ic }}"></i>
        <h3>{{ $t }}</h3>
        <p>{{ $d }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="reg-box reveal">
      <div class="eyebrow" style="color:var(--light)"><span style="background:var(--light)"></span>Legal Status</div>
      <h3>Licensed &amp; Regulated</h3>
      <p>Myloan Limited is a company licensed under the Financial Institutions Act 2012 and Financial Institutions (Credit only and Deposit taking Financial Institutions) (Amendment) Regulations 2014, as amended in 2018, as a Credit Only Micro Finance Institution Tier II and regulated by the Central Bank of Lesotho.</p>
      <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
        <a href="{{ route('contact') }}" class="btn btn-gold">Contact Us</a>
        <a href="{{ route('borrower.register') }}" class="btn btn-outline" style="border-color:rgba(255,255,255,.25);color:#fff">Apply Now</a>
      </div>
    </div>
  </div>
</section>

@endsection
