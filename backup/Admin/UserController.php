<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller {
    public function __construct(private UserService $svc) {}

    public function index(Request $request) {
        $filters = $request->only(['role','status','search']);
        return view('admin.users.index',[
            'users'   => $this->svc->getPaginated($filters),
            'stats'   => $this->svc->getStats(),
            'filters' => $filters,
        ]);
    }

    public function create() {
        return view('admin.users.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users',
            'phone'                 => 'nullable|string|unique:users',
            'role'                  => 'required|in:admin,loan_officer,borrower',
            'password'              => 'required|string|min:8|confirmed',
            'is_active'             => 'nullable|in:0,1',
        ]);
        $data = $request->all();
        $data['is_active'] = $request->input('is_active', '1') === '1';
        $user = $this->svc->create($data);
        return redirect()->route('admin.users.index')->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user) {
        $user->load(['loanApplications.loanProduct','loans.loanProduct']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user) {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user) {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'phone'    => 'nullable|string|unique:users,phone,'.$user->id,
            'role'     => 'required|in:admin,loan_officer,borrower',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active'=> 'nullable|in:0,1',
        ]);
        $data = $request->all();
        $data['is_active'] = $request->input('is_active', '1') === '1';
        $this->svc->update($user, $data);
        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user) {
        $this->svc->toggleStatus($user, auth('admin')->user());
        $status = $user->fresh()->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "{$user->name} has been {$status}.");
    }

    public function destroy(User $user) {
        if ($user->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $name = $user->name;
        $this->svc->delete($user);
        return redirect()->route('admin.users.index')->with('success', "{$name} deleted successfully.");
    }

    // ── Bulk CSV import ─────────────────────────────────────────────
    public function import(Request $request) {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);
        $path = $request->file('file')->path();
        $rows = array_map('str_getcsv', file($path));
        if (count($rows) < 2) return back()->with('error', 'CSV file is empty or has no data rows.');

        $header  = array_map('trim', array_shift($rows));
        $imported = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            if (count($row) < count($header)) { $skipped++; continue; }
            $data = array_combine($header, array_map('trim', $row));
            if (empty($data['email'])) { $skipped++; continue; }

            User::updateOrCreate(['email' => $data['email']], [
                'name'      => $data['name']  ?? $data['email'],
                'phone'     => $data['phone'] ?? null,
                'role'      => 'borrower',
                'password'  => \Illuminate\Support\Facades\Hash::make($data['password'] ?? 'Password@123'),
                'is_active' => true,
            ]);
            $imported++;
        }

        \App\Models\AuditLog::record('users.import', "Imported {$imported} borrowers via CSV", null, [], ['count' => $imported]);
        return back()->with('success', "{$imported} borrowers imported successfully." . ($skipped ? " {$skipped} rows skipped.": ''));
    }

    // ── Export CSV ──────────────────────────────────────────────────
    public function export(Request $request) {
        $filters = $request->only(['role','status','search']);
        $users   = $this->svc->getPaginated($filters, 9999);
        $csv = "Name,Email,Phone,Role,Status,Created\n";
        foreach ($users as $u) {
            $csv .= implode(',', [
                '"'.$u->name.'"',
                $u->email,
                $u->phone ?? '',
                $u->role,
                $u->is_active ? 'Active' : 'Inactive',
                $u->created_at->format('Y-m-d'),
            ]) . "\n";
        }
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="borrowers-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
}
