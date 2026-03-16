@extends('admin.layouts.app')
@section('title','Edit — '.$user->name)
@section('page-title','Edit User')
@section('bc','<a href="'.route('admin.users.index').'">Users</a> / <a href="'.route('admin.users.show',$user).'">'.$user->name.'</a> / Edit')
@section('content')

<div style="max-width:760px">
<div class="card">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-pencil-fill" style="color:var(--p)"></i> Edit: {{ $user->name }}</span>
    <span class="badge {{ $user->is_active ? 'bok' : 'bs' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
  </div>
  <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')
    <div class="card-body">

      @if($errors->any())
      <div class="alert a-e" style="margin-bottom:20px">
        <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0"></i>
        <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      {{-- Role selector --}}
      <div class="fg">
        <label class="fl">Role *</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
          @foreach([
            ['admin',        'Admin',        'shield-fill',       '#ef4444', 'Full system access'],
            ['loan_officer', 'Loan Officer', 'person-badge-fill', '#4f46e5', 'Review applications'],
            ['borrower',     'Borrower',     'person-fill',       '#10b981', 'Apply for loans'],
          ] as [$val,$label,$icon,$color,$desc])
          <label style="cursor:pointer">
            <input type="radio" name="role" value="{{ $val }}" {{ old('role',$user->role)===$val?'checked':'' }} style="display:none" class="role-radio" onchange="onRoleChange('{{ $val }}')">
            <div class="role-card" data-role="{{ $val }}" style="border:2px solid {{ old('role',$user->role)===$val ? 'var(--p)' : 'var(--border)' }};border-radius:13px;padding:16px 14px;text-align:center;transition:all .2s;user-select:none;{{ old('role',$user->role)===$val ? 'background:rgba(26,92,46,.06)' : '' }}">
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

        {{-- Name --}}
        <div class="fg">
          <label class="fl">Full Name *</label>
          <input type="text" name="name" class="fc @error('name') err @enderror" value="{{ old('name', $user->name) }}" required>
          @error('name')<span class="iv">{{ $message }}</span>@enderror
        </div>

        {{-- Phone --}}
        <div class="fg">
          <label class="fl">Phone Number * <span style="color:var(--muted);font-weight:400;font-size:11px">(+266XXXXXXXX)</span></label>
          <div style="position:relative">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;font-weight:600">+266</span>
            <input type="tel" name="phone" id="phoneInput"
              class="fc @error('phone') err @enderror"
              value="{{ old('phone', $user->phone) }}"
              style="padding-left:52px"
              required
              oninput="formatPhonePreview(this)">
          </div>
          <div class="ft" id="phonePreview" style="color:var(--p);font-weight:600"></div>
          @error('phone')<span class="iv">{{ $message }}</span>@enderror
        </div>

        {{-- Email (optional) --}}
        <div class="fg">
          <label class="fl">Email Address <span style="color:var(--muted);font-weight:400;font-size:11px">(optional)</span></label>
          <input type="email" name="email" class="fc @error('email') err @enderror" value="{{ old('email', $user->email) }}" placeholder="Leave blank if not available">
          @error('email')<span class="iv">{{ $message }}</span>@enderror
        </div>

        {{-- National ID (borrowers) --}}
        <div class="fg" id="nationalIdField" style="display:{{ old('role',$user->role) === 'borrower' ? '' : 'none' }}">
          <label class="fl">ID Number <span style="color:var(--muted);font-weight:400;font-size:11px">(National ID)</span></label>
          <input type="text" name="national_id" class="fc @error('national_id') err @enderror" value="{{ old('national_id', $user->national_id) }}" placeholder="e.g. 9001015009087">
          @error('national_id')<span class="iv">{{ $message }}</span>@enderror
        </div>

        {{-- Assigned Officer (borrowers) --}}
        <div class="fg" id="officerField" style="display:{{ old('role',$user->role) === 'borrower' ? '' : 'none' }}">
          <label class="fl">Assigned Loan Officer</label>
          <select name="assigned_officer_id" class="fc @error('assigned_officer_id') err @enderror">
            <option value="">— Select Loan Officer —</option>
            @foreach($officers as $officer)
            <option value="{{ $officer->id }}" {{ old('assigned_officer_id', $user->assigned_officer_id) == $officer->id ? 'selected' : '' }}>
              {{ $officer->name }}
            </option>
            @endforeach
          </select>
          @error('assigned_officer_id')<span class="iv">{{ $message }}</span>@enderror
        </div>

        {{-- Status --}}
        <div class="fg">
          <label class="fl">Status</label>
          <select name="is_active" class="fc">
            <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive / Suspended</option>
          </select>
        </div>

        {{-- Password (optional on edit) --}}
        <div class="fg">
          <label class="fl">New Password <span style="color:var(--muted);font-weight:400;font-size:11px">(leave blank to keep current)</span></label>
          <div style="position:relative">
            <input type="password" name="password" id="pw" class="fc @error('password') err @enderror" placeholder="Min 8 characters" style="padding-right:42px">
            <button type="button" onclick="togglePw('pw','eyePw')" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px"><i class="bi bi-eye" id="eyePw"></i></button>
          </div>
          @error('password')<span class="iv">{{ $message }}</span>@enderror
        </div>

        {{-- Confirm Password --}}
        <div class="fg">
          <label class="fl">Confirm New Password</label>
          <div style="position:relative">
            <input type="password" name="password_confirmation" id="pw2" class="fc" placeholder="Repeat new password" style="padding-right:42px">
            <button type="button" onclick="togglePw('pw2','eyePw2')" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px"><i class="bi bi-eye" id="eyePw2"></i></button>
          </div>
        </div>

      </div>

    </div>
    <div style="padding:16px 22px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <a href="{{ route('admin.users.show', $user) }}" class="btn btn-o">Cancel</a>
      <button type="submit" class="btn btn-p"><i class="bi bi-check-lg"></i> Save Changes</button>
    </div>
  </form>
</div>
</div>

<style>
.role-radio:checked + .role-card { border-color: var(--p) !important; background: rgba(26,92,46,.06); }
.role-card:hover { border-color: #a7f3d0 !important; }
</style>

<script>
function togglePw(id, iconId) {
  const f = document.getElementById(id);
  const i = document.getElementById(iconId);
  if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
  else { f.type = 'password'; i.className = 'bi bi-eye'; }
}

function onRoleChange(role) {
  document.querySelectorAll('.role-card').forEach(c => {
    c.style.borderColor = 'var(--border)';
    c.style.background  = '';
  });
  const active = document.querySelector(`.role-card[data-role="${role}"]`);
  if (active) { active.style.borderColor = 'var(--p)'; active.style.background = 'rgba(26,92,46,.06)'; }
  const isBorrower = role === 'borrower';
  document.getElementById('officerField').style.display    = isBorrower ? '' : 'none';
  document.getElementById('nationalIdField').style.display = isBorrower ? '' : 'none';
}

function formatPhonePreview(el) {
  const digits  = el.value.replace(/[^0-9]/g, '');
  const preview = document.getElementById('phonePreview');
  if (digits.length === 8)      preview.textContent = 'Will be saved as: +266' + digits;
  else if (digits.length > 8)   preview.textContent = '';
  else if (digits.length > 0)   preview.textContent = 'Enter remaining ' + (8 - digits.length) + ' digit(s)';
  else                          preview.textContent = '';
}

// Init phone preview with existing value
formatPhonePreview(document.getElementById('phoneInput'));
</script>
@endsection
