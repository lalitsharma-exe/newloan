<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};

class PhoneVerificationController extends Controller
{
    public function __construct(private SmsService $sms) {}

    // ── 1. Show the OTP entry screen ─────────────────────────────────────────

    public function show()
    {
        $user = Auth::guard('borrower')->user();
        if (!$user) return redirect()->route('borrower.login');
        if ($user->phone_verified_at) return redirect()->route('borrower.dashboard');

        return view('borrower.auth.verify-phone', ['phone' => $user->phone]);
    }

    // ── 2. Resend OTP ─────────────────────────────────────────────────────────

    public function resend(Request $request)
    {
        $user = Auth::guard('borrower')->user();
        if (!$user) return redirect()->route('borrower.login');

        // Rate-limit: don't allow resend within 60s
        $existing = \DB::table('phone_otps')
            ->where('phone', $user->phone)
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($existing) {
            return back()->with('error', 'Please wait at least 60 seconds before requesting a new code.');
        }

        $this->sms->sendOtp($user->phone);

        return back()->with('success', 'A new code has been sent to ' . $user->phone);
    }

    // ── 3. Verify the OTP ────────────────────────────────────────────────────

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:6|max:6',
        ]);

        $user = Auth::guard('borrower')->user();
        if (!$user) return redirect()->route('borrower.login');

        $correct = $this->sms->verifyOtp($user->phone, $request->otp);

        if (!$correct) {
            // How many attempts left?
            $record = \DB::table('phone_otps')
                ->where('phone', $user->phone)
                ->first();

            $remaining = $record ? (5 - $record->attempts) : 0;

            return back()->withErrors([
                'otp' => $remaining > 0
                    ? "Incorrect code. {$remaining} attempt(s) remaining."
                    : 'Too many wrong attempts. Please request a new code.',
            ]);
        }

        // Mark phone as verified
        $user->update(['phone_verified_at' => now()]);

        Log::info('Phone verified', ['user_id' => $user->id, 'phone' => $user->phone]);

        return redirect()->route('borrower.dashboard')
            ->with('success', 'Phone verified! Welcome to MyLoan, ' . $user->name . '.');
    }
}
