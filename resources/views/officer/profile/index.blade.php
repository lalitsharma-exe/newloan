@extends('officer.layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
@php $officer = auth('officer')->user(); @endphp

@if(session('success'))
<div class="alert a-ok" style="margin-bottom:20px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

  {{-- Left: Avatar + summary --}}
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card">
      <div class="card-body" style="text-align:center;padding:30px 20px">
        <div style="position:relative;display:inline-block;margin-bottom:16px">
          @if($officer->profile_photo)
          <img src="{{ Storage::url($officer->profile_photo) }}" alt="{{ $officer->name }}"
            style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--border)">
          @else
          <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:32px;margin:0 auto">
            {{ strtoupper(substr($officer->name,0,1)) }}
          </div>
          @endif
        </div>
        <div style="font-size:18px;font-weight:800;margin-bottom:4px">{{ $officer->name }}</div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:10px">{{ $officer->phone }}</div>
        <span class="officer-badge"><i class="bi bi-shield-check"></i> Loan Officer</span>
      </div>
    </div>

    {{-- Update photo --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Profile Photo</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('officer.profile.photo') }}" enctype="multipart/form-data">
          @csrf
          <div class="fg">
            <label class="fl">Upload New Photo</label>
            <input type="file" name="photo" class="fc" accept="image/jpg,image/jpeg,image/png,image/webp" style="padding:7px">
            <span class="ft">JPG, PNG, WebP · Max 2MB</span>
            @error('photo')<span class="iv">{{ $message }}</span>@enderror
          </div>
          <button type="submit" class="btn btn-p btn-sm" style="width:100%;justify-content:center"><i class="bi bi-cloud-upload"></i> Upload Photo</button>
        </form>
      </div>
    </div>

    {{-- Account info --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Account Info</span></div>
      <div class="card-body" style="padding:14px 18px">
        @foreach(['Role'=>'Loan Officer','Status'=>$officer->is_active?'Active':'Inactive','Last Login'=>$officer->last_login_at?->diffForHumans()??'—','Member Since'=>$officer->created_at->format('d M Y')] as $l=>$v)
        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
          <span style="color:var(--muted)">{{ $l }}</span>
          <strong style="{{ $l==='Status'?'color:'.($officer->is_active?'var(--ok)':'var(--err)').'':'' }}">{{ $v }}</strong>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Right: forms --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Update profile --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-person-fill" style="color:var(--p)"></i> Update Profile</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('officer.profile.update') }}">
          @csrf @method('PUT')
          <div class="g2" style="gap:16px">
            <div class="fg">
              <label class="fl">Full Name *</label>
              <input type="text" name="name" class="fc @error('name') err @enderror" value="{{ old('name',$officer->name) }}" required>
              @error('name')<span class="iv">{{ $message }}</span>@enderror
            </div>
            <div class="fg">
              <label class="fl">Phone Number</label>
              <input type="tel" name="phone" class="fc @error('phone') err @enderror" value="{{ old('phone',$officer->phone) }}" placeholder="+266 5000 0000">
              @error('phone')<span class="iv">{{ $message }}</span>@enderror
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Change password --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-lock-fill" style="color:var(--p)"></i> Change Password</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('officer.profile.password') }}">
          @csrf @method('PUT')
          <div style="display:flex;flex-direction:column;gap:0">
            <div class="fg">
              <label class="fl">Current Password *</label>
              <input type="password" name="current_password" class="fc @error('current_password') err @enderror" required autocomplete="current-password">
              @error('current_password')<span class="iv">{{ $message }}</span>@enderror
            </div>
            <div class="fg">
              <label class="fl">New Password *</label>
              <input type="password" name="password" class="fc @error('password') err @enderror" required autocomplete="new-password">
              <span class="ft">Minimum 8 characters</span>
              @error('password')<span class="iv">{{ $message }}</span>@enderror
            </div>
            <div class="fg">
              <label class="fl">Confirm New Password *</label>
              <input type="password" name="password_confirmation" class="fc" required autocomplete="new-password">
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-p"><i class="bi bi-lock-fill"></i> Update Password</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Performance summary --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-bar-chart" style="color:var(--p)"></i> My Performance</span></div>
      <div class="card-body">
        @php
        $officer = auth('officer')->user();
        $totalAssigned = \App\Models\LoanApplication::where('assigned_officer_id',$officer->id)->where('status','!=','draft')->count();
        $approved = \App\Models\LoanApplication::where('assigned_officer_id',$officer->id)->where('status','approved')->count();
        $declined = \App\Models\LoanApplication::where('assigned_officer_id',$officer->id)->where('status','declined')->count();
        $pending  = \App\Models\LoanApplication::where('assigned_officer_id',$officer->id)->whereIn('status',['submitted','under_review','info_requested'])->count();
        $clients  = \App\Models\User::where('assigned_officer_id',$officer->id)->where('role','borrower')->count();
        @endphp
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;text-align:center">
          @foreach([['Total Assigned',$totalAssigned,'#4f46e5'],['Pending',$pending,'#f59e0b'],['Approved',$approved,'#10b981'],['Declined',$declined,'#ef4444'],['My Clients',$clients,'var(--p)']] as [$l,$v,$c])
          <div style="background:#f8fafc;border-radius:11px;padding:14px;border:1px solid var(--border)">
            <div style="font-size:22px;font-weight:800;color:{{ $c }}">{{ $v }}</div>
            <div style="font-size:10.5px;color:var(--muted);font-weight:600;margin-top:3px">{{ $l }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
