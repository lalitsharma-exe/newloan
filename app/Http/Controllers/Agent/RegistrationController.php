<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    /**
     * Show the public 4-step agent registration form.
     */
    public function showForm()
    {
        return view('agent.register.form');
    }

    /**
     * Handle submission of the agent application.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string|max:60|regex:/^[a-zA-Z\s\-]+$/',
            'last_name'     => 'required|string|max:60|regex:/^[a-zA-Z\s\-]+$/',
            'national_id'   => 'required|string|max:50',
            'mobile_number' => 'required|string|max:20',
            'agent_type'    => 'required|in:shop,individual',
            'shop_name'     => 'required_if:agent_type,shop|nullable|string|max:100',
            'shop_location' => 'required|string|max:120',
            'business_type' => 'required_if:agent_type,shop|nullable|string',
            'national_id_photo'    => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'selfie_holding_id'    => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'business_licence'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'payout_method'        => 'required|in:M-Pesa,EcoCash,Bank transfer',
            'payout_number_or_details' => 'required|string|max:100',
            'payout_account_name'  => 'required|string|max:80',
            'payout_bank_name'     => 'required_if:payout_method,Bank transfer|nullable|string',
            'agreement'            => 'accepted',
        ], [
            'first_name.regex'       => 'First name must contain letters only.',
            'last_name.regex'        => 'Last name must contain letters only.',
            'agreement.accepted'     => 'You must accept the agent agreement to proceed.',
        ]);

        // Duplicate check — national ID already registered
        $existing = AgentApplication::where('national_id', $request->national_id)->first();
        if ($existing) {
            $msg = match ($existing->status) {
                'pending'             => 'An application with this National ID is already pending review.',
                'approved'            => 'This National ID is already registered as an agent.',
                'documents_requested' => 'An application with this National ID requires additional documents. Check your SMS.',
                'rejected'            => ($existing->updated_at->diffInDays(now()) < 30)
                                         ? 'This National ID was recently rejected. You may reapply after ' . $existing->updated_at->addDays(30)->format('d M Y') . '.'
                                         : null,
                default               => null,
            };
            if ($msg) {
                return back()->withInput()->withErrors(['national_id' => $msg]);
            }
        }

        // Upload files
        $idPath     = $request->file('national_id_photo')->store('agent-applications/ids', 'public');
        $selfiePath = $request->file('selfie_holding_id')->store('agent-applications/selfies', 'public');
        $licencePath = null;
        if ($request->hasFile('business_licence')) {
            $licencePath = $request->file('business_licence')->store('agent-applications/licences', 'public');
        }

        $application = AgentApplication::create([
            'first_name'              => $request->first_name,
            'last_name'               => $request->last_name,
            'national_id'             => $request->national_id,
            'mobile_number'           => $request->mobile_number,
            'agent_type'              => $request->agent_type,
            'shop_name'               => $request->shop_name,
            'shop_location'           => $request->shop_location,
            'business_type'           => $request->business_type,
            'national_id_path'        => $idPath,
            'selfie_holding_id_path'  => $selfiePath,
            'business_licence_path'   => $licencePath,
            'payout_method'           => $request->payout_method,
            'payout_number_or_details'=> $request->payout_number_or_details,
            'payout_account_name'     => $request->payout_account_name,
            'payout_bank_name'        => $request->payout_bank_name,
            'status'                  => 'pending',
        ]);

        return view('agent.register.success', ['ref' => $application->application_ref]);
    }
}
