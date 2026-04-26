<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth('borrower')->user();
        
        // Ensure user has a referral code
        if (!$user->referral_code) {
            $user->update(['referral_code' => \App\Models\User::generateReferralCode()]);
        }

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->get();

        $stats = [
            'pending'   => $referrals->where('status', 'pending')->count(),
            'validated' => $referrals->where('status', 'validated')->count(),
            'qualified' => $referrals->where('status', 'qualified')->count(),
            'paid'      => $referrals->where('status', 'paid')->count(),
            'total_earned' => $referrals->where('status', 'paid')->sum('amount'),
        ];

        return view('borrower.referrals.index', compact('user', 'referrals', 'stats'));
    }
}
