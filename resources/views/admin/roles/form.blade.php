@extends('admin.layouts.app')
@section('title', $role ? "Edit {$role->name}" : 'Create Role')
@section('page-title', $role ? "Edit Role" : 'Create Role')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / <a href="{{ route('admin.roles.index') }}">Roles</a> / {{ $role ? 'Edit' : 'New' }}
@endsection

@section('content')
@push('styles')
<style>
.perm-group {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    margin-bottom: 16px;
    overflow: hidden;
}
.perm-group-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
}
.perm-group-hdr h6 {
    font-size: 13px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.perm-group-toggle {
    font-size: 11px;
    font-weight: 600;
    color: var(--pl);
    cursor: pointer;
    border: none;
    background: none;
    font-family: 'Inter', sans-serif;
}
.perm-group-toggle:hover { text-decoration: underline; }
.perm-list { padding: 8px 12px; }

.perm-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 8px;
    border-radius: 8px;
    transition: background .15s;
}
.perm-row:hover { background: #f8fafc; }
.perm-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--dark);
}
.perm-slug {
    font-size: 11px;
    color: var(--muted);
    font-family: 'Courier New', monospace;
}

/* Toggle switch */
.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #cbd5e1;
    border-radius: 24px;
    transition: all .25s;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: all .25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.15);
}
.toggle-switch input:checked + .toggle-slider {
    background: var(--ok);
}
.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

.super-admin-banner {
    background: linear-gradient(135deg, #c9a84c, #e8c865);
    color: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.super-admin-banner i { font-size: 24px; }

.form-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}

@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

<form action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST">
    @csrf
    @if($role) @method('PUT') @endif

    <div class="form-grid">
        <div>
            {{-- Role Details --}}
            <div class="card" style="margin-bottom:18px;">
                <div class="card-hdr"><span class="card-title">Role Details</span></div>
                <div class="card-body">
                    <div class="fg">
                        <label class="fl">Role Name *</label>
                        <input type="text" name="name" class="fc" value="{{ old('name', $role?->name) }}" placeholder="e.g. Finance Manager" required>
                        @error('name') <span class="iv">{{ $message }}</span> @enderror
                    </div>
                    <div class="fg">
                        <label class="fl">Description</label>
                        <input type="text" name="description" class="fc" value="{{ old('description', $role?->description) }}" placeholder="Short description of this role">
                    </div>
                    <div class="perm-row" style="margin-top:8px; padding:14px 12px; background:linear-gradient(135deg,rgba(201,168,76,.08),rgba(232,200,101,.08)); border-radius:10px; border:1px solid rgba(201,168,76,.15);">
                        <div>
                            <div class="perm-label" style="font-weight:700; color:#92730a;">
                                <i class="bi bi-shield-lock-fill" style="margin-right:4px;"></i> Super Admin
                            </div>
                            <div class="perm-slug" style="font-family:'Inter',sans-serif; color:#b8941a;">Bypasses all permission checks — full access</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_super_admin" value="1" id="superAdminToggle"
                                {{ old('is_super_admin', $role?->is_super_admin) ? 'checked' : '' }}
                                onchange="toggleSuperAdmin()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Permissions --}}
            <div id="permissionsSection">
                @foreach($permissionGroups as $group => $permissions)
                <div class="perm-group" data-group="{{ Str::slug($group) }}">
                    <div class="perm-group-hdr">
                        <h6>
                            @switch($group)
                                @case('Dashboard') <i class="bi bi-grid-fill" style="color:var(--pl);"></i> @break
                                @case('Lending') <i class="bi bi-bank2" style="color:var(--pl);"></i> @break
                                @case('Reports & Marketing') <i class="bi bi-bar-chart-fill" style="color:var(--pl);"></i> @break
                                @case('Configuration') <i class="bi bi-gear-fill" style="color:var(--pl);"></i> @break
                                @case('System') <i class="bi bi-cpu-fill" style="color:var(--pl);"></i> @break
                            @endswitch
                            {{ $group }}
                        </h6>
                        <button type="button" class="perm-group-toggle" onclick="toggleGroup('{{ Str::slug($group) }}')">Toggle All</button>
                    </div>
                    <div class="perm-list">
                        @foreach($permissions as $slug => $label)
                        <div class="perm-row">
                            <div>
                                <div class="perm-label">{{ $label }}</div>
                                <div class="perm-slug">{{ $slug }}</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="perms[{{ $slug }}]" value="1" class="perm-check" data-group="{{ Str::slug($group) }}"
                                    {{ old("perms.{$slug}", $role?->permissions[$slug] ?? false) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="card" style="position:sticky; top:20px;">
                <div class="card-hdr"><span class="card-title">Summary</span></div>
                <div class="card-body">
                    <div style="text-align:center; padding:16px 0;">
                        <div id="permCount" style="font-size:36px; font-weight:800; color:var(--pl); font-family:'Playfair Display',serif; line-height:1;">0</div>
                        <div class="muted" style="font-size:12px; margin-top:4px;">permissions enabled</div>
                    </div>

                    <div style="margin:16px 0; padding:12px; background:var(--bg); border-radius:8px; font-size:12px; color:var(--muted); line-height:1.6;">
                        <i class="bi bi-info-circle" style="margin-right:4px;"></i>
                        Users assigned to this role will <strong>only</strong> see the sidebar links and access the pages you enable here.
                    </div>

                    <div style="display:flex; gap:10px; margin-top:16px;">
                        <button type="submit" class="btn btn-p" style="flex:1;">
                            <i class="bi bi-check-lg"></i> {{ $role ? 'Update Role' : 'Create Role' }}
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-o">Cancel</a>
                    </div>

                    @if($role && $role->users()->count() > 0)
                    <div style="margin-top:16px; padding:12px; background:rgba(245,158,11,.06); border:1px solid rgba(245,158,11,.15); border-radius:8px; font-size:12px; color:#92400e;">
                        <i class="bi bi-exclamation-triangle-fill" style="margin-right:4px;"></i>
                        <strong>{{ $role->users()->count() }} user(s)</strong> are assigned to this role. Changes will take effect immediately.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function updatePermCount() {
    const isSuperAdmin = document.getElementById('superAdminToggle').checked;
    const total = document.querySelectorAll('.perm-check').length;
    const checked = document.querySelectorAll('.perm-check:checked').length;
    document.getElementById('permCount').textContent = isSuperAdmin ? total : checked;
}

function toggleSuperAdmin() {
    const isSuper = document.getElementById('superAdminToggle').checked;
    const section = document.getElementById('permissionsSection');
    section.style.opacity = isSuper ? '0.4' : '1';
    section.style.pointerEvents = isSuper ? 'none' : 'auto';
    updatePermCount();
}

function toggleGroup(group) {
    const checks = document.querySelectorAll(`.perm-check[data-group="${group}"]`);
    const allChecked = [...checks].every(c => c.checked);
    checks.forEach(c => c.checked = !allChecked);
    updatePermCount();
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    toggleSuperAdmin();
    document.querySelectorAll('.perm-check').forEach(c => c.addEventListener('change', updatePermCount));
});
</script>
@endpush
@endsection
