<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AgentApplication, AgentProfile, User, LoanApplication};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Storage};
use Illuminate\Support\Str;
use App\Services\SmsService;

class AgentManagementController extends Controller
{
    /**
     * Agent Applications Queue — pending, approved, rejected
     */
    public function applications(Request $request)
    {
        $query = AgentApplication::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('agent_type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('national_id', 'like', "%{$s}%")
                  ->orWhere('application_ref', 'like', "%{$s}%")
                  ->orWhere('mobile_number', 'like', "%{$s}%");
            });
        }

        $applications = $query->latest()->paginate(20);

        $counts = [
            'pending'  => AgentApplication::where('status', 'pending')->count(),
            'approved' => AgentApplication::where('status', 'approved')->count(),
            'rejected' => AgentApplication::where('status', 'rejected')->count(),
            'total'    => AgentApplication::count(),
        ];

        return view('admin.agents.applications', compact('applications', 'counts'));
    }

    /**
     * View a single agent application detail
     */
    public function showApplication(AgentApplication $agentApplication)
    {
        return view('admin.agents.application-detail', ['application' => $agentApplication]);
    }

    /**
     * Approve an agent application:
     * 1. Create a user account with role='agent'
     * 2. Create an agent_profile record
     * @param  AgentApplication $agentApplication
     * @param  SmsService $smsService
     */
    public function approve(AgentApplication $agentApplication, SmsService $smsService)
    {
        if ($agentApplication->status === 'approved') {
            return back()->with('error', 'This application is already approved.');
        }

        DB::beginTransaction();
        try {
            // Create user account
            $password = Str::random(10);
            $user = User::create([
                'name'      => $agentApplication->first_name . ' ' . $agentApplication->last_name,
                'email'     => strtolower(str_replace(' ', '', $agentApplication->first_name)) . '.' . strtolower(str_replace(' ', '', $agentApplication->last_name)) . '@agent.myloan.co.ls',
                'phone'     => $agentApplication->mobile_number,
                'password'  => Hash::make($password),
                'role'      => 'agent',
                'is_active' => true,
            ]);

            // Create agent profile
            $profile = AgentProfile::create([
                'user_id'                  => $user->id,
                'agent_id'                 => AgentProfile::generateAgentID(),
                'agent_type'               => $agentApplication->agent_type,
                'shop_name'                => $agentApplication->shop_name,
                'shop_location'            => $agentApplication->shop_location,
                'business_type'            => $agentApplication->business_type,
                'payout_method'            => $agentApplication->payout_method,
                'payout_number_or_details' => $agentApplication->payout_number_or_details,
                'payout_account_name'      => $agentApplication->payout_account_name,
                'payout_bank_name'         => $agentApplication->payout_bank_name,
            ]);

            // Mark application approved
            $agentApplication->update(['status' => 'approved']);

            DB::commit();

            // Send activation SMS
            $smsMessage = "Congratulations! Your MyLoan Agent Application has been approved. Agent ID: {$profile->agent_id}\n\nLogin Credentials:\nEmail: {$user->email}\nPassword: {$password}\n\nLog in here: " . route('agent.login');
            $smsService->send($agentApplication->mobile_number, $smsMessage);

            return back()->with('success', "Agent {$profile->agent_id} approved! Login credentials generated & sent to applicant via SMS.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject an agent application
     */
    public function reject(Request $request, AgentApplication $agentApplication)
    {
        $request->validate(['feedback' => 'required|string|max:500']);

        $agentApplication->update([
            'status'         => 'rejected',
            'admin_feedback' => $request->feedback,
        ]);

        return back()->with('success', "Application {$agentApplication->application_ref} rejected.");
    }

    /**
     * Request additional documents from applicant
     */
    public function requestDocuments(Request $request, AgentApplication $agentApplication)
    {
        $request->validate(['feedback' => 'required|string|max:500']);

        $agentApplication->update([
            'status'         => 'documents_requested',
            'admin_feedback' => $request->feedback,
        ]);

        return back()->with('success', "Document request sent for {$agentApplication->application_ref}.");
    }

    /**
     * Active Agents List
     */
    public function activeAgents(Request $request)
    {
        $query = User::where('role', 'agent')
            ->with('agentProfile');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhereHas('agentProfile', fn($q2) => $q2->where('agent_id', 'like', "%{$s}%"));
            });
        }

        $agents = $query->latest()->paginate(20);

        $stats = [
            'total_active'    => User::where('role', 'agent')->where('is_active', true)->count(),
            'total_inactive'  => User::where('role', 'agent')->where('is_active', false)->count(),
            'total_shops'     => AgentProfile::where('agent_type', 'shop')->count(),
            'total_individual'=> AgentProfile::where('agent_type', 'individual')->count(),
        ];

        return view('admin.agents.active', compact('agents', 'stats'));
    }

    /**
     * View a specific agent's full profile and their submitted applications
     */
    public function showAgent(User $user)
    {
        if ($user->role !== 'agent') {
            abort(404);
        }

        $user->load('agentProfile');
        $applications = LoanApplication::where('agent_id', $user->id)
            ->with(['user', 'loanProduct'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_apps'   => LoanApplication::where('agent_id', $user->id)->where('status', '!=', 'draft')->count(),
            'approved'     => LoanApplication::where('agent_id', $user->id)->where('status', 'approved')->count(),
            'disbursed'    => LoanApplication::where('agent_id', $user->id)->where('status', 'disbursed')->count(),
            'declined'     => LoanApplication::where('agent_id', $user->id)->where('status', 'declined')->count(),
        ];

        return view('admin.agents.show', compact('user', 'applications', 'stats'));
    }

    /**
     * Toggle agent active/inactive status
     */
    public function toggleStatus(User $user)
    {
        if ($user->role !== 'agent') abort(404);
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Agent {$user->name} has been {$status}.");
    }
}
