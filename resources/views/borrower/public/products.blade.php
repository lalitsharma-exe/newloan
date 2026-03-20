@extends('borrower.layouts.public')
@section('title','Loan Products')
@section('meta_desc','MyLoan loan products for government employees (up to M20,000), private sector workers and pensioners (up to M4,000) in Lesotho.')

@push('page-styles')
<style>
.page-hero{background:var(--forest);padding:120px 0 80px;color:#fff}
.page-hero h1{color:#fff;margin-bottom:14px}
.page-hero p{color:rgba(255,255,255,.6);font-size:18px;max-width:520px}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px;font-family:'Outfit',sans-serif}
.breadcrumb a{color:rgba(255,255,255,.5);text-decoration:none}.breadcrumb a:hover{color:#fff}
.breadcrumb i{font-size:10px}
.product-block{display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center;padding:72px 0;border-bottom:1px solid var(--border)}
.product-block:last-child{border-bottom:none}
.product-block.flip .product-details{order:2}
.product-block.flip .product-visual{order:1}
.product-visual{background:var(--forest);border-radius:8px;padding:44px 40px;color:#fff}
.product-visual h2{color:#fff;font-size:clamp(26px,3vw,40px);margin-bottom:10px}
.product-visual .range{font-family:'Outfit',sans-serif;font-size:13px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--light);margin-bottom:24px;display:block}
.fee-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.08);font-size:14px}
.fee-row:last-child{border-bottom:none}
.fee-row span:first-child{color:rgba(255,255,255,.5)}
.fee-row span:last-child{font-weight:600;color:#fff}
.eligibility-list{list-style:none;padding:0;margin-top:20px}
.eligibility-list li{display:flex;align-items:flex-start;gap:10px;font-size:15px;color:var(--slate);margin-bottom:12px;line-height:1.6}
.eligibility-list li i{color:var(--green);margin-top:3px;flex-shrink:0}
.req-tag{display:inline-block;background:var(--ivory2);border:1px solid var(--border);border-radius:3px;font-family:'Outfit',sans-serif;font-size:12px;font-weight:600;padding:4px 10px;margin:3px 3px 3px 0;color:var(--slate)}
@media(max-width:768px){.product-block{grid-template-columns:1fr;gap:36px}.product-block.flip .product-details,.product-block.flip .product-visual{order:unset}}
</style>
@endpush

@section('content')

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb">
      <a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>Products</span>
    </div>
    <h1>Our Loan Products</h1>
    <p>We provide the best financial services tailored specifically to each employment sector in Lesotho.</p>
  </div>
</div>

<section>
  <div class="container">

    {{-- Government --}}
    <div class="product-block">
      <div class="product-visual reveal">
        <span class="range">M100 – M20,000</span>
        <h2>Government Employee Loan</h2>
        <div style="margin-top:28px">
          @foreach(['Interest Rate'=>'15% per month (flat)','Initiation Fee'=>'40% of principal (once-off)','Admin Fee'=>'M50 per month','Late Penalty'=>'M20 per 10 days overdue','Min Term'=>'1 month','Max Term'=>'6 months'] as $l=>$v)
          <div class="fee-row"><span>{{ $l }}</span><span>{{ $v }}</span></div>
          @endforeach
        </div>
        <div style="margin-top:24px">
          <a href="{{ route('borrower.register') }}" class="btn btn-gold" style="width:100%;justify-content:center">Apply for this Loan</a>
        </div>
      </div>
      <div class="product-details reveal d2">
        <div class="eyebrow">Government Sector</div>
        <h2>For Public Sector Workers</h2>
        <div class="divider"></div>
        <p style="margin-bottom:24px">Designed specifically for permanent employees across Lesotho's government ministries, departments, and agencies. This product offers the highest loan amounts available, reflecting the stability of government employment.</p>
        <h4 style="margin-bottom:14px">Who Qualifies</h4>
        <ul class="eligibility-list">
          <li><i class="bi bi-check-circle-fill"></i>Permanent government employees (ministries and departments)</li>
          <li><i class="bi bi-check-circle-fill"></i>Teachers and educators at government schools</li>
          <li><i class="bi bi-check-circle-fill"></i>Nurses, doctors and health workers in public hospitals</li>
          <li><i class="bi bi-check-circle-fill"></i>Police, defence and security forces</li>
          <li><i class="bi bi-check-circle-fill"></i>Civil servants with regular government payslips</li>
        </ul>
        <h4 style="margin-top:24px;margin-bottom:12px">Required Documents</h4>
        <div>
          <span class="req-tag">National ID</span>
          <span class="req-tag">Latest Payslip</span>
          <span class="req-tag">3 Months Bank Statement</span>
        </div>
      </div>
    </div>

    {{-- Private --}}
    <div class="product-block flip">
      <div class="product-details reveal">
        <div class="eyebrow">Private Sector</div>
        <h2>For Private Sector Workers</h2>
        <div class="divider"></div>
        <p style="margin-bottom:24px">Built for employees in Lesotho's growing private sector. Whether you work for a local company or an international organisation, if you have a stable salary and can demonstrate repayment ability, this product is for you.</p>
        <h4 style="margin-bottom:14px">Who Qualifies</h4>
        <ul class="eligibility-list">
          <li><i class="bi bi-check-circle-fill"></i>Permanent employees in registered private companies</li>
          <li><i class="bi bi-check-circle-fill"></i>Contract workers with stable monthly salary</li>
          <li><i class="bi bi-check-circle-fill"></i>Workers with verifiable bank or mobile money payment history</li>
          <li><i class="bi bi-check-circle-fill"></i>Employees of NGOs and international organisations</li>
        </ul>
        <h4 style="margin-top:24px;margin-bottom:12px">Required Documents</h4>
        <div>
          <span class="req-tag">National ID</span>
          <span class="req-tag">Latest Payslip</span>
          <span class="req-tag">3 Months Bank Statement</span>
          <span class="req-tag">Employment Letter</span>
        </div>
      </div>
      <div class="product-visual reveal d2">
        <span class="range">M100 – M4,000</span>
        <h2>Private Sector Loan</h2>
        <div style="margin-top:28px">
          @foreach(['Interest Rate'=>'15% per month (flat)','Initiation Fee'=>'40% of principal (once-off)','Admin Fee'=>'M50 per month','Late Penalty'=>'M20 per 10 days overdue','Min Term'=>'1 month','Max Term'=>'6 months'] as $l=>$v)
          <div class="fee-row"><span>{{ $l }}</span><span>{{ $v }}</span></div>
          @endforeach
        </div>
        <div style="margin-top:24px">
          <a href="{{ route('borrower.register') }}" class="btn btn-gold" style="width:100%;justify-content:center">Apply for this Loan</a>
        </div>
      </div>
    </div>

    {{-- Pensioner --}}
    <div class="product-block">
      <div class="product-visual reveal">
        <span class="range">M100 – M4,000</span>
        <h2>Pensioner Loan</h2>
        <div style="margin-top:28px">
          @foreach(['Interest Rate'=>'15% per month (flat)','Initiation Fee'=>'40% of principal (once-off)','Admin Fee'=>'M50 per month','Late Penalty'=>'M20 per 10 days overdue','Min Term'=>'1 month','Max Term'=>'6 months'] as $l=>$v)
          <div class="fee-row"><span>{{ $l }}</span><span>{{ $v }}</span></div>
          @endforeach
        </div>
        <div style="margin-top:24px">
          <a href="{{ route('borrower.register') }}" class="btn btn-gold" style="width:100%;justify-content:center">Apply for this Loan</a>
        </div>
      </div>
      <div class="product-details reveal d2">
        <div class="eyebrow">Pensioners</div>
        <h2>For Retirees &amp; Pensioners</h2>
        <div class="divider"></div>
        <p style="margin-bottom:16px">We understand that pensioners' lives after retirement should be peaceful and dignified. Unexpected expenses — medical bills, repairs, family obligations — should not cause distress.</p>
        <p style="margin-bottom:24px">We provide affordable, respectful, and easy-to-access loans specially designed for pensioners, with terms structured around pension payment cycles.</p>
        <h4 style="margin-bottom:14px">Who Qualifies</h4>
        <ul class="eligibility-list">
          <li><i class="bi bi-check-circle-fill"></i>Recipients of government pension payments</li>
          <li><i class="bi bi-check-circle-fill"></i>Retirees with verifiable regular pension income</li>
          <li><i class="bi bi-check-circle-fill"></i>Must have a bank account receiving pension deposits</li>
        </ul>
        <h4 style="margin-top:24px;margin-bottom:12px">Required Documents</h4>
        <div>
          <span class="req-tag">National ID</span>
          <span class="req-tag">Pension Payslip</span>
          <span class="req-tag">3 Months Bank Statement</span>
        </div>
      </div>
    </div>

  </div>
</section>

{{-- CTA --}}
<section style="background:var(--green);padding:72px 0">
  <div class="container" style="text-align:center">
    <div class="reveal">
      <h2 style="color:#fff;margin-bottom:12px">Ready to Apply?</h2>
      <p style="color:rgba(255,255,255,.7);margin-bottom:28px">Choose your product and apply online in minutes.</p>
      <a href="{{ route('borrower.register') }}" class="btn btn-gold btn-lg">Start Your Application</a>
    </div>
  </div>
</section>

@endsection
