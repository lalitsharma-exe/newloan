@extends('borrower.layouts.app')
@section('title','My Profile')
@section('content')
<div style="font-size:20px;font-weight:800;margin-bottom:20px">My Profile</div>

<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
  <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:26px;flex-shrink:0">{{ strtoupper(substr(auth('borrower')->user()->name,0,1)) }}</div>
  <div>
    <div style="font-size:20px;font-weight:800">{{ auth('borrower')->user()->name }}</div>
    <div style="font-size:13px;color:var(--muted)">{{ auth('borrower')->user()->phone }}</div>
  </div>
</div>

@if(session('success'))
<div style="padding:12px;background:#e1f5fe;color:#01579b;border-radius:8px;margin-bottom:16px;font-size:14px;font-weight:600">{{ session('success') }}</div>
@endif

<div class="card" style="margin-bottom:16px">
  <div class="card-hdr"><span class="card-title">Personal Information</span></div>
  <div class="card-body">
    <div class="g2">
      <div class="fg"><label class="fl">Full Name</label><input type="text" class="fc" value="{{ $user->name }}" readonly disabled></div>
      <div class="fg"><label class="fl">Maiden Name</label><input type="text" class="fc" value="{{ $user->maiden_name ?? '—' }}" readonly disabled></div>
      <div class="fg"><label class="fl">Date of Birth</label><input type="text" class="fc" value="{{ $user->date_of_birth?->format('d M Y') ?? 'Not set' }}" readonly disabled></div>
      <div class="fg" style="grid-column:span 2"><label class="fl">Address</label><input type="text" class="fc" value="{{ $user->address ?? 'Not set' }}" readonly disabled></div>
    </div>
    <div style="margin-top:10px;font-size:12px;color:var(--muted);"><i class="bi bi-info-circle"></i> Profile updates are restricted. Please submit a request below for any changes.</div>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-hdr"><span class="card-title">Request Profile Change</span></div>
  <form method="POST" action="{{ route('borrower.profile.request-change') }}" class="card-body">@csrf
    <div class="fg">
        <label class="fl">Details of change</label>
        <textarea name="requested_details" class="fc" rows="3" placeholder="Describe the changes you want to make to your profile (e.g. Change address to...)" required></textarea>
    </div>
    <div style="text-align:right"><button type="submit" class="btn btn-p btn-sm">Submit Request</button></div>
  </form>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-hdr"><span class="card-title">Account Details</span></div>
  <div class="card-body">
    <div class="g2">
      @foreach(['Phone'=>$user->phone??'—','Email'=>$user->email??'Not set','ID Number'=>$user->national_id??'—','Maiden Name'=>$user->maiden_name??'—','Member Since'=>$user->created_at->format('d M Y'),'Last Login'=>$user->last_login_at?->diffForHumans()??'—','Status'=>$user->is_active?'Active':'Inactive'] as $l=>$v)
      <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v }}</div></div>
      @endforeach
    </div>
  </div>
</div>

<div class="card" style="margin-top:20px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-pencil-square" style="color:var(--p)"></i> Request Profile Update</span>
  </div>
  <div class="card-body">
    @if($latestRequest && $latestRequest->status === 'pending')
      <div class="alert bi mb-4" style="background:rgba(59,130,246,.08);color:#1e40af;border:1px solid rgba(59,130,246,.2);padding:12px;border-radius:10px;font-size:13px">
        <i class="bi bi-clock-history"></i> You have a pending change request submitted on {{ $latestRequest->created_at->format('d M Y') }}.
      </div>
    @elseif($latestRequest && $latestRequest->status === 'approved')
       <div class="alert bs mb-4" style="background:rgba(22,163,74,.08);color:#065f46;border:1px solid rgba(22,163,74,.2);padding:12px;border-radius:10px;font-size:13px">
        <i class="bi bi-check-circle"></i> Your last request was approved. @if($latestRequest->admin_note) <strong>Note:</strong> {{ $latestRequest->admin_note }} @endif
      </div>
    @elseif($latestRequest && $latestRequest->status === 'rejected')
       <div class="alert be mb-4" style="background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2);padding:12px;border-radius:10px;font-size:13px">
        <i class="bi bi-x-circle"></i> Your last request was rejected. @if($latestRequest->admin_note) <strong>Note:</strong> {{ $latestRequest->admin_note }} @endif
      </div>
    @endif

    <p style="font-size:13px; color:var(--muted); margin-bottom:16px">
      Direct profile editing is disabled. If you need to update your personal details, address, or banking information, please describe the changes below. An administrator will review and update your profile for you.
    </p>

    <form method="POST" action="{{ route('borrower.profile.request-change') }}">
      @csrf
      <div class="fg">
        <label class="fl">Describe requested changes</label>
        <textarea name="requested_details" class="fc" style="height:120px" placeholder="e.g. Please change my phone number to +266 1234 5678 and update my bank account number to..." required></textarea>
      </div>
      <button type="submit" class="btn btn-p" @if($latestRequest && $latestRequest->status==='pending') disabled @endif>
        <i class="bi bi-send"></i> Submit Request
      </button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-lock-fill" style="color:var(--warn)"></i> Change Password</span></div>
  <form method="POST" action="{{ route('borrower.profile.password') }}" class="card-body">@csrf @method('PUT')
    <div class="g2">
      <div class="fg"><label class="fl">Current Password</label><input type="password" name="current_password" class="fc" required>@error('current_password')<span class="iv">{{ $message }}</span>@enderror</div>
      <div></div>
      <div class="fg"><label class="fl">New Password</label><input type="password" name="password" class="fc" placeholder="Min 8 characters" required></div>
      <div class="fg"><label class="fl">Confirm New Password</label><input type="password" name="password_confirmation" class="fc" required></div>
    </div>
    <div style="text-align:right"><button type="submit" class="btn btn-w btn-sm"><i class="bi bi-lock-fill"></i> Change Password</button></div>
  </form>
</div>
<div class="card" style="margin-top:20px; border:none; background: linear-gradient(135deg, #0f4527, #22894e); color:#fff; overflow:hidden; position:relative;">
  <div style="display:flex; align-items:center;">
    <div style="flex:1; padding:40px; position:relative; z-index:2;">
      <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); padding:6px 12px; border-radius:30px; font-size:11px; font-weight:700; margin-bottom:16px;">
        <i class="bi bi-shield-lock-fill" style="color:var(--accent)"></i> PROTECTED BY MYLOAN SECURE
      </div>
      <h3 style="font-family:'Playfair Display',serif; font-size:32px; font-weight:700; margin-bottom:12px;">Your data is <span style="color:var(--accent)">secure.</span></h3>
      <p style="font-size:14px; opacity:0.8; line-height:1.6; max-width:400px;">We use bank-grade encryption and multi-factor authentication to ensure your personal information and financial history remain private and protected at all times.</p>
    </div>
    <div style="width:300px; height:280px; position:relative; overflow:hidden;">
        <img src="{{ asset('financial_security_shield_1778866185839.png') }}" style="width:100%; height:100%; object-fit:cover; mix-blend-mode: lighten; opacity:0.9;">
        <div style="position:absolute; inset:0; background:linear-gradient(to right, #0f4527, transparent);"></div>
    </div>
  </div>
</div>
@endsection
