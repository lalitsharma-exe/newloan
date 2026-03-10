@extends('admin.layouts.app')
@section('title','Users')
@section('page-title','User Management')
@section('content')
<div class="grid grid-4 mb-6">
    <div class="stat-card"><div class="stat-icon primary"><i class="bi bi-people-fill"></i></div><div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Users</div></div></div>
    <div class="stat-card"><div class="stat-icon danger"><i class="bi bi-shield-fill"></i></div><div><div class="stat-value">{{ $stats['admins'] }}</div><div class="stat-label">Admins</div></div></div>
    <div class="stat-card"><div class="stat-icon info"><i class="bi bi-person-badge-fill"></i></div><div><div class="stat-value">{{ $stats['officers'] }}</div><div class="stat-label">Loan Officers</div></div></div>
    <div class="stat-card"><div class="stat-icon success"><i class="bi bi-person-fill"></i></div><div><div class="stat-value">{{ $stats['borrowers'] }}</div><div class="stat-label">Borrowers</div></div></div>
</div>
<form method="GET" class="filter-bar mb-4">
    <div class="form-group" style="flex:2"><label class="form-label">Search</label><input type="text" name="search" class="form-control" placeholder="Name, email, phone…" value="{{ $filters['search']??'' }}"></div>
    <div class="form-group"><label class="form-label">Role</label><select name="role" class="form-control"><option value="">All Roles</option>@foreach(['admin','loan_officer','borrower'] as $r)<option value="{{ $r }}" {{ ($filters['role']??'')===$r?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>@endforeach</select></div>
    <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="">All</option><option value="active" {{ ($filters['status']??'')==='active'?'selected':'' }}>Active</option><option value="inactive" {{ ($filters['status']??'')==='inactive'?'selected':'' }}>Inactive</option></select></div>
    <div style="display:flex;gap:8px;align-items:flex-end"><button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button><a href="{{ route('admin.users.index') }}" class="btn btn-outline">Clear</a></div>
</form>
<div class="card">
    <div class="card-header"><span class="card-title">Users ({{ $users->total() }})</span><a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add User</a></div>
    <div style="overflow-x:auto"><table class="data-table">
        <thead><tr><th>User</th><th>Phone</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($users as $user)
        <tr>
            <td><div style="display:flex;align-items:center;gap:9px"><div class="avatar avatar-sm">{{ strtoupper(substr($user->name,0,1)) }}</div><div><div style="font-size:13px;font-weight:600">{{ $user->name }}</div><div style="font-size:11px;color:#94a3b8">{{ $user->email }}</div></div></div></td>
            <td>{{ $user->phone??'—' }}</td>
            <td><span class="badge badge-{{ $user->role==='admin'?'danger':($user->role==='loan_officer'?'info':'secondary') }}">{{ ucfirst(str_replace('_',' ',$user->role)) }}</span></td>
            <td><span class="badge badge-{{ $user->is_active?'success':'danger' }}">{{ $user->is_active?'Active':'Inactive' }}</span></td>
            <td><span style="font-size:12px;color:#64748b">{{ $user->last_login_at?->diffForHumans()??'Never' }}</span></td>
            <td>
                <div style="display:flex;gap:5px">
                    <a href="{{ route('admin.users.edit',$user) }}" class="btn btn-xs btn-outline"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.users.toggle-status',$user) }}">@csrf<button class="btn btn-xs {{ $user->is_active?'btn-warning':'btn-success' }}" title="{{ $user->is_active?'Disable':'Enable' }}"><i class="bi bi-{{ $user->is_active?'lock':'unlock' }}"></i></button></form>
                    <form method="POST" action="{{ route('admin.users.destroy',$user) }}" onsubmit="return confirm('Delete this user?')">@csrf@method('DELETE')<button class="btn btn-xs btn-danger"><i class="bi bi-trash"></i></button></form>
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-people"></i><p>No users found</p></div></td></tr>@endforelse
        </tbody>
    </table></div>
    @if($users->hasPages())<div style="padding:16px 20px;border-top:1px solid #f1f5f9">{{ $users->withQueryString()->links() }}</div>@endif
</div>
@endsection
