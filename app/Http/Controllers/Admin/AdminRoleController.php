<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AdminRole, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = AdminRole::withCount('users')->orderBy('is_super_admin', 'desc')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionGroups = config('admin_permissions');
        return view('admin.roles.form', [
            'role'             => null,
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:admin_roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        $permissions = [];
        $allPerms = collect(config('admin_permissions'))->flatMap(fn($perms) => $perms)->keys();
        foreach ($allPerms as $perm) {
            $permissions[$perm] = $request->boolean("perm_{$perm}");
        }

        $role = AdminRole::create([
            'name'           => $request->name,
            'slug'           => Str::slug($request->name, '_'),
            'description'    => $request->description,
            'permissions'    => $permissions,
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        AuditLog::record('role.created', "Admin role '{$role->name}' created.", null, [],
            ['role_id' => $role->id, 'permissions' => array_keys(array_filter($permissions))]
        );

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' created successfully.");
    }

    public function edit(AdminRole $role)
    {
        $permissionGroups = config('admin_permissions');
        return view('admin.roles.form', [
            'role'             => $role,
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function update(Request $request, AdminRole $role)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:admin_roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $permissions = [];
        $allPerms = collect(config('admin_permissions'))->flatMap(fn($perms) => $perms)->keys();
        foreach ($allPerms as $perm) {
            $permissions[$perm] = $request->boolean("perm_{$perm}");
        }

        $role->update([
            'name'           => $request->name,
            'slug'           => Str::slug($request->name, '_'),
            'description'    => $request->description,
            'permissions'    => $permissions,
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        AuditLog::record('role.updated', "Admin role '{$role->name}' updated.", null, [],
            ['role_id' => $role->id, 'permissions' => array_keys(array_filter($permissions))]
        );

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' updated.");
    }

    public function destroy(AdminRole $role)
    {
        if ($role->is_super_admin) {
            return back()->with('error', 'Cannot delete the Super Admin role.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Cannot delete '{$role->name}' — {$role->users()->count()} user(s) are assigned to it.");
        }

        $name = $role->name;
        $role->delete();

        AuditLog::record('role.deleted', "Admin role '{$name}' deleted.");

        return back()->with('success', "Role '{$name}' deleted.");
    }
}
