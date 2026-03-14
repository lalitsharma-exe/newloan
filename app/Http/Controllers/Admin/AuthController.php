<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Password, DB};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    public function showLogin() {
        if (Auth::guard('admin')->check()) return redirect()->route('admin.dashboard');
        return view('admin.auth.login');
    }

    public function login(Request $request) {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::guard('admin')->attempt($request->only('email','password'), $request->boolean('remember'))) {
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
            return redirect()->intended(route('admin.dashboard'))->with('success', "Welcome back, {$user->name}!");
        }
        throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }

    // ── Forgot password ──────────────────────────────────────────────
    public function showForgotPassword() {
        if (Auth::guard('admin')->check()) return redirect()->route('admin.dashboard');
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request) {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)
                    ->whereIn('role', ['admin','loan_officer'])
                    ->first();
        // Always return success to prevent email enumeration
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
            // In production: Mail::to($user)->send(new AdminPasswordReset($token));
            // For dev: flash the token directly
            return back()->with('success', 'If that email exists, a reset link has been sent. (Dev token: '.$token.')');
        }
        return back()->with('success', 'If that email exists, a reset link has been sent.');
    }

    public function showResetPassword(Request $request, string $token) {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
        ]);
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }
        $user = User::where('email', $request->email)->whereIn('role',['admin','loan_officer'])->first();
        if (!$user) return back()->withErrors(['email' => 'No admin account found.']);

        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return redirect()->route('admin.login')->with('success', 'Password reset successfully. Please log in.');
    }

    // ── Admin profile ────────────────────────────────────────────────
    public function profile() {
        $user = auth('admin')->user();
        return view('admin.profile.index', compact('user'));
    }

    public function updateProfile(Request $request) {
        $user = auth('admin')->user();
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|unique:users,phone,'.$user->id,
        ]);
        $user->update($request->only('name','email','phone'));
        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);
        $user = auth('admin')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function updatePhoto(Request $request) {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = auth('admin')->user();

        // Delete old photo if exists
        if ($user->profile_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo' => $path]);

        return back()->with('success', 'Profile photo updated.');
    }
}

