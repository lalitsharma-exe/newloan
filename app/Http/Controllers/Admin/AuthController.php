<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    public function showLogin() {
        // Already logged in? Go to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt(
            $request->only('email','password'),
            $request->boolean('remember')
        )) {
            $user = Auth::guard('admin')->user();
            if (!$user->is_active) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages(['email' => 'Your account has been disabled.']);
            }
            if (!in_array($user->role, ['admin','loan_officer'])) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages(['email' => 'You do not have admin access.']);
            }
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('admin.dashboard'))
                             ->with('success', "Welcome back, {$user->name}!");
        }

        throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }
}