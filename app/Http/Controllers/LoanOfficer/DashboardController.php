<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, User, Document};

class DashboardController extends Controller
{
    public function index()
    {
        $officer = auth('officer')->user();

        $stats = [
            'assigned_total'    => LoanApplication::where('assigned_officer_id', $officer->id)->where('status', '!=', 'draft')->count(),
            'pending_review'    => LoanApplication::where('assigned_officer_id', $officer->id)->whereIn('status', ['submitted', 'info_requested'])->count(),
            'under_review'      => LoanApplication::where('assigned_officer_id', $officer->id)->where('status', 'under_review')->count(),
            'completed_month'   => LoanApplication::where('assigned_officer_id', $officer->id)->whereIn('status', ['approved','declined','disbursed'])->whereMonth('decided_at', now()->month)->whereYear('decided_at', now()->year)->count(),
            'my_clients'        => User::where('assigned_officer_id', $officer->id)->where('role', 'borrower')->count(),
            'docs_pending'      => \App\Models\Document::whereHas('application', fn($q) => $q->where('assigned_officer_id', $officer->id))->where('status', 'pending')->count(),
        ];

        $myApplications = LoanApplication::with(['user', 'loanProduct'])
            ->where('assigned_officer_id', $officer->id)
            ->where('status', '!=', 'draft')
            ->latest()
            ->take(8)
            ->get();

        $pendingDocs = \App\Models\Document::with(['application.user'])
            ->whereHas('application', fn($q) => $q->where('assigned_officer_id', $officer->id))
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $myClients = User::where('assigned_officer_id', $officer->id)
            ->where('role', 'borrower')
            ->with(['loanApplications' => fn($q) => $q->latest()->take(1)])
            ->latest()
            ->take(5)
            ->get();

        return view('officer.dashboard.index', compact('stats', 'myApplications', 'pendingDocs', 'myClients'));
    }

    public function stats()
    {
        $officer = auth('officer')->user();
        return response()->json([
            'assigned_total'  => LoanApplication::where('assigned_officer_id', $officer->id)->where('status', '!=', 'draft')->count(),
            'pending_review'  => LoanApplication::where('assigned_officer_id', $officer->id)->whereIn('status', ['submitted', 'info_requested'])->count(),
            'under_review'    => LoanApplication::where('assigned_officer_id', $officer->id)->where('status', 'under_review')->count(),
            'docs_pending'    => \App\Models\Document::whereHas('application', fn($q) => $q->where('assigned_officer_id', $officer->id))->where('status', 'pending')->count(),
        ]);
    }

    public function myApplications()
    {
        $officer = auth('officer')->user();
        $apps = LoanApplication::with(['user', 'loanProduct'])
            ->where('assigned_officer_id', $officer->id)
            ->where('status', '!=', 'draft')
            ->latest()->take(10)->get();
        return response()->json($apps);
    }
}
