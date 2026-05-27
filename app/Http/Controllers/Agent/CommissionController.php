<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, AgentProfile};

class CommissionController extends Controller
{
    public function index()
    {
        $agent   = auth('agent')->user();
        $profile = $agent->agentProfile;

        // Build the commission log from applications
        $applications = LoanApplication::with(['user'])
            ->where('agent_id', $agent->id)
            ->whereIn('status', ['approved', 'disbursed', 'active', 'paid_off', 'closed', 'overdue'])
            ->latest()
            ->paginate(20);

        // Calculate commission for each application
        $commissionLog = $applications->through(function ($app) {
            $loan = $app->loan;
            $firstPayment = $loan?->payments()->where('status', 'completed')->orderBy('created_at')->first();

            return (object)[
                'application'    => $app,
                'client_name'    => $app->applicant_name,
                'loan_amount'    => $app->requested_amount,
                'submitted_at'   => $app->submitted_at,
                'status'         => $firstPayment ? 'earned' : ($app->status === 'disbursed' ? 'awaiting_first_payment' : 'pending'),
                'commission'     => $firstPayment ? 50.00 : 0.00,
                'earned_at'      => $firstPayment?->created_at,
            ];
        });

        $totalEarned   = $profile->total_earned ?? 0;
        $pendingAmount = $profile->pending_earnings ?? 0;

        return view('agent.commissions.index', compact('commissionLog', 'totalEarned', 'pendingAmount'));
    }
}
