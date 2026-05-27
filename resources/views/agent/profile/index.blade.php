@extends('agent.layouts.app')
@section('page-title', 'My Profile')
@section('content')

<div class="g2" style="gap:22px">
  {{-- Profile Info --}}
  <div class="card">
    <div class="card-hdr"><div class="card-title">Profile Information</div></div>
    <div class="card-body">
      <form method="POST" action="{{ route('agent.profile.update') }}">
        @csrf @method('PUT')
        <div class="fg">
          <label class="fl">Full Name</label>
          <input type="text" name="name" class="fc" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="fg">
          <label class="fl">Phone</label>
          <input type="text" name="phone" class="fc" value="{{ old('phone', $user->phone) }}">
        </div>
        <div class="fg">
          <label class="fl">Email</label>
          <input type="email" class="fc" value="{{ $user->email }}" disabled style="background:#f1f5f9">
        </div>
        @if($profile)
        <div class="fg">
          <label class="fl">Agent ID</label>
          <input type="text" class="fc" value="{{ $profile->agent_id }}" disabled style="background:#f1f5f9;font-weight:700;color:#0f766e">
        </div>
        <div class="fg">
          <label class="fl">Agent Type</label>
          <input type="text" class="fc" value="{{ ucfirst($profile->agent_type) }}" disabled style="background:#f1f5f9">
        </div>
        @if($profile->shop_name)
        <div class="fg">
          <label class="fl">Shop / Business</label>
          <input type="text" class="fc" value="{{ $profile->shop_name }}" disabled style="background:#f1f5f9">
        </div>
        @endif
        @endif
        <button type="submit" class="btn btn-p">Save Changes</button>
      </form>
    </div>
  </div>

  {{-- Change Password --}}
  <div class="card">
    <div class="card-hdr"><div class="card-title">Change Password</div></div>
    <div class="card-body">
      <form method="POST" action="{{ route('agent.profile.password') }}">
        @csrf @method('PUT')
        <div class="fg">
          <label class="fl">Current Password</label>
          <input type="password" name="current_password" class="fc" required>
          @error('current_password')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">New Password</label>
          <input type="password" name="password" class="fc" required minlength="8">
        </div>
        <div class="fg">
          <label class="fl">Confirm New Password</label>
          <input type="password" name="password_confirmation" class="fc" required>
        </div>
        <button type="submit" class="btn btn-p">Update Password</button>
      </form>
    </div>
  </div>
</div>
@endsection
