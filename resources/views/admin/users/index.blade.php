@extends('admin.layouts.app')
@section('title','Users')
@section('page-title','User Management')
@section('bc') <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Users @endsection
@section('content')

{{-- Flash messages --}}
@if(session('success'))<div class="alert a-ok mb-4"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert" style="background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)" class="mb-4"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

{{-- Stats row --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:26px">
  @foreach([
    ['Total Users',   $stats['total'],    'people-fill',      'p'],
    ['Admins',        $stats['admins'],   'shield-fill',      'e'],
    ['Loan Officers', $stats['officers'], 'person-badge-fill','i'],
    ['Borrowers',     $stats['borrowers'],'person-fill',      'ok'],
    ['Active',        $stats['active'],   'check-circle-fill','ok'],
  ] as [$label,$val,$icon,$cls])
  <div class="sc">
    <div class="si {{ $cls }}"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div class="sv">{{ number_format($val) }}</div><div class="sl">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.users.index') }}" style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:22px">
  <div class="fg" style="margin-bottom:0;flex:2;min-width:180px">
    <label class="fl">Search</label>
    <div style="position:relative">
      <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
      <input type="text" name="search" class="fc" style="padding-left:32px" placeholder="Name, email, phone…" value="{{ $filters['search']??'' }}">
    </div>
  </div>
  <div class="fg" style="margin-bottom:0;min-width:150px">
    <label class="fl">Role</label>
    <select name="role" class="fc">
      <option value="">All Roles</option>
      <option value="admin"        {{ ($filters['role']??'')==='admin'?'selected':'' }}>Admin</option>
      <option value="loan_officer" {{ ($filters['role']??'')==='loan_officer'?'selected':'' }}>Loan Officer</option>
      <option value="borrower"     {{ ($filters['role']??'')==='borrower'?'selected':'' }}>Borrower</option>
    </select>
  </div>
  <div class="fg" style="margin-bottom:0;min-width:130px">
    <label class="fl">Status</label>
    <select name="status" class="fc">
      <option value="">All</option>
      <option value="active"   {{ ($filters['status']??'')==='active'?'selected':'' }}>Active</option>
      <option value="inactive" {{ ($filters['status']??'')==='inactive'?'selected':'' }}>Inactive</option>
    </select>
  </div>
  <div style="display:flex;gap:8px">
    <button type="submit" class="btn btn-p"><i class="bi bi-funnel"></i> Filter</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-o">Clear</a>
  </div>
</form>

{{-- Flash messages --}}
@if(session('import_success'))
<div class="alert a-ok mb-4" style="flex-direction:column;align-items:flex-start;gap:6px">
  <div style="display:flex;align-items:center;gap:9px"><i class="bi bi-check-circle-fill"></i><strong>{{ session('import_success') }}</strong></div>
  @if(session('import_errors'))
  <div style="margin-top:6px;width:100%">
    <div style="font-size:12px;font-weight:700;color:#065f46;margin-bottom:4px">Row issues (skipped):</div>
    <ul style="margin:0;padding-left:18px;font-size:12px;color:#065f46">
      @foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach
    </ul>
    {{ session()->forget('import_errors') }}
  </div>
  @endif
</div>
@endif

{{-- Bulk Import Excel --}}
<div class="card" style="margin-bottom:20px;border:2px dashed rgba(43,75,173,.2)">
  <div class="card-hdr" style="background:linear-gradient(135deg,rgba(43,75,173,.04),rgba(43,75,173,.08));border-bottom:1px solid rgba(43,75,173,.12)">
    <span class="card-title"><i class="bi bi-file-earmark-spreadsheet-fill" style="color:#4f46e5"></i> &nbsp;Bulk Import Borrowers</span>
    <div style="display:flex;gap:8px">
      <a href="{{ route('admin.users.import-template') }}" class="btn btn-sm" style="background:rgba(79,70,229,.1);color:#4f46e5;border:1px solid rgba(79,70,229,.25);font-size:12px">
        <i class="bi bi-download"></i> Download Template
      </a>
      <a href="{{ route('admin.users.export') }}" class="btn btn-sm btn-o">
        <i class="bi bi-file-earmark-excel"></i> Export Borrowers (.xlsx)
      </a>
    </div>
  </div>
  <div style="padding:20px">

    {{-- Instructions --}}
    <div style="background:rgba(79,70,229,.05);border:1px solid rgba(79,70,229,.15);border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:12.5px;color:var(--navy)">
      <div style="font-weight:700;margin-bottom:8px"><i class="bi bi-info-circle" style="color:#4f46e5"></i> &nbsp;How to import</div>
      <ol style="margin:0;padding-left:18px;line-height:1.9">
        <li>Download the <strong>Excel template</strong> above and fill in your borrowers.</li>
        <li>Required columns: <code>name</code>, <code>phone</code></li>
        <li>Optional columns: <code>email</code>, <code>national_id</code>, <code>maiden_name</code>, <code>officer_id</code></li>
        <li><strong>Password</strong> is automatically set to the borrower's 8-digit phone number (e.g. <code>53797734</code>).</li>
        <li>Duplicate phones, emails and national IDs are automatically skipped.</li>
        <li>Accepted formats: <strong>.xlsx, .xls, .csv</strong> — max 10 MB</li>
      </ol>
    </div>

    {{-- Upload Form --}}
    <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" id="importForm">
      @csrf
      <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div class="fg" style="margin-bottom:0;flex:1;min-width:260px">
          <label class="fl">Select Excel / CSV file</label>
          <div style="position:relative">
            <input type="file" name="file" class="fc" id="importFile" accept=".xlsx,.xls,.csv,.txt" required
              style="padding:9px 12px;cursor:pointer"
              onchange="document.getElementById('importFileName').textContent = this.files[0]?.name ?? ''">
          </div>
          <div id="importFileName" style="font-size:11px;color:var(--muted);margin-top:4px"></div>
          @error('file')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-p" id="importBtn" style="gap:8px" onclick="this.disabled=true;this.innerHTML='<i class=\'bi bi-hourglass-split\'></i> Importing…';this.closest(\'form\').submit()">
          <i class="bi bi-people-fill"></i> Import Borrowers
        </button>
      </div>
    </form>

  </div>
</div>

{{-- Table --}}
<div class="card">
  <div class="card-hdr">
    <span class="card-title">Users <span style="color:var(--muted);font-weight:400">({{ $users->total() }})</span></span>
    <a href="{{ route('admin.users.create') }}" class="btn btn-p btn-sm"><i class="bi bi-plus-lg"></i> Add User</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>User</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Last Login</th><th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($users as $user)
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:11px">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,{{ $user->role==='admin'?'#ef4444,#dc2626':($user->role==='loan_officer'?'#4f46e5,#6366f1':'#10b981,#059669') }});display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0">
              {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <div>
              <div style="font-weight:600;font-size:13.5px">{{ $user->name }}</div>
              <div style="font-size:12px;color:var(--muted)">{{ $user->email }}</div>
            </div>
          </div>
        </td>
        <td style="color:var(--muted);font-size:13px">{{ $user->phone ?? '—' }}</td>
        <td>
          @if($user->role==='admin')
            <span class="badge be"><i class="bi bi-shield-fill" style="font-size:10px"></i> Admin</span>
          @elseif($user->role==='loan_officer')
            <span class="badge bi"><i class="bi bi-person-badge-fill" style="font-size:10px"></i> Loan Officer</span>
          @else
            <span class="badge bs"><i class="bi bi-person-fill" style="font-size:10px"></i> Borrower</span>
          @endif
          @if($user->adminRole)
            <div style="font-size:11px;color:var(--muted);margin-top:2px;font-weight:600">{{ $user->adminRole->name }}</div>
          @endif
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:7px">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $user->is_active?'var(--ok)':'#cbd5e1' }}"></div>
            <span style="font-size:13px;color:{{ $user->is_active?'var(--ok)':'var(--muted)' }};font-weight:600">{{ $user->is_active?'Active':'Inactive' }}</span>
          </div>
        </td>
        <td style="font-size:12px;color:var(--muted)">{{ $user->created_at->format('d M Y') }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
        <td>
          <div style="display:flex;justify-content:flex-end;gap:6px">
            <a href="{{ route('admin.users.show',$user) }}" class="btn btn-xs btn-o" title="View"><i class="bi bi-eye"></i></a>
            <a href="{{ route('admin.users.edit',$user) }}" class="btn btn-xs btn-o" title="Edit"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="{{ route('admin.users.toggle-status',$user) }}" style="display:inline">
              @csrf
              <button class="btn btn-xs {{ $user->is_active?'btn-w':'btn-ok' }}" title="{{ $user->is_active?'Disable':'Enable' }}">
                <i class="bi bi-{{ $user->is_active?'lock':'unlock' }}"></i>
              </button>
            </form>
            @if($user->id !== auth('admin')->id())
            <button onclick="confirmDelete({{ $user->id }},'{{ addslashes($user->name) }}')" class="btn btn-xs btn-e" title="Delete"><i class="bi bi-trash"></i></button>
            <form id="del-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy',$user) }}" style="display:none">@csrf @method('DELETE')</form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="7">
        <div style="text-align:center;padding:48px 0;color:var(--muted)">
          <i class="bi bi-people" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
          <div style="font-weight:600">No users found</div>
          <div style="font-size:12px;margin-top:4px">Try adjusting your filters</div>
        </div>
      </td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if($users->hasPages())
  <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $users->withQueryString()->links() }}</div>
  @endif
</div>

{{-- Delete confirmation modal --}}
<div id="delModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:20px;padding:32px;max-width:420px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:modalIn .2s ease">
    <div style="width:60px;height:60px;border-radius:50%;background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:26px;color:var(--err)">
      <i class="bi bi-trash"></i>
    </div>
    <h3 style="text-align:center;font-size:18px;margin-bottom:8px">Delete User?</h3>
    <p style="text-align:center;color:var(--muted);font-size:14px;margin-bottom:24px">
      You're about to delete <strong id="delName"></strong>. This action cannot be undone.
    </p>
    <div style="display:flex;gap:10px">
      <button onclick="document.getElementById('delModal').style.display='none'" class="btn btn-o" style="flex:1">Cancel</button>
      <button onclick="submitDelete()" class="btn btn-e" style="flex:1"><i class="bi bi-trash"></i> Delete</button>
    </div>
  </div>
</div>

<style>
@keyframes modalIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px}
</style>
<script>
let deleteId = null;
function confirmDelete(id, name) {
  deleteId = id;
  document.getElementById('delName').textContent = name;
  document.getElementById('delModal').style.display = 'flex';
}
function submitDelete() {
  if (deleteId) document.getElementById('del-' + deleteId).submit();
}
document.getElementById('delModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>
@endsection
