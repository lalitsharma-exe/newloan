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
}