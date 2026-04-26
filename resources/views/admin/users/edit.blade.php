@extends('admin.layouts.app')
@section('title','Edit — '.$user->name)
@section('page-title','Edit User')
@section('bc')
<a href="{{ route('admin.users.index') }}">Users</a> / <a href="{{ route('admin.users.show',$user) }}">{{ $user->name }}</a> / Edit
@endsection

@section('content')
<div style="max-width:900px;margin:0 auto">
  <form method="POST" action="{{ route('admin.users.update', $user) }}" id="userForm">
    @csrf
    @method('PUT')

    {{-- ── ROLE SELECTION — THE PRIMARY TRIGGER ── --}}
    <div style="margin-bottom:26px">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:12px">User Role</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px">
        @foreach([
          ['admin', 'Administrator', 'shield-lock-fill', '#ef4444', 'Manage system, staff and settings'],
          ['loan_officer', 'Loan Officer', 'person-badge-fill', '#4f46e5', 'Review applications and manage clients'],
          ['borrower', 'Borrower', 'person-fill', '#10b981', 'Apply for loans and manage repayments']
        ] as [$val, $label, $icon, $color, $desc])
        <label style="cursor:pointer;margin:0">
          <input type="radio" name="role" value="{{ $val }}" {{ old('role',$user->role)===$val?'checked':'' }} class="role-radio" style="display:none" onchange="onRoleChange('{{ $val }}')">
          <div class="role-card" data-role="{{ $val }}" style="background:#fff;border:2px solid var(--border);border-radius:18px;padding:22px 18px;text-align:center;transition:all .3s ease;height:100%;display:flex;flex-direction:column;align-items:center">
            <div class="role-icon" style="width:54px;height:54px;border-radius:50%;background:{{ $color }}15;color:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:14px;transition:all .3s">
              <i class="bi bi-{{ $icon }}"></i>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--dark);margin-bottom:6px">{{ $label }}</div>
            <div style="font-size:11.5px;color:var(--muted);line-height:1.4">{{ $desc }}</div>
            <div class="role-check" style="margin-top:auto;padding-top:14px;color:var(--p);font-size:18px;opacity:0;transition:all .2s">
              <i class="bi bi-check-circle-fill"></i>
            </div>
          </div>
        </label>
        @endforeach
      </div>
      @error('role')<div class="iv" style="text-align:center;margin-top:10px">{{ $message }}</div>@enderror
    </div>

    {{-- ── FORM CONTENT ── --}}
    <div id="formContent" style="animation:fadeIn .4s ease">
      
      <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start">
        
        {{-- Left Column: Main Data --}}
        <div>
          {{-- SECTION: BASIC INFO --}}
          <div class="card" style="margin-bottom:20px">
            <div class="card-hdr">
              <span class="card-title"><i class="bi bi-person-fill" style="color:var(--p);margin-right:6px"></i>Basic Account Information</span>
            </div>
            <div class="card-body">
              <div class="g2">
                <div class="fg">
                  <label class="fl">Full Name *</label>
                  <input type="text" name="name" class="fc @error('name') err @enderror" value="{{ old('name',$user->name) }}" required>
                  @error('name')<span class="iv">{{ $message }}</span>@enderror
                </div>
                <div class="fg">
                  <label class="fl">Email Address</label>
                  <input type="email" name="email" class="fc @error('email') err @enderror" value="{{ old('email',$user->email) }}" placeholder="email@example.com">
                  @error('email')<span class="iv">{{ $message }}</span>@enderror
                </div>
              </div>

              <div class="g2">
                <div class="fg">
                  <label class="fl">Phone Number * <span style="color:var(--muted);font-weight:400;font-size:11px">(+266)</span></label>
                  <div style="position:relative">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-weight:700;font-size:13px">+266</span>
                    <input type="tel" name="phone" id="phoneInput" class="fc @error('phone') err @enderror" value="{{ old('phone',$user->phone) }}" placeholder="58123456" style="padding-left:54px" required oninput="formatPhonePreview(this)">
                  </div>
                  <div id="phonePreview" style="font-size:11px;color:var(--p);font-weight:700;margin-top:4px"></div>
                  @error('phone')<span class="iv">{{ $message }}</span>@enderror
                </div>
                <div class="fg">
                  <label class="fl">Account Status</label>
                  <select name="is_active" class="fc">
                    <option value="1" {{ old('is_active',$user->is_active?'1':'0')==='1'?'selected':'' }}>Active (Can login)</option>
                    <option value="0" {{ old('is_active',$user->is_active?'1':'0')==='0'?'selected':'' }}>Inactive (Blocked)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          {{-- SECTION: ROLE SPECIFIC --}}
          <div id="roleSpecificSection" class="card" style="margin-bottom:20px;display:none">
            <div class="card-hdr" id="roleSpecificHdr">
              <span class="card-title"><i class="bi bi-stars" style="color:var(--warn);margin-right:6px"></i>Role Specific Details</span>
            </div>
            <div class="card-body">
              
              {{-- Borrower Fields --}}
              <div id="borrowerFields" style="display:none">
                <div class="g2">
                  <div class="fg">
                    <label class="fl">National ID Number *</label>
                    <input type="text" name="national_id" class="fc @error('national_id') err @enderror" value="{{ old('national_id',$user->national_id) }}" placeholder="Enter ID number">
                    @error('national_id')<span class="iv">{{ $message }}</span>@enderror
                  </div>
                  <div class="fg">
                    <label class="fl">Maiden Name</label>
                    <input type="text" name="maiden_name" class="fc" value="{{ old('maiden_name',$user->maiden_name) }}" placeholder="Original surname">
                  </div>
                </div>
                <div class="fg">
                  <label class="fl">Assigned Loan Officer *</label>
                  <select name="assigned_officer_id" class="fc @error('assigned_officer_id') err @enderror">
                    <option value="">— Select an Officer —</option>
                    @foreach($officers as $o)
                    <option value="{{ $o->id }}" {{ old('assigned_officer_id',$user->assigned_officer_id)==$o->id?'selected':'' }}>{{ $o->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              {{-- Admin Fields --}}
              <div id="adminFields" style="display:none">
                <div class="fg">
                  <label class="fl">Admin Access Role *</label>
                  <select name="admin_role_id" class="fc @error('admin_role_id') err @enderror">
                    <option value="">— Select Permissions Level —</option>
                    @foreach($adminRoles as $ar)
                    <option value="{{ $ar->id }}" {{ old('admin_role_id',$user->admin_role_id)==$ar->id?'selected':'' }}>{{ $ar->name }} {{ $ar->is_super_admin?'(Super Admin)':'' }}</option>
                    @endforeach
                  </select>
                  @error('admin_role_id')<span class="iv">{{ $message }}</span>@enderror
                </div>
              </div>

              {{-- Loan Officer Fields --}}
              <div id="officerFields" style="display:none">
                <div class="alert a-i" style="margin:0">
                  <i class="bi bi-info-circle-fill"></i>
                  <div>Loan Officers have their own portal and do not require additional configuration.</div>
                </div>
              </div>

            </div>
          </div>
        </div>

        {{-- Right Column: Security/Photo --}}
        <div>
          <div class="card" style="margin-bottom:20px;background:#fdfdfd">
            <div class="card-hdr">
              <span class="card-title"><i class="bi bi-shield-lock-fill" style="color:var(--err);margin-right:6px"></i>Security</span>
            </div>
            <div class="card-body">
              <div class="fg">
                <label class="fl">New Password <span style="color:var(--muted);font-weight:400;font-size:11px">(leave blank to keep current)</span></label>
                <div style="position:relative">
                  <input type="password" name="password" id="pw" class="fc @error('password') err @enderror" placeholder="Min 8 characters" style="padding-right:42px">
                  <button type="button" onclick="togglePw('pw','e1')" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px"><i class="bi bi-eye" id="e1"></i></button>
                </div>
                @error('password')<span class="iv">{{ $message }}</span>@enderror
              </div>
              <div class="fg">
                <label class="fl">Confirm Password</label>
                <div style="position:relative">
                  <input type="password" name="password_confirmation" id="pw2" class="fc" placeholder="Repeat password" style="padding-right:42px">
                  <button type="button" onclick="togglePw('pw2','e2')" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px"><i class="bi bi-eye" id="e2"></i></button>
                </div>
              </div>
            </div>
          </div>

          <div style="background:var(--p);color:#fff;border-radius:18px;padding:24px;text-align:center;box-shadow:0 10px 25px rgba(30,51,112,0.2)">
            <button type="submit" class="btn btn-ok" style="width:100%;justify-content:center;padding:12px;font-size:15px">
              <i class="bi bi-check-lg"></i> Save Changes
            </button>
            <a href="{{ route('admin.users.show', $user) }}" style="display:block;margin-top:14px;color:#fff;text-decoration:none;font-size:12.5px;font-weight:600;opacity:.7" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='.7'">Discard Changes</a>
          </div>
        </div>

      </div>
    </div>
  </form>
</div>

<style>
.role-radio:checked + .role-card {
  border-color: var(--p) !important;
  background: rgba(30,51,112,.03) !important;
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(0,0,0,.06);
}
.role-radio:checked + .role-card .role-icon {
  background: var(--p) !important;
  color: #fff !important;
  transform: scale(1.1);
}
.role-radio:checked + .role-card .role-check {
  opacity: 1 !important;
}
.role-card:hover:not(.role-radio:checked + .role-card) {
  border-color: #b0bdd0 !important;
  transform: translateY(-2px);
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
function onRoleChange(role) {
  const roleSection = document.getElementById('roleSpecificSection');
  const roleHdr     = document.getElementById('roleSpecificHdr');
  const bFields     = document.getElementById('borrowerFields');
  const aFields     = document.getElementById('adminFields');
  const oFields     = document.getElementById('officerFields');
  
  roleSection.style.display = 'block';
  bFields.style.display = 'none';
  aFields.style.display = 'none';
  oFields.style.display = 'none';
  
  document.querySelector('[name=assigned_officer_id]').required = false;
  document.querySelector('[name=national_id]').required = false;
  document.querySelector('[name=admin_role_id]').required = false;

  if (role === 'borrower') {
    bFields.style.display = 'block';
    roleHdr.innerHTML = '<span class="card-title"><i class="bi bi-person-fill" style="color:#10b981;margin-right:6px"></i>Borrower Specific Details</span>';
    document.querySelector('[name=assigned_officer_id]').required = true;
    document.querySelector('[name=national_id]').required = true;
  } else if (role === 'admin') {
    aFields.style.display = 'block';
    roleHdr.innerHTML = '<span class="card-title"><i class="bi bi-shield-lock-fill" style="color:#ef4444;margin-right:6px"></i>Administrative Access</span>';
    document.querySelector('[name=admin_role_id]').required = true;
  } else if (role === 'loan_officer') {
    oFields.style.display = 'block';
    roleHdr.innerHTML = '<span class="card-title"><i class="bi bi-person-badge-fill" style="color:#4f46e5;margin-right:6px"></i>Officer Profile</span>';
  }
}

function formatPhonePreview(el) {
  const digits = el.value.replace(/[^0-9]/g, '');
  const preview = document.getElementById('phonePreview');
  if (digits.length === 8) {
    preview.textContent = '✓ Saved as: +266 ' + digits;
    preview.style.color = 'var(--ok)';
  } else if (digits.length > 8) {
    preview.textContent = 'Maximum 8 digits after prefix';
    preview.style.color = 'var(--err)';
  } else {
    preview.textContent = digits.length > 0 ? 'Need ' + (8 - digits.length) + ' more digit(s)' : '';
    preview.style.color = 'var(--p)';
  }
}

function togglePw(id, iconId) {
  const f = document.getElementById(id);
  const i = document.getElementById(iconId);
  if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
  else                       { f.type = 'password'; i.className = 'bi bi-eye'; }
}

window.addEventListener('load', () => {
  const checked = document.querySelector('.role-radio:checked');
  if (checked) onRoleChange(checked.value);
  formatPhonePreview(document.getElementById('phoneInput'));
});
</script>
@endsection
