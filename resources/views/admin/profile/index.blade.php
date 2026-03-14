@extends('admin.layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / My Profile
@endsection
@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div style="display:grid;grid-template-columns:300px 1fr;gap:22px;align-items:start">

  {{-- ── LEFT: Avatar card ───────────────────────────────────── --}}
  <div class="card" style="text-align:center;padding:30px 22px">
    @if($user->profile_photo)
    <img src="{{ Storage::disk('public')->url($user->profile_photo) }}"
         alt="{{ $user->name }}"
         style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 14px;display:block;border:3px solid var(--border)">
    @else
    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:30px;margin:0 auto 14px">
      {{ strtoupper(substr($user->name,0,1)) }}
    </div>
    @endif
    <div style="font-size:17px;font-weight:700">{{ $user->name }}</div>
    <div style="font-size:13px;color:var(--muted);margin-top:4px">{{ $user->email }}</div>
    <span class="badge {{ $user->role==='admin'?'be':'bi' }}" style="margin-top:10px;display:inline-block">
      {{ ucfirst(str_replace('_',' ',$user->role)) }}
    </span>

    <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);font-size:13px;text-align:left">
      <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border)">
        <span style="color:var(--muted)">Last Login</span>
        <span style="font-weight:600">{{ $user->last_login_at?->diffForHumans() ?? 'Just now' }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:7px 0">
        <span style="color:var(--muted)">Member Since</span>
        <span style="font-weight:600">{{ $user->created_at->format('d M Y') }}</span>
      </div>
    </div>

    {{-- Photo upload --}}
    <form method="POST" action="{{ route('admin.profile.photo') }}" enctype="multipart/form-data" style="margin-top:16px">
      @csrf
      <label style="cursor:pointer;display:block">
        <span class="btn btn-o btn-sm" style="width:100%;justify-content:center">
          <i class="bi bi-camera"></i> Change Photo
        </span>
        <input type="file" name="photo" accept="image/*" style="display:none" onchange="this.form.submit()">
      </label>
    </form>
  </div>

  {{-- ── RIGHT: Forms ────────────────────────────────────────── --}}
  <div style="display:flex;flex-direction:column;gap:18px">

    {{-- Personal info --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Personal Information</span></div>
      <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf @method('PUT')
        <div class="card-body">
          <div class="g2" style="gap:16px">
            <div class="fg">
              <label class="fl">Full Name *</label>
              <input type="text" name="name" class="fc" value="{{ old('name', $user->name) }}" required>
              @error('name')<span class="iv">{{ $message }}</span>@enderror
            </div>
            <div class="fg">
              <label class="fl">Email Address *</label>
              <input type="email" name="email" class="fc" value="{{ old('email', $user->email) }}" required>
              @error('email')<span class="iv">{{ $message }}</span>@enderror
            </div>
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Phone Number</label>
              <input type="text" name="phone" class="fc" value="{{ old('phone', $user->phone) }}" placeholder="+266 5000 0000">
            </div>
          </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
          <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Changes</button>
        </div>
      </form>
    </div>

    {{-- Change password --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Change Password</span></div>
      <form method="POST" action="{{ route('admin.profile.password') }}">
        @csrf @method('PUT')
        <div class="card-body">
          <div class="g3" style="gap:16px">
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Current Password *</label>
              <input type="password" name="current_password" class="fc" required>
              @error('current_password')<span class="iv">{{ $message }}</span>@enderror
            </div>
            <div class="fg" style="margin-bottom:0">
              <label class="fl">New Password *</label>
              <input type="password" name="password" class="fc" placeholder="Min 8 characters" required>
            </div>
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Confirm New Password *</label>
              <input type="password" name="password_confirmation" class="fc" required>
            </div>
          </div>
        </div>
        <div style="padding:14px 22px;border-top:1px solid var(--border);text-align:right">
          <button type="submit" class="btn btn-p"><i class="bi bi-lock"></i> Update Password</button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection
