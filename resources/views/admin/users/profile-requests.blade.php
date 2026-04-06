@extends('admin.layouts.app')
@section('title', 'Profile Change Requests')
@section('page-title', 'Profile Change Requests')
@section('bc', 'Profile Requests')

@section('content')

{{-- Flash messages --}}
@if(session('success'))<div class="alert a-ok mb-4"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert" style="background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)" class="mb-4"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

<div class="card">
    <div class="card-hdr">
        <span class="card-title">Pending & Past Requests <span style="color:var(--muted);font-weight:400">({{ $requests->total() }})</span></span>
    </div>
    <div style="overflow-x:auto">
        <table class="dt">
            <thead>
                <tr>
                    <th>Borrower</th>
                    <th>Requested Details</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13.5px">{{ $request->user->name }}</div>
                        <div style="font-size:12px;color:var(--muted)">{{ $request->user->email }}</div>
                    </td>
                    <td>
                        <div style="max-width:300px; white-space: pre-wrap; font-size:13px; color:var(--muted)">{{ $request->requested_details }}</div>
                        @if($request->admin_note)
                        <div style="margin-top:8px; padding:8px; background:#f8fafc; border-radius:6px; font-size:12px; border:1px solid var(--border)">
                            <strong>Admin Note:</strong> {{ $request->admin_note }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($request->status === 'pending')
                            <span class="badge bi"><i class="bi bi-clock-history"></i> Pending</span>
                        @elseif($request->status === 'approved')
                            <span class="badge bs"><i class="bi bi-check-circle"></i> Approved</span>
                        @else
                            <span class="badge be"><i class="bi bi-x-circle"></i> Rejected</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--muted)">{{ $request->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        <div style="display:flex;justify-content:flex-end;gap:6px">
                            @if($request->status === 'pending')
                                <button onclick="openHandleModal({{ $request->id }}, '{{ addslashes($request->user->name) }}')" class="btn btn-xs btn-p" title="Handle Request">
                                    <i class="bi bi-pencil-square"></i> Handle
                                </button>
                            @else
                                <span style="font-size:11px; color:var(--muted)">Processed</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div style="text-align:center;padding:48px 0;color:var(--muted)">
                            <i class="bi bi-person-gear" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px"></i>
                            <div style="font-weight:600">No profile change requests found</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $requests->links() }}</div>
    @endif
</div>

{{-- Handle Request Modal --}}
<div id="handleModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:20px;padding:32px;max-width:500px;width:95%;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:modalIn .2s ease">
        <h3 style="font-size:18px;margin-bottom:8px">Handle Profile Request</h3>
        <p style="color:var(--muted);font-size:14px;margin-bottom:20px">
            Updating profile request for <strong id="requestUserName"></strong>.
        </p>
        
        <form id="handleForm" method="POST" action="">
            @csrf
            <div class="fg">
                <label class="fl">Admin Note (Optional)</label>
                <textarea name="admin_note" class="fc" style="height:100px" placeholder="e.g. Profil updated as requested or Request rejected due to missing documents..."></textarea>
            </div>
            
            <input type="hidden" name="status" id="requestStatus" value="approved">
            
            <div style="display:flex;gap:10px;margin-top:24px">
                <button type="button" onclick="closeHandleModal()" class="btn btn-o" style="flex:1">Cancel</button>
                <button type="button" onclick="submitStatus('rejected')" class="btn btn-e" style="flex:1"><i class="bi bi-x-lg"></i> Reject</button>
                <button type="button" onclick="submitStatus('approved')" class="btn btn-p" style="flex:1"><i class="bi bi-check-lg"></i> Approve</button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px}
</style>

<script>
function openHandleModal(id, name) {
    const form = document.getElementById('handleForm');
    form.action = `/admin/users/profile-requests/${id}`;
    document.getElementById('requestUserName').textContent = name;
    document.getElementById('handleModal').style.display = 'flex';
}

function closeHandleModal() {
    document.getElementById('handleModal').style.display = 'none';
}

function submitStatus(status) {
    document.getElementById('requestStatus').value = status;
    document.getElementById('handleForm').submit();
}

document.getElementById('handleModal').addEventListener('click', function(e) {
    if (e.target === this) closeHandleModal();
});
</script>

@endsection
