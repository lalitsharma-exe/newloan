<?php
namespace App\Http\Controllers\Agent;

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
        if (Auth::guard('agent')->check()) return redirect()->route('agent.dashboard');
        return view('agent.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (Auth::guard('agent')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::guard('agent')->user();
            if (!$user->is_active) {
                Auth::guard('agent')->logout();
                throw ValidationException::withMessages(['email' => 'Your account has been disabled.']);
            }
            if ($user->role !== 'agent') {
                Auth::guard('agent')->logout();
                throw ValidationException::withMessages(['email' => 'You do not have agent access.']);
            }
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('agent.dashboard'))->with('success', "Welcome back, {$user->name}!");
        }
        throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();
        return redirect()->route('agent.login')->with('success', 'You have been logged out.');
    }

    public function showForgotPassword()
    {
        if (Auth::guard('agent')->check()) return redirect()->route('agent.dashboard');
        return view('agent.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->where('role', 'agent')->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
            return back()->with('success', 'Reset link sent. (Dev token: '.$token.')');
        }
        return back()->with('success', 'If that email exists, a reset link has been sent.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('agent.auth.reset-password', ['token' => $token, 'email' => $request->email]);
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
        $user = User::where('email', $request->email)->where('role', 'agent')->first();
        if (!$user) return back()->withErrors(['email' => 'No agent account found.']);
        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return redirect()->route('agent.login')->with('success', 'Password reset. Please log in.');
    }
}
