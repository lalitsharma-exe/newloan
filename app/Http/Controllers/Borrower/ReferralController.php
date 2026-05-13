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

        // Backfill missing amounts for qualified/paid referrals (legacy fix)
        Referral::where('referrer_id', $user->id)
            ->whereIn('status', ['qualified', 'payout_pending', 'paid'])
            ->where(function($q) { $q->whereNull('amount')->orWhere('amount', 0); })
            ->update(['amount' => 50]);

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->get();

        $stats = [
            'pending'      => $referrals->where('status', 'pending')->count(),
            'validated'    => $referrals->where('status', 'validated')->count(),
            'qualified'    => $referrals->where('status', 'qualified')->count(),
            'paid'         => $referrals->where('status', 'paid')->count(),
            'total_earned' => $referrals->whereIn('status', ['qualified', 'payout_pending', 'paid'])->sum('amount'),
            'balance'      => $referrals->where('status', 'qualified')->sum('amount'),
        ];

        return view('borrower.referrals.index', compact('user', 'referrals', 'stats'));
    }

    public function requestPayout(Request $request)
    {
        $user = auth('borrower')->user();
        
        // Calculate current qualified balance
        $balance = Referral::where('referrer_id', $user->id)
            ->where('status', 'qualified')
            ->sum('amount');

        if ($balance < 250) {
            return back()->with('error', 'Minimum payout threshold is M250.00. You currently have M' . number_format($balance, 2));
        }

        // Update all qualified referrals to payout_pending
        Referral::where('referrer_id', $user->id)
            ->where('status', 'qualified')
            ->update([
                'status' => 'payout_pending',
                'payout_method' => $request->payout_method ?? 'M-Pesa',
            ]);

        // TODO: Notify Admin of payout request

        return back()->with('success', 'Your payout request for M' . number_format($balance, 2) . ' has been submitted for approval.');
    }
}
