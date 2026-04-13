<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, DB};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin() {
        if (Auth::guard('borrower')->check()) return redirect()->route('borrower.dashboard');
        return view('borrower.auth.login');
    }

    public function login(Request $request) {
        $request->validate(['login' => 'required', 'password' => 'required']);
        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        // Normalize phone number if it's a phone login
        if ($field === 'phone') {
            $login = $this->formatPhone($login);
        }

        $user  = User::where($field, $login)->where('role', 'borrower')->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['login' => 'Invalid credentials.']);
        }
        if (!$user->is_active) throw ValidationException::withMessages(['login' => 'Your account has been disabled.']);
        Auth::guard('borrower')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        return redirect()->intended(route('borrower.dashboard'));
    }

    public function showRegister() {
        return view('borrower.auth.register');
    }

   
    public function register(Request $request) {
        $request->validate([
            'name'        => 'required|string|max:150',
            'maiden_name' => 'nullable|string|max:150',
            'phone'       => 'required|string|max:30|unique:users,phone',
            'national_id' => 'required|string|max:50|unique:users,national_id',
            'email'       => 'nullable|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
        ]);
        $phone = $this->formatPhone($request->phone);
        if (\App\Models\User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Phone already registered.'])->withInput();
        }
        $user = \App\Models\User::create([
            'name'              => $request->name,
            'maiden_name'       => $request->maiden_name,
            'phone'             => $phone,
            'email'             => $request->filled('email') ? $request->email : null,
            'national_id'       => $request->national_id,
            'password'          => \Illuminate\Support\Facades\Hash::make($request->password),
            'role'              => 'borrower',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // Pre-fill draft application with registration data
        $nameParts = explode(' ', trim($user->name), 2);
        \App\Models\LoanApplication::create([
            'user_id'            => $user->id,
            'status'             => 'draft',
            'step'               => 1,
            'first_name'         => $nameParts[0] ?? '',
            'surname'            => $nameParts[1] ?? '',
            'cell_number'        => $user->phone,
            'national_id'        => $user->national_id,
            'maiden_name'        => $user->maiden_name,
            'email'              => $user->email,
        ]);

        \Illuminate\Support\Facades\Auth::guard('borrower')->login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        // Send OTP for phone verification
        app(\App\Services\SmsService::class)->sendOtp($phone);

        return redirect()->route('borrower.phone.verify.show')
            ->with('success', "Welcome, {$user->name}! Please verify your phone number to continue.");
    }

    public function verifyEmail(Request $request, string $token) {
        $record = DB::table('password_reset_tokens')->where('token', $token)->first();
        if (!$record) return redirect()->route('borrower.login')->with('error', 'Invalid verification link.');
        $user = User::where('email', $record->email)->where('role', 'borrower')->first();
        if ($user) { $user->update(['email_verified_at' => now()]); DB::table('password_reset_tokens')->where('email', $user->email)->delete(); }
        return redirect()->route('borrower.login')->with('success', 'Email verified. Please log in.');
    }

    public function logout(Request $request) {
        Auth::guard('borrower')->logout();
        return redirect()->route('borrower.login');
    }


    public function showForgotPassword() { return view('borrower.auth.forgot-password'); }

    public function sendResetLink(Request $request) {
        $request->validate(['phone' => 'required|string']);
        $user = User::where('phone', $this->formatPhone($request->phone))->where('role', 'borrower')->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(['email' => $user->phone], ['token' => Hash::make($token), 'created_at' => now()]);
            // TODO: SMS the token to $user->phone
        }
        return back()->with('success', 'If that phone is registered, a reset code has been sent.');
    }

    public function showResetPassword(Request $request, string $token) {
        return view('borrower.auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request) {
        $request->validate(['token' => 'required', 'phone' => 'required', 'password' => 'required|string|min:8|confirmed']);
        $phone  = $this->formatPhone($request->phone);
        $record = DB::table('password_reset_tokens')->where('email', $phone)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Invalid or expired reset code.']);
        }
        $user = User::where('phone', $phone)->where('role', 'borrower')->first();
        if (!$user) return back()->withErrors(['phone' => 'Account not found.']);
        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $phone)->delete();
        return redirect()->route('borrower.login')->with('success', 'Password reset. Please log in.');
    }

    private function formatPhone(?string $p): string {
        if (!$p) return '';
        $d = preg_replace('/[^0-9]/', '', $p);
        if (strlen($d) === 8) return '+266'.$d;
        if (strlen($d) === 12 && str_starts_with($d, '266')) return '+'.$d;
        if (str_starts_with($p, '+266')) return $p;
        return '+266'.substr($d, -8);
    }
}
