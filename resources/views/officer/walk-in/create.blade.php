@extends('officer.layouts.app')
@section('title','Register Walk-in Client')
@section('page-title','Register Walk-in Client')
@section('bc','<a href="'.route('officer.dashboard').'">Dashboard</a> / Register Walk-in Client')

@section('content')
<div class="alert a-i" style="margin-bottom:20px">
  <i class="bi bi-info-circle-fill"></i>
  <div>Register a walk-in client below. The system will automatically create their borrower account and allow you to start the loan application on their behalf.</div>
</div>

<div style="max-width:700px">
  <div class="card">
    <div class="card-hdr">
      <span class="card-title"><i class="bi bi-person-plus-fill" style="color:var(--p)"></i> New Walk-in Client</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('officer.walk-in.store') }}">
        @csrf

        @if($errors->any())
        <div class="alert a-e" style="margin-bottom:18px">
          <i class="bi bi-exclamation-circle-fill"></i>
          <div>
            @foreach($errors->all() as $e)
            <div>{{ $e }}</div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- Identity --}}
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)">
          Identity
        </div>

        <div class="g2">
          <div class="fg">
            <label class="fl">Full Name *</label>
            <input type="text" name="name" class="fc @error('name') err @enderror" value="{{ old('name') }}" placeholder="First and Last name" required>
            @error('name')<span class="iv">{{ $message }}</span>@enderror
          </div>
          <div class="fg">
            <label class="fl">National ID *</label>
            <input type="text" name="national_id" class="fc @error('national_id') err @enderror" value="{{ old('national_id') }}" placeholder="e.g. 900115-0001-00" required>
            @error('national_id')<span class="iv">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="g2">
          <div class="fg">
            <label class="fl">Date of Birth</label>
            <input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth') }}" max="{{ now()->subYears(18)->format('Y-m-d') }}">
            <span class="ft">Minimum age: 18</span>
          </div>
          <div class="fg">
            <label class="fl">Gender</label>
            <select name="gender" class="fc">
              <option value="">— Select —</option>
              <option value="male" {{ old('gender')==='male'?'selected':'' }}>Male</option>
              <option value="female" {{ old('gender')==='female'?'selected':'' }}>Female</option>
              <option value="other" {{ old('gender')==='other'?'selected':'' }}>Other</option>
            </select>
          </div>
        </div>

        {{-- Contact --}}
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:14px;margin-top:4px;padding-bottom:8px;border-bottom:1px solid var(--border)">
          Contact
        </div>

        <div class="g2">
          <div class="fg">
            <label class="fl">Cell / Mobile Number *</label>
            <input type="tel" name="phone" class="fc @error('phone') err @enderror" value="{{ old('phone') }}" placeholder="+266 5000 0000" required>
            @error('phone')<span class="iv">{{ $message }}</span>@enderror
          </div>
          <div class="fg">
            <label class="fl">Email Address</label>
            <input type="email" name="email" class="fc" value="{{ old('email') }}" placeholder="Optional">
          </div>
        </div>

        <div class="fg">
          <label class="fl">Address</label>
          <textarea name="address" class="fc" rows="2" placeholder="Street, Area/Zone, City">{{ old('address') }}</textarea>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px">
          <a href="{{ route('officer.dashboard') }}" class="btn btn-o">Cancel</a>
          <button type="submit" class="btn btn-p"><i class="bi bi-person-plus-fill"></i> Register Client</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
