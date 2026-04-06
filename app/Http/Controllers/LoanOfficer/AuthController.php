<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, DB};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('officer')->check()) return redirect()->route('officer.dashboard');
        return view('officer.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (Auth::guard('officer')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::guard('officer')->user();
            if (!$user->is_active) {
                Auth::guard('officer')->logout();
                throw ValidationException::withMessages(['email' => 'Your account has been disabled.']);
            }
            if ($user->role !== 'loan_officer') {
                Auth::guard('officer')->logout();
                throw ValidationException::withMessages(['email' => 'You do not have loan officer access.']);
            }
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('officer.dashboard'))->with('success', "Welcome back, {$user->name}!");
        }
        throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('officer')->logout();
        return redirect()->route('officer.login')->with('success', 'You have been logged out.');
    }


    public function showForgotPassword()
    {
        if (Auth::guard('officer')->check()) return redirect()->route('officer.dashboard');
        return view('officer.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->where('role', 'loan_officer')->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
            // Production: Mail::to($user)->send(new OfficerPasswordReset($token));
            return back()->with('success', 'Reset link sent. (Dev token: '.$token.')');
        }
        return back()->with('success', 'If that email exists, a reset link has been sent.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('officer.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }
        $user = User::where('email', $request->email)->where('role', 'loan_officer')->first();
        if (!$user) return back()->withErrors(['email' => 'No officer account found.']);
        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return redirect()->route('officer.login')->with('success', 'Password reset. Please log in.');
    }

    public function profile()
    {
        return view('officer.profile.index', ['user' => auth('officer')->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth('officer')->user();
        $request->validate([
            'name'  => 'required|string|max:150',
            'phone' => 'nullable|string|max:30|unique:users,phone,'.$user->id,
        ]);
        $user->update($request->only('name', 'phone'));
        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);
        $user = auth('officer')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = auth('officer')->user();
        if ($user->profile_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
        }
        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo' => $path]);
        return back()->with('success', 'Profile photo updated.');
    }
}
