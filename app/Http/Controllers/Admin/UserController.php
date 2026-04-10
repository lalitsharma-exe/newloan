<?php
namespace App\Http\Controllers\Admin;

use App\Exports\BorrowersExport;
use App\Exports\BorrowersImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\BorrowersImport;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            'maiden_name'         => 'nullable|string|max:100',
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
            'maiden_name'         => 'nullable|string|max:100',
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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'file.mimes' => 'Please upload an Excel (.xlsx, .xls) or CSV file.',
        ]);

        $import = new BorrowersImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        \App\Models\AuditLog::record(
            'users.import',
            "Imported {$import->imported} borrowers via Excel/CSV. Skipped: {$import->skipped}."
        );

        $message = "{$import->imported} borrower(s) imported successfully.";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} row(s) skipped.";
        }

        // Store per-row errors in session for display
        if (!empty($import->errors)) {
            session(['import_errors' => $import->errors]);
        }

        return back()->with('import_success', $message);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $filename = 'borrowers-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new BorrowersExport($filters), $filename);
    }

    public function importTemplate()
    {
        return Excel::download(new BorrowersImportTemplate(), 'borrowers-import-template.xlsx');
    }

    public function profileRequests()
    {
        $requests = \App\Models\ProfileChangeRequest::with('user')->latest()->paginate(20);
        return view('admin.users.profile-requests', compact('requests'));
    }

    public function handleProfileRequest(Request $request, $requestId)
    {
        $profileRequest = \App\Models\ProfileChangeRequest::findOrFail($requestId);
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:500'
        ]);

        $profileRequest->update([
            'status' => $request->action === 'approve' ? 'approved' : 'rejected',
            'admin_note' => $request->admin_note
        ]);

        return back()->with('success', 'Profile change request ' . $request->action . 'd.');
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
