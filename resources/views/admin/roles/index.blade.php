@extends('admin.layouts.app')
@section('title', 'Admin Roles')
@section('page-title', 'Roles & Permissions')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / Roles
@endsection

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <div></div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-p">
        <i class="bi bi-plus-lg"></i> Create Role
    </a>
</div>

<div class="card">
    <div class="card-hdr">
        <span class="card-title">All Roles</span>
        <span class="muted">{{ $roles->count() }} role{{ $roles->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dt">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Description</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th>Created</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px;
                                background:{{ $role->is_super_admin ? 'linear-gradient(135deg,#c9a84c,#e8c865)' : 'rgba(43,75,173,.1)' }};
                                color:{{ $role->is_super_admin ? '#fff' : 'var(--pl)' }};">
                                <i class="bi bi-{{ $role->is_super_admin ? 'shield-lock-fill' : 'person-badge' }}"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;">{{ $role->name }}</div>
                                @if($role->is_super_admin)
                                <span style="font-size:10px; font-weight:700; color:#c9a84c; text-transform:uppercase; letter-spacing:.05em;">Full Access</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="muted" style="font-size:12.5px; max-width:200px;">{{ $role->description ?? '—' }}</td>
                    <td>
                        @if($role->is_super_admin)
                            <span class="badge bok">All Permissions</span>
                        @else
                            <span style="font-weight:600;">{{ count($role->enabledPermissions()) }}</span>
                            <span class="muted" style="font-size:11.5px;">/ {{ count(collect(config('admin_permissions'))->flatMap(fn($p) => $p)) }}</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600;">{{ $role->users_count }}</span>
                        <span class="muted" style="font-size:11.5px;">user{{ $role->users_count !== 1 ? 's' : '' }}</span>
                    </td>
                    <td class="muted" style="font-size:12.5px;">{{ $role->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:8px; justify-content:flex-end;">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-o">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @if(!$role->is_super_admin)
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-e">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty">
                        <i class="bi bi-shield-lock"></i>
                        No roles created yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
