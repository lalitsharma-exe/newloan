<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, AgentProfile};

class DashboardController extends Controller
{
    public function index()
    {
        $agent   = auth('agent')->user();
        $profile = $agent->agentProfile;

        $stats = [
            'total_submitted'  => LoanApplication::where('agent_id', $agent->id)->where('status', '!=', 'draft')->count(),
            'approved'         => LoanApplication::where('agent_id', $agent->id)->where('status', 'approved')->count(),
            'under_review'     => LoanApplication::where('agent_id', $agent->id)->whereIn('status', ['submitted', 'under_review'])->count(),
            'declined'         => LoanApplication::where('agent_id', $agent->id)->where('status', 'declined')->count(),
            'disbursed'        => LoanApplication::where('agent_id', $agent->id)->where('status', 'disbursed')->count(),
            'total_earned'     => $profile->total_earned ?? 0,
            'pending_earnings' => $profile->pending_earnings ?? 0,
        ];

        $recentApps = LoanApplication::with(['user', 'loanProduct'])
            ->where('agent_id', $agent->id)
            ->where('status', '!=', 'draft')
            ->latest()
            ->take(10)
            ->get();

        return view('agent.dashboard.index', compact('stats', 'recentApps', 'profile'));
    }
}
