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

<div class="card" style="margin-bottom:16px">
  <div class="card-hdr"><span class="card-title">Personal Information</span></div>
  <form method="POST" action="{{ route('borrower.profile.personal') }}" class="card-body">@csrf @method('PUT')
    <div class="g2">
      <div class="fg"><label class="fl">Full Name</label><input type="text" name="name" class="fc" value="{{ old('name',$user->name) }}" required></div>
      <div class="fg"><label class="fl">Date of Birth</label><input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth',$user->date_of_birth?->format('Y-m-d')) }}"></div>
      <div class="fg" style="grid-column:span 2"><label class="fl">Address</label><input type="text" name="address" class="fc" value="{{ old('address',$user->address) }}"></div>
    </div>
    <div style="text-align:right"><button type="submit" class="btn btn-p btn-sm">Save</button></div>
  </form>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-hdr"><span class="card-title">Account Details</span></div>
  <div class="card-body">
    <div class="g2">
      @foreach(['Phone'=>$user->phone??'—','Email'=>$user->email??'Not set','ID Number'=>$user->national_id??'—','Member Since'=>$user->created_at->format('d M Y'),'Last Login'=>$user->last_login_at?->diffForHumans()??'—','Status'=>$user->is_active?'Active':'Inactive'] as $l=>$v)
      <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v }}</div></div>
      @endforeach
    </div>
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
@endsection
