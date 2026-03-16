<?php
namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getPaginated(array $f, int $perPage = 20)
    {
        $q = User::query();
        if (!empty($f['role']))    $q->where('role', $f['role']);
        if (!empty($f['status']))  $q->where('is_active', $f['status'] === 'active');
        if (!empty($f['search'])) {
            $s = $f['search'];
            $q->where(fn($x) =>
                $x->where('name',        'like', "%$s%")
                  ->orWhere('email',     'like', "%$s%")
                  ->orWhere('phone',     'like', "%$s%")
                  ->orWhere('national_id','like', "%$s%")
            );
        }
        return $q->latest()->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total'    => User::count(),
            'admins'   => User::where('role', 'admin')->count(),
            'officers' => User::where('role', 'loan_officer')->count(),
            'borrowers'=> User::where('role', 'borrower')->count(),
            'active'   => User::where('is_active', true)->count(),
        ];
    }

    public function create(array $d): User
    {
        return User::create([
            'name'                => $d['name'],
            'email'               => !empty($d['email']) ? $d['email'] : null,
            'phone'               => $d['phone'],
            'national_id'         => $d['national_id'] ?? null,
            'date_of_birth'       => $d['date_of_birth'] ?? null,
            'address'             => $d['address'] ?? null,
            'role'                => $d['role'],
            'assigned_officer_id' => $d['assigned_officer_id'] ?? null,
            'password'            => Hash::make($d['password']),
            'is_active'           => $d['is_active'] ?? true,
            'email_verified_at'   => now(),
        ]);
    }

    public function update(User $u, array $d): User
    {
        $up = [
            'name'                => $d['name'],
            'email'               => !empty($d['email']) ? $d['email'] : null,
            'phone'               => $d['phone'],
            'national_id'         => $d['national_id'] ?? $u->national_id,
            'date_of_birth'       => $d['date_of_birth'] ?? $u->date_of_birth,
            'address'             => $d['address'] ?? $u->address,
            'role'                => $d['role'],
            'assigned_officer_id' => $d['assigned_officer_id'] ?? $u->assigned_officer_id,
            'is_active'           => $d['is_active'] ?? $u->is_active,
        ];
        if (!empty($d['password'])) {
            $up['password'] = Hash::make($d['password']);
        }
        $u->update($up);
        return $u;
    }

    public function toggleStatus(User $u, User $admin): void
    {
        $u->update(['is_active' => !$u->is_active]);
    }

    public function delete(User $u): void
    {
        $u->delete();
    }
}
