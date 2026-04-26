<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $fillable = ['name', 'slug', 'permissions', 'is_super_admin', 'description'];

    protected $casts = [
        'permissions'    => 'array',
        'is_super_admin' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'admin_role_id');
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) return true;

        $perms = $this->permissions ?? [];
        return !empty($perms[$permission]);
    }

    /**
     * Get all enabled permission slugs.
     */
    public function enabledPermissions(): array
    {
        if ($this->is_super_admin) {
            return array_keys(collect(config('admin_permissions'))->flatMap(fn($perms) => $perms)->toArray());
        }
        return array_keys(array_filter($this->permissions ?? []));
    }
}
