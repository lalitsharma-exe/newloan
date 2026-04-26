@extends('admin.layouts.app')
@section('title', 'MyBill Limits')
@section('bc')
<a href="{{ route('admin.mybill.dashboard') }}">MyBill</a> / Credit Limits
@endsection
@section('content')

@if(session('success'))<div style="padding:12px 16px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;margin-bottom:20px;font-size:14px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif

{{-- Subnav --}}
<div class="card mb6" style="padding:0">
  <div style="display:flex;border-bottom:1px solid #e2e8f0;overflow-x:auto">
    <a href="{{ route('admin.mybill.dashboard') }}" style="padding:14px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#64748b;white-space:nowrap"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="{{ route('admin.mybill.loans') }}" style="padding:14px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#64748b;white-space:nowrap"><i class="bi bi-list-ul"></i> All Loans</a>
    <a href="{{ route('admin.mybill.limits') }}" style="padding:14px 20px;text-decoration:none;font-weight:600;font-size:14px;color:var(--blue);border-bottom:2px solid var(--blue);white-space:nowrap"><i class="bi bi-sliders"></i> Credit Limits</a>
  </div>
</div>

<div class="card" style="padding:0">
  {{-- Toolbar --}}
  <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
    <form method="GET" action="{{ route('admin.mybill.limits') }}" style="display:flex;gap:12px">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client name or phone..." class="fc" style="width:280px;font-size:13px">
      <button type="submit" class="btn btn-p" style="padding:8px 16px"><i class="bi bi-search"></i> Search</button>
      @if(request('search'))
      <a href="{{ route('admin.mybill.limits') }}" class="btn btn-o" style="padding:8px 16px">Clear</a>
      @endif
    </form>
    
    {{-- Manual Payday Trigger Button --}}
    <button onclick="openModal('paydayModal')" class="btn btn-ok" style="padding:8px 16px;font-size:13px"><i class="bi bi-lightning-charge-fill"></i> Manual Payday Trigger</button>
  </div>

  {{-- Table --}}
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Client</th>
          <th>Total Limit</th>
          <th>Used Amount</th>
          <th>Available Credit</th>
          <th>Utilization</th>
          <th>Activated On</th>
          <th style="width:120px"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($limits as $limit)
        <tr>
          <td>
            <div style="font-weight:600;font-size:13px"><a href="{{ route('admin.users.show', $limit->user_id) }}" style="text-decoration:none;color:var(--blue)">{{ $limit->user->name ?? 'Unknown' }}</a></div>
            <div style="font-size:11px;color:#64748b">{{ $limit->user->phone ?? '' }}</div>
          </td>
          <td style="font-weight:600;font-size:13px">M {{ number_format($limit->total_limit, 2) }}</td>
          <td style="font-weight:600;font-size:13px;color:{{ $limit->used_amount > 0 ? '#92400e' : '#64748b' }}">M {{ number_format($limit->used_amount, 2) }}</td>
          <td style="font-weight:700;font-size:13px;color:#059669">M {{ number_format($limit->available_amount, 2) }}</td>
          <td>
            @php $pct = $limit->total_limit > 0 ? min(100, ($limit->used_amount / $limit->total_limit) * 100) : 0; @endphp
            <div style="display:flex;align-items:center;gap:8px">
              <div style="flex:1;background:#f1f5f9;height:6px;border-radius:3px;overflow:hidden">
                <div style="background:{{ $pct > 80 ? '#dc2626' : ($pct > 50 ? '#f59e0b' : '#10b981') }};height:100%;width:{{ $pct }}%"></div>
              </div>
              <span style="font-size:11px;font-weight:600;color:#64748b;width:30px">{{ round($pct) }}%</span>
            </div>
          </td>
          <td style="font-size:12px;color:#64748b">{{ $limit->activated_at->format('d M y') }}</td>
          <td style="text-align:right">
            <button onclick="editLimit({{ $limit->id }}, '{{ $limit->user->name }}', {{ $limit->total_limit }})" class="btn btn-sm btn-o"><i class="bi bi-pencil-square"></i> Edit</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:#64748b">No limits found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  @if($limits->hasPages())
  <div style="padding:16px 20px;border-top:1px solid #e2e8f0">
    {{ $limits->withQueryString()->links() }}
  </div>
  @endif
</div>

{{-- Edit Limit Modal --}}
<div id="limitModal" class="mo">
  <div class="mb" style="max-width:400px">
    <div class="mh">
      <h3 class="mt" style="margin:0">Edit Credit Limit</h3>
      <button class="mc" onclick="closeModal('limitModal')">&times;</button>
    </div>
    <form id="limitForm" method="POST" action="">
      @csrf
      @method('PATCH')
      <div class="mbody">
        <div style="margin-bottom:12px;font-size:13px;color:#64748b">Adjust MyBill credit limit for <strong id="limitClientName" style="color:var(--ink)"></strong></div>
        <div class="fg">
          <label class="fl">Total Limit (M)</label>
          <input type="number" name="total_limit" id="limitInput" step="1" min="0" max="10000" class="fc" required>
        </div>
      </div>
      <div class="mf">
        <button type="button" class="btn btn-o" onclick="closeModal('limitModal')">Cancel</button>
        <button type="submit" class="btn btn-p">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- Manual Payday Modal --}}
<div id="paydayModal" class="mo">
  <div class="mb" style="max-width:440px">
    <div class="mh">
      <h3 class="mt" style="margin:0"><i class="bi bi-lightning-charge-fill" style="color:#10b981"></i> Manual Payday Settlement</h3>
      <button class="mc" onclick="closeModal('paydayModal')">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.mybill.payday') }}">
      @csrf
      <div class="mbody">
        <div style="background:#fffbeb;border:1px solid rgba(245,158,11,.2);color:#92400e;padding:12px;border-radius:8px;font-size:12.5px;margin-bottom:16px">
          <strong>Warning:</strong> This will instantly deduct funds from the client's available balance to settle their active MyBill loans (oldest first).
        </div>
        
        <div class="fg" style="margin-bottom:16px">
          <label class="fl">Select Client</label>
          <select name="user_id" class="fc" required style="width:100%">
            <option value="">-- Choose client --</option>
            @foreach(\App\Models\User::borrowers()->whereHas('myBillLoans', fn($q) => $q->active())->get() as $u)
              <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->phone }})</option>
            @endforeach
          </select>
        </div>

        <div class="fg">
          <label class="fl">Available Salary Amount (M)</label>
          <input type="number" name="amount" step="0.01" min="0.01" class="fc" placeholder="Amount received" required>
        </div>
      </div>
      <div class="mf">
        <button type="button" class="btn btn-o" onclick="closeModal('paydayModal')">Cancel</button>
        <button type="submit" class="btn btn-ok"><i class="bi bi-check-circle"></i> Run Settlement</button>
      </div>
    </form>
  </div>
</div>

<script>
function editLimit(id, name, limit) {
  document.getElementById('limitClientName').textContent = name;
  document.getElementById('limitInput').value = limit;
  document.getElementById('limitForm').action = '/admin/mybill/limits/' + id;
  openModal('limitModal');
}
</script>

@endsection
