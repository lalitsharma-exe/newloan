<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $svc) {}

    public function index(Request $request)
    {
        $filters = $request->only(['role','status','search']);
        return view('admin.users.index', [
            'users'   => $this->svc->getPaginated($filters),
            'stats'   => $this->svc->getStats(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $officers = User::where('role', 'loan_officer')->where('is_active', true)->orderBy('name')->get();
        return view('admin.users.create', compact('officers'));
    }

    public function store(Request $request)
    {
        $isBorowwer = $request->input('role') === 'borrower';

        $rules = [
            'name'                => 'required|string|max:150',
            'email'               => 'nullable|email|unique:users,email',
            'phone'               => 'required|string|max:30|unique:users,phone',
            'role'                => 'required|in:admin,loan_officer,borrower',
            'password'            => 'required|string|min:8|confirmed',
            'is_active'           => 'nullable|in:0,1',
            'assigned_officer_id' => $isBorowwer ? 'required|exists:users,id' : 'nullable|exists:users,id',
            'national_id'         => $isBorowwer ? 'required|string|max:50|unique:users,national_id' : 'nullable|string|max:50',
        ];

        $validated = $request->validate($rules);

        // Duplicate checks
        if ($request->filled('national_id')) {
            $exists = User::where('national_id', $request->national_id)->exists();
            if ($exists) {
                return back()->withErrors(['national_id' => 'A borrower with this ID number already exists.'])->withInput();
            }
        }

        // Format phone to +266XXXXXXXX
        $phone = $this->formatPhone($request->phone);
        $dupPhone = User::where('phone', $phone)->exists();
        if ($dupPhone) {
            return back()->withErrors(['phone' => 'A user with this phone number already exists.'])->withInput();
        }

        $data = $request->all();
        $data['phone']     = $phone;
        $data['is_active'] = $request->input('is_active', '1') === '1';

        $user = $this->svc->create($data);
        return redirect()->route('admin.users.index')->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        $user->load(['loanApplications.loanProduct','loans.loanProduct']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $officers = User::where('role', 'loan_officer')->where('is_active', true)->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'officers'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'                => 'required|string|max:150',
            'email'               => 'nullable|email|unique:users,email,'.$user->id,
            'phone'               => 'required|string|max:30|unique:users,phone,'.$user->id,
            'role'                => 'required|in:admin,loan_officer,borrower',
            'password'            => 'nullable|string|min:8|confirmed',
            'is_active'           => 'nullable|in:0,1',
            'assigned_officer_id' => 'nullable|exists:users,id',
            'national_id'         => 'nullable|string|max:50|unique:users,national_id,'.$user->id,
        ];

        $request->validate($rules);

        // Duplicate phone check
        $phone = $this->formatPhone($request->phone);
        $dupPhone = User::where('phone', $phone)->where('id', '!=', $user->id)->exists();
        if ($dupPhone) {
            return back()->withErrors(['phone' => 'Another user with this phone number already exists.'])->withInput();
        }

        $data              = $request->all();
        $data['phone']     = $phone;
        $data['is_active'] = $request->input('is_active', '1') === '1';

        $this->svc->update($user, $data);
        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $this->svc->toggleStatus($user, auth('admin')->user());
        $status = $user->fresh()->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "{$user->name} has been {$status}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $name = $user->name;
        $this->svc->delete($user);
        return redirect()->route('admin.users.index')->with('success', "{$name} deleted successfully.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);
        \App\Models\AuditLog::record('user.reset_password', "Password reset for {$user->name}", $user);
        return back()->with('success', "Password reset for {$user->name}.");
    }

    public function impersonate(User $user)
    {
        if (auth('admin')->user()->role !== 'admin') {
            return back()->with('error', 'Only administrators can impersonate users.');
        }
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot impersonate admin accounts.');
        }
        session(['impersonating_user' => $user->id, 'impersonating_from' => 'admin']);
        \App\Models\AuditLog::record('user.impersonate', "Started impersonating {$user->name}", $user);
        return redirect()->route('borrower.dashboard')->with('info', "Viewing as {$user->name}. Close tab to return.");
    }

    public function activity(User $user)
    {
        $logs = \App\Models\AuditLog::where('user_id', $user->id)->latest()->paginate(20);
        return view('admin.users.activity', compact('user', 'logs'));
    }

    public function loans(User $user)
    {
        $user->load(['loans.loanProduct', 'loans.installments']);
        return view('admin.users.loans', compact('user'));
    }

    public function applications(User $user)
    {
        $user->load(['loanApplications.loanProduct']);
        return view('admin.users.applications', compact('user'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);
        $path    = $request->file('file')->path();
        $rows    = array_map('str_getcsv', file($path));
        if (count($rows) < 2) return back()->with('error', 'CSV file is empty or has no data rows.');
        $header   = array_map('trim', array_shift($rows));
        $imported = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            if (count($row) < count($header)) { $skipped++; continue; }
            $data  = array_combine($header, array_map('trim', $row));
            $phone = $this->formatPhone($data['phone'] ?? '');
            if (!$phone) { $skipped++; continue; }
            if (User::where('phone', $phone)->exists()) { $skipped++; continue; }
            User::create([
                'name'      => $data['name'] ?? $phone,
                'email'     => !empty($data['email']) ? $data['email'] : null,
                'phone'     => $phone,
                'role'      => 'borrower',
                'password'  => \Illuminate\Support\Facades\Hash::make($data['password'] ?? 'Password@123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $imported++;
        }

        \App\Models\AuditLog::record('users.import', "Imported {$imported} borrowers via CSV");
        return back()->with('success', "{$imported} borrowers imported.".($skipped ? " {$skipped} rows skipped." : ''));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['role','status','search']);
        $users   = $this->svc->getPaginated($filters, 9999);
        $csv     = "Name,Email,Phone,Role,Status,Created\n";
        foreach ($users as $u) {
            $csv .= implode(',', ['"'.$u->name.'"', $u->email ?? '', $u->phone ?? '', $u->role, $u->is_active ? 'Active' : 'Inactive', $u->created_at->format('Y-m-d')]) . "\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="borrowers-'.now()->format('Y-m-d').'.csv"']);
    }

    // ── Format phone to +266XXXXXXXX ─────────────────────────────────────────
    private function formatPhone(?string $phone): string
    {
        if (!$phone) return '';
        $digits = preg_replace('/[^0-9]/', '', $phone);
        // Already has country code
        if (strlen($digits) === 12 && str_starts_with($digits, '266')) {
            return '+' . $digits;
        }
        // 8 digits only — prepend +266
        if (strlen($digits) === 8) {
            return '+266' . $digits;
        }
        // Has leading 0 (local format 0XXXXXXXX = 9 digits, unlikely but handle)
        if (strlen($digits) === 9 && str_starts_with($digits, '0')) {
            return '+266' . substr($digits, 1);
        }
        // Return as-is if already has + prefix
        if (str_starts_with($phone, '+266')) {
            return $phone;
        }
        return '+266' . substr($digits, -8);
    }
}
