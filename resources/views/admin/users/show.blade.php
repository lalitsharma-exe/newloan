@extends('admin.layouts.app')
@section('title','User Profile')
@section('page-title','User Profile')
@section('bc','<a href="'.route('admin.users.index').'">Users</a> / Profile')
@section('content')

@if(session('success'))<div class="alert a-ok" style="padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:20px"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif

<div style="display:grid;grid-template-columns:300px 1fr;gap:22px;align-items:start">

  {{-- LEFT: Profile card --}}
  <div>
    <div class="card" style="text-align:center;padding:30px 22px">
      <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,{{ $user->role==='admin'?'#ef4444,#dc2626':($user->role==='loan_officer'?'#4f46e5,#6366f1':'#10b981,#059669') }});display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:30px;margin:0 auto 16px">{{ strtoupper(substr($user->name,0,1)) }}</div>
      <div style="font-size:17px;font-weight:700">{{ $user->name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:4px">{{ $user->email }}</div>
      <div style="margin:14px 0">
        @if($user->role==='admin')<span class="badge be"><i class="bi bi-shield-fill" style="font-size:10px"></i> Admin</span>
        @elseif($user->role==='loan_officer')<span class="badge bi"><i class="bi bi-person-badge-fill" style="font-size:10px"></i> Loan Officer</span>
        @else<span class="badge bs"><i class="bi bi-person-fill" style="font-size:10px"></i> Borrower</span>@endif
        &nbsp;
        <span class="badge {{ $user->is_active?'bok':'bs' }}">{{ $user->is_active?'Active':'Inactive' }}</span>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:16px;text-align:left">
        @foreach(['Phone'=>$user->phone??'—','Joined'=>$user->created_at->format('d M Y'),'Last Login'=>$user->last_login_at?->diffForHumans()??'Never'] as $l=>$v)
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
          <span style="color:var(--muted)">{{ $l }}</span><span style="font-weight:600">{{ $v }}</span>
        </div>
        @endforeach
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:18px">
        <a href="{{ route('admin.users.edit',$user) }}" class="btn btn-p" style="justify-content:center"><i class="bi bi-pencil"></i> Edit Profile</a>
        <form method="POST" action="{{ route('admin.users.toggle-status',$user) }}">
          @csrf
          <button type="submit" class="btn {{ $user->is_active?'btn-w':'btn-ok' }}" style="width:100%;justify-content:center">
            <i class="bi bi-{{ $user->is_active?'lock':'unlock' }}"></i> {{ $user->is_active?'Disable Account':'Enable Account' }}
          </button>
        </form>
        <button onclick="document.getElementById('pwModal').style.display='flex'" class="btn btn-o" style="justify-content:center"><i class="bi bi-key"></i> Reset Password</button>
        @if($user->id !== auth('admin')->id())
        <button onclick="document.getElementById('delModal').style.display='flex'" class="btn btn-e" style="justify-content:center"><i class="bi bi-trash"></i> Delete User</button>
        @endif
      </div>
    </div>
  </div>

  {{-- RIGHT: Activity tabs --}}
  <div>
    {{-- Tab nav — uses userTab() not switchTab() to avoid layout conflict --}}
    <div style="display:flex;gap:4px;background:#fff;border:1px solid var(--border);border-radius:12px;padding:5px;margin-bottom:18px;width:fit-content">
      <button onclick="userTab('overview')"      id="utab-overview"      style="padding:8px 16px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;background:var(--p);color:#fff"><i class="bi bi-grid"></i> Overview</button>
      <button onclick="userTab('applications')"  id="utab-applications"  style="padding:8px 16px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;background:transparent;color:var(--muted)"><i class="bi bi-file-text"></i> Applications</button>
      <button onclick="userTab('loans')"         id="utab-loans"         style="padding:8px 16px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;background:transparent;color:var(--muted)"><i class="bi bi-bank"></i> Loans</button>
      <button onclick="userTab('payments')"      id="utab-payments"      style="padding:8px 16px;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;background:transparent;color:var(--muted)"><i class="bi bi-credit-card"></i> Payments</button>
    </div>

    {{-- Overview panel --}}
    <div id="upanel-overview">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px">
        <div class="sc"><div class="si p"><i class="bi bi-file-text"></i></div><div><div class="sv">{{ $user->loanApplications->count() }}</div><div class="sl">Applications</div></div></div>
        <div class="sc"><div class="si ok"><i class="bi bi-bank"></i></div><div><div class="sv">{{ $user->loans->count() }}</div><div class="sl">Loans</div></div></div>
        <div class="sc"><div class="si i"><i class="bi bi-cash"></i></div><div><div class="sv">L {{ number_format($user->loans->sum('outstanding_balance'),0) }}</div><div class="sl">Outstanding</div></div></div>
      </div>
      <div class="card">
        <div class="card-hdr"><span class="card-title">Account Details</span></div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:1fr 1fr">
            @foreach([
              'Full Name'      => $user->name,
              'Email'          => $user->email,
              'Phone'          => $user->phone ?? '—',
              'Role'           => ucfirst(str_replace('_',' ',$user->role)),
              'Status'         => $user->is_active ? 'Active' : 'Inactive',
              'Member Since'   => $user->created_at->format('d M Y H:i'),
              'Last Login'     => $user->last_login_at?->format('d M Y H:i') ?? 'Never',
              'Email Verified' => $user->email_verified_at ? 'Yes ('.$user->email_verified_at->format('d M Y').')' : 'No',
            ] as $l => $v)
            <div style="padding:12px 0;border-bottom:1px solid var(--border)">
              <div style="font-size:11.5px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em">{{ $l }}</div>
              <div style="font-size:14px;font-weight:600;margin-top:3px">{{ $v }}</div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Applications panel --}}
    <div id="upanel-applications" style="display:none">
      <div class="card">
        <div class="card-hdr"><span class="card-title">Loan Applications</span></div>
        <div style="overflow-x:auto">
          <table class="dt">
            <thead><tr><th>Ref</th><th>Amount</th><th>Product</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @forelse($user->loanApplications()->with('loanProduct')->latest()->get() as $app)
            <tr>
              <td style="font-weight:700;color:var(--p)">{{ $app->application_number }}</td>
              <td>L {{ number_format($app->requested_amount,0) }}</td>
              <td style="font-size:12px">{{ $app->loanProduct->name ?? '—' }}</td>
              <td>
                @php $sc = match($app->status){ 'approved','disbursed'=>'bok','declined'=>'be','on_hold','info_requested'=>'bw',default=>'bs' }; @endphp
                <span class="badge {{ $sc }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span>
              </td>
              <td style="font-size:12px;color:var(--muted)">{{ $app->created_at->format('d M Y') }}</td>
              <td><a href="{{ route('admin.applications.show',$app) }}" class="btn btn-xs btn-o">View</a></td>
            </tr>
            @empty
            <tr><td colspan="6"><div style="text-align:center;padding:32px;color:var(--muted)"><i class="bi bi-file-x" style="font-size:30px;opacity:.3;display:block;margin-bottom:8px"></i>No applications</div></td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Loans panel --}}
    <div id="upanel-loans" style="display:none">
      <div class="card">
        <div class="card-hdr"><span class="card-title">Loans</span></div>
        <div style="overflow-x:auto">
          <table class="dt">
            <thead><tr><th>Loan #</th><th>Principal</th><th>Outstanding</th><th>Monthly</th><th>Status</th><th>Disbursed</th><th></th></tr></thead>
            <tbody>
            @forelse($user->loans()->with('loanProduct')->latest()->get() as $loan)
            <tr>
              <td style="font-weight:700;color:var(--p)">{{ $loan->loan_number }}</td>
              <td>L {{ number_format($loan->principal_amount,0) }}</td>
              <td style="font-weight:600;color:{{ $loan->status==='overdue'?'var(--err)':'var(--dark)' }}">L {{ number_format($loan->outstanding_balance,0) }}</td>
              <td>L {{ number_format($loan->monthly_installment,0) }}</td>
              <td><span class="badge {{ $loan->status==='active'?'bok':($loan->status==='overdue'?'be':'bs') }}">{{ ucfirst($loan->status) }}</span></td>
              <td style="font-size:12px;color:var(--muted)">{{ $loan->disbursement_date?->format('d M Y') }}</td>
              <td><a href="{{ route('admin.loans.show',$loan) }}" class="btn btn-xs btn-o">View</a></td>
            </tr>
            @empty
            <tr><td colspan="7"><div style="text-align:center;padding:32px;color:var(--muted)"><i class="bi bi-bank" style="font-size:30px;opacity:.3;display:block;margin-bottom:8px"></i>No loans</div></td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Payments panel --}}
    <div id="upanel-payments" style="display:none">
      <div class="card">
        <div class="card-hdr"><span class="card-title">Payment History</span></div>
        <div style="overflow-x:auto">
          <table class="dt">
            <thead><tr><th>Reference</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            @php $payments = \App\Models\Payment::where('user_id',$user->id)->latest()->get(); @endphp
            @forelse($payments as $pay)
            <tr>
              <td style="font-weight:700;color:var(--p);font-size:12px">{{ $pay->payment_reference }}</td>
              <td>L {{ number_format($pay->amount,2) }}</td>
              <td style="font-size:12px">{{ ucfirst(str_replace('_',' ',$pay->method)) }}</td>
              <td><span class="badge {{ $pay->status==='verified'?'bok':($pay->status==='pending'?'bw':'be') }}">{{ ucfirst($pay->status) }}</span></td>
              <td style="font-size:12px;color:var(--muted)">{{ $pay->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5"><div style="text-align:center;padding:32px;color:var(--muted)"><i class="bi bi-credit-card" style="font-size:30px;opacity:.3;display:block;margin-bottom:8px"></i>No payments</div></td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Delete modal --}}
<div id="delModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:20px;padding:32px;max-width:420px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,.2)">
    <div style="width:60px;height:60px;border-radius:50%;background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:26px;color:var(--err)"><i class="bi bi-trash"></i></div>
    <h3 style="text-align:center;font-size:18px;margin-bottom:8px">Delete {{ $user->name }}?</h3>
    <p style="text-align:center;color:var(--muted);font-size:14px;margin-bottom:24px">This will permanently delete the user and all their data. This cannot be undone.</p>
    <div style="display:flex;gap:10px">
      <button onclick="document.getElementById('delModal').style.display='none'" class="btn btn-o" style="flex:1">Cancel</button>
      <form method="POST" action="{{ route('admin.users.destroy',$user) }}" style="flex:1">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-e" style="width:100%;justify-content:center"><i class="bi bi-trash"></i> Delete</button>
      </form>
    </div>
  </div>
</div>


{{-- RESET PASSWORD MODAL --}}
<div id="pwModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:18px;width:100%;max-width:420px;margin:20px;box-shadow:0 25px 60px rgba(0,0,0,.2)">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:15px;font-weight:700"><i class="bi bi-key" style="color:var(--p)"></i> Reset Password</span>
      <button onclick="document.getElementById('pwModal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:22px;color:var(--muted)">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.users.reset-password',$user) }}">
      @csrf
      <div style="padding:22px">
        <div class="fg"><label class="fl">New Password *</label><input type="password" name="password" class="fc" placeholder="Minimum 8 characters" required></div>
        <div class="fg"><label class="fl">Confirm Password *</label><input type="password" name="password_confirmation" class="fc" required></div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:9px">
        <button type="button" onclick="document.getElementById('pwModal').style.display='none'" class="btn btn-o">Cancel</button>
        <button type="submit" class="btn btn-p"><i class="bi bi-check-lg"></i> Reset Password</button>
      </div>
    </form>
  </div>
</div>

<script>
// Named userTab() — avoids collision with layout's global switchTab()
function userTab(name) {
  ['overview','applications','loans','payments'].forEach(function(t) {
    var panel = document.getElementById('upanel-' + t);
    var btn   = document.getElementById('utab-' + t);
    if (!panel || !btn) return;
    panel.style.display   = t === name ? 'block' : 'none';
    btn.style.background  = t === name ? 'var(--p)' : 'transparent';
    btn.style.color       = t === name ? '#fff' : 'var(--muted)';
  });
}
document.getElementById('delModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>
@endsection
