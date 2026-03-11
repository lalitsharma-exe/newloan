@extends('admin.layouts.app')
@section('title','Create User')
@section('page-title','Create User')
@section('bc','<a href="'.route('admin.users.index').'">Users</a> / Create')
@section('content')

<div style="max-width:680px">
<div class="card">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-person-plus-fill" style="color:var(--p)"></i> New User</span>
  </div>
  <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="card-body">

      {{-- Validation errors --}}
      @if($errors->any())
      <div class="alert" style="background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2);margin-bottom:20px;display:flex;align-items:flex-start;gap:9px;padding:12px 16px;border-radius:11px;font-size:13px">
        <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;flex-shrink:0"></i>
        <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      {{-- Role selector (visual) --}}
      <div class="fg">
        <label class="fl">Role *</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px" id="roleCards">
          @foreach([
            ['admin','Admin','shield-fill','#ef4444','Full system access'],
            ['loan_officer','Loan Officer','person-badge-fill','#4f46e5','Review applications'],
            ['borrower','Borrower','person-fill','#10b981','Apply for loans'],
          ] as [$val,$label,$icon,$color,$desc])
          <label style="cursor:pointer">
            <input type="radio" name="role" value="{{ $val }}" {{ old('role')===$val?'checked':'' }} style="display:none" class="role-radio">
            <div class="role-card" data-role="{{ $val }}" style="border:2px solid var(--border);border-radius:13px;padding:16px 14px;text-align:center;transition:all .2s;user-select:none">
              <div style="width:46px;height:46px;border-radius:50%;background:{{ $color }}22;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:20px;color:{{ $color }}">
                <i class="bi bi-{{ $icon }}"></i>
              </div>
              <div style="font-weight:700;font-size:13px">{{ $label }}</div>
              <div style="font-size:11px;color:var(--muted);margin-top:3px">{{ $desc }}</div>
            </div>
          </label>
          @endforeach
        </div>
        @error('role')<span class="iv">{{ $message }}</span>@enderror
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="fg">
          <label class="fl">Full Name *</label>
          <input type="text" name="name" class="fc @error('name') err @enderror" value="{{ old('name') }}" placeholder="John Doe" required>
          @error('name')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Email Address *</label>
          <input type="email" name="email" class="fc @error('email') err @enderror" value="{{ old('email') }}" placeholder="john@example.com" required>
          @error('email')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Phone Number *</label>
          <input type="text" name="phone" class="fc @error('phone') err @enderror" value="{{ old('phone') }}" placeholder="26622000000" required>
          @error('phone')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Status</label>
          <select name="is_active" class="fc">
            <option value="1" {{ old('is_active','1')==='1'?'selected':'' }}>Active</option>
            <option value="0" {{ old('is_active')==='0'?'selected':'' }}>Inactive</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Password *</label>
          <div style="position:relative">
            <input type="password" name="password" id="pw" class="fc @error('password') err @enderror" placeholder="Min 8 characters" required style="padding-right:42px">
            <button type="button" onclick="togglePw('pw','eyePw')" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px"><i class="bi bi-eye" id="eyePw"></i></button>
          </div>
          @error('password')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Confirm Password *</label>
          <div style="position:relative">
            <input type="password" name="password_confirmation" id="pw2" class="fc" placeholder="Repeat password" required style="padding-right:42px">
            <button type="button" onclick="togglePw('pw2','eyePw2')" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px"><i class="bi bi-eye" id="eyePw2"></i></button>
          </div>
        </div>
      </div>
    </div>
    <div style="padding:16px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px">
      <a href="{{ route('admin.users.index') }}" class="btn btn-o">Cancel</a>
      <button type="submit" class="btn btn-p"><i class="bi bi-check-lg"></i> Create User</button>
    </div>
  </form>
</div>
</div>

<style>
.role-radio:checked + .role-card { border-color: var(--p) !important; background: rgba(79,70,229,.04); }
.role-card:hover { border-color: #c7d2fe !important; }
</style>
<script>
// Highlight selected role card on page load
document.querySelectorAll('.role-radio').forEach(r => {
  if(r.checked) r.nextElementSibling.style.borderColor = 'var(--p)';
  r.addEventListener('change', () => {
    document.querySelectorAll('.role-card').forEach(c => c.style.cssText = '');
  });
});
function togglePw(id, eyeId) {
  const i = document.getElementById(id);
  const e = document.getElementById(eyeId);
  i.type = i.type === 'password' ? 'text' : 'password';
  e.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
@endsection