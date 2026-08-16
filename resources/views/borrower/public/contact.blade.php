@extends('borrower.layouts.public')
@section('title','Contact Us')
@section('meta_desc','Contact Prosperity Loans Limited — (+266) 5694 7028 / 5724 7936 / 6321 8591 — prosperityloans1@gmail.com — Ha Matala, KK Building, Maseru, Lesotho.')

@push('page-styles')
<style>
.page-hero{background:var(--forest);padding:120px 0 80px;color:#fff}
.page-hero h1{color:#fff;margin-bottom:14px}
.page-hero p{color:rgba(255,255,255,.6);font-size:18px;max-width:520px}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px;font-family:'DM Sans',sans-serif}
.breadcrumb a{color:rgba(255,255,255,.5);text-decoration:none}.breadcrumb a:hover{color:#fff}
.breadcrumb i{font-size:10px}
.contact-layout{display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:start}
.contact-info-item{display:flex;gap:18px;align-items:flex-start;margin-bottom:36px}
.contact-icon{width:48px;height:48px;border-radius:6px;background:rgba(26,92,46,.08);display:flex;align-items:center;justify-content:center;color:var(--green);font-size:20px;flex-shrink:0}
.contact-info-item h4{font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--slate);margin-bottom:4px}
.contact-info-item p,.contact-info-item a{font-size:16px;color:var(--ink);text-decoration:none;line-height:1.5;display:block;transition:color .2s}
.contact-info-item a:hover{color:var(--green)}
.form-card{background:var(--ivory);border-radius:8px;padding:40px 36px;border:1px solid var(--border)}
.form-card h3{font-size:28px;margin-bottom:6px}
.form-card .sub{font-size:14px;color:var(--slate);margin-bottom:28px}
.fg{margin-bottom:20px}
.fl{display:block;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--slate);margin-bottom:6px}
.fc{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:4px;font-size:14px;font-family:'DM Sans',sans-serif;background:var(--white);outline:none;transition:border-color .2s,box-shadow .2s;color:var(--ink)}
.fc:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(26,92,46,.08)}
textarea.fc{resize:vertical;min-height:120px}
@media(max-width:768px){.contact-layout{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right"></i><span>Contact</span></div>
    <h1>Contact Us</h1>
    <p>We are here to help. Reach our team by phone, email, or visit us in person.</p>
  </div>
</div>

<section>
  <div class="container">
    <div class="contact-layout">
      <div class="reveal">
        <div class="eyebrow">Get in Touch</div>
        <h2>We're Here<br>to Help</h2>
        <div class="divider"></div>
        <p style="margin-bottom:40px">Have a question about our products or your application? Our team is available to assist you Monday to Friday.</p>

        <div class="contact-info-item">
          <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
          <div>
            <h4>Phone</h4>
            <a href="tel:+26656947028">(+266) 5694 7028 / 5724 7936 / 6321 8591</a>
            <p style="font-size:13px;color:var(--slate);margin-top:4px">Monday – Friday, 8:00 AM – 5:00 PM</p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
          <div>
            <h4>Email</h4>
            <a href="mailto:prosperityloans1@gmail.com">prosperityloans1@gmail.com</a>
            <p style="font-size:13px;color:var(--slate);margin-top:4px">We respond within 1 business day</p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <h4>Office Address</h4>
            <p>Ha Matala, KK Building<br>Maseru, Lesotho</p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-icon"><i class="bi bi-clock-fill"></i></div>
          <div>
            <h4>Office Hours</h4>
            <p>Monday – Friday: 8:00 AM – 5:00 PM<br>Saturday: 9:00 AM – 1:00 PM<br>Sunday &amp; Public Holidays: Closed</p>
          </div>
        </div>
      </div>

      <div class="form-card reveal d2">
        <h3>Send a Message</h3>
        <p class="sub">Fill in the form and we will get back to you within one business day.</p>

        @if(session('success'))
        <div style="background:rgba(26,92,46,.08);border:1px solid rgba(26,92,46,.2);color:var(--green);padding:14px 18px;border-radius:4px;font-size:14px;margin-bottom:20px;display:flex;align-items:center;gap:8px">
          <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('contact.send') }}">
          @csrf
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="fg">
              <label class="fl">Full Name *</label>
              <input type="text" name="name" class="fc" value="{{ old('name') }}" required>
              @error('name')<span style="font-size:12px;color:#dc2626;margin-top:3px;display:block">{{ $message }}</span>@enderror
            </div>
            <div class="fg">
              <label class="fl">Phone Number</label>
              <input type="tel" name="phone" class="fc" value="{{ old('phone') }}" placeholder="+266...">
            </div>
          </div>
          <div class="fg">
            <label class="fl">Email Address</label>
            <input type="email" name="email" class="fc" value="{{ old('email') }}">
          </div>
          <div class="fg">
            <label class="fl">Subject *</label>
            <select name="subject" class="fc" required>
              <option value="">— Select a subject —</option>
              <option {{ old('subject')==='Loan Enquiry'?'selected':'' }}>Loan Enquiry</option>
              <option {{ old('subject')==='Application Status'?'selected':'' }}>Application Status</option>
              <option {{ old('subject')==='Repayment Query'?'selected':'' }}>Repayment Query</option>
              <option {{ old('subject')==='Account Issue'?'selected':'' }}>Account Issue</option>
              <option {{ old('subject')==='General Query'?'selected':'' }}>General Query</option>
              <option {{ old('subject')==='Complaint'?'selected':'' }}>Complaint</option>
            </select>
          </div>
          <div class="fg">
            <label class="fl">Message *</label>
            <textarea name="message" class="fc" rows="5" required>{{ old('message') }}</textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
            <i class="bi bi-send"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
