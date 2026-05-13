<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, LoanProduct, User};
use App\Services\Admin\ApplicationService;
use App\Services\Admin\ScoringService;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function __construct(
        private ApplicationService $svc,
        private ScoringService $scoring
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status','product','date_from','date_to','search']);
        return view('admin.applications.index', [
            'applications' => $this->svc->getPaginated($filters),
            'stats'        => $this->svc->getStats(),
            'products'     => LoanProduct::active()->get(),
            'filters'      => $filters,
        ]);
    }

    public function create()
    {
        $products  = LoanProduct::active()->get();
        $borrowers = User::where('role','borrower')->where('is_active',true)->orderBy('name')->get();
        $officers  = User::where('role','loan_officer')->where('is_active',true)->orderBy('name')->get();
        return view('admin.applications.create', compact('products','borrowers','officers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'            => 'required|exists:users,id',
            'loan_product_id'    => 'required|exists:loan_products,id',
            'requested_amount'   => 'required|numeric|min:1',
            'requested_term'     => 'required|integer|min:1|max:120',
            'loan_purpose'       => 'required|string|max:500',
            'payout_method'      => 'required|string',
            'collection_method'  => 'required|string',
            'title'              => 'nullable|string|max:10',
            'first_name'         => 'required|string|max:100',
            'surname'            => 'required|string|max:100',
            'maiden_name'        => 'nullable|string|max:100',
            'national_id'        => 'nullable|string|max:50',
            'date_of_birth'      => 'nullable|date',
            'gender'             => 'nullable|string|max:20',
            'marital_status'     => 'nullable|string|max:20',
            'cell_number'        => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'assigned_officer_id'=> 'nullable|exists:users,id',
            'admin_notes'        => 'nullable|string|max:1000',
        ]);

        $data['status']       = 'submitted';
        $data['submitted_at'] = now();

        $application = LoanApplication::create($data);

        if (!empty($data['admin_notes'])) {
            $application->notes()->create([
                'created_by'  => auth('admin')->id(),
                'type'        => 'general',
                'content'     => $data['admin_notes'],
                'is_internal' => true,
            ]);
        }

        return redirect()->route('admin.applications.show', $application)
                         ->with('success', "Application {$application->application_number} created successfully.");
    }

    public function show(LoanApplication $application)
    {
        $application->load([
            'user','loanProduct','documents','notes.createdBy',
            'affordability','employment','bankDetails','nextOfKin',
            'creditReport','loan','assignedOfficer',
        ]);

        $credit = $this->scoring->calculateCreditScore($application);
        $fraud  = $this->scoring->calculateFraudScore($application);
        $decision = $this->scoring->getDecision($credit['total'], $fraud['total'], $fraud['has_default'] ?? false);

        // Persist scores
        $application->update([
            'credit_score' => $credit['total'],
            'fraud_score'  => $fraud['total'],
        ]);

        $officers = User::where('role','loan_officer')->where('is_active',true)->orderBy('name')->get();
        $declineCategories = \App\Models\DeclineCategory::orderBy('display_order')->get();
        return view('admin.applications.show', compact('application','officers', 'credit', 'fraud', 'decision', 'declineCategories'));
    }

    public function updateAffordability(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'monthly_earnings'         => 'required|numeric|min:0',
            'tax_deduction'            => 'nullable|numeric|min:0',
            'existing_loans_deduction' => 'nullable|numeric|min:0',
            'other_deductions'         => 'nullable|numeric|min:0',
            'transport'                => 'nullable|numeric|min:0',
            'groceries'                => 'nullable|numeric|min:0',
            'utilities'                => 'nullable|numeric|min:0',
            'rent'                     => 'nullable|numeric|min:0',
            'education'                => 'nullable|numeric|min:0',
            'communication'            => 'nullable|numeric|min:0',
            'other_insurance'          => 'nullable|numeric|min:0',
            'medical'                  => 'nullable|numeric|min:0',
            'other_loan_repayments'    => 'nullable|numeric|min:0',
            'family_support'           => 'nullable|numeric|min:0',
            'entertainment'            => 'nullable|numeric|min:0',
            'other_expenses'           => 'nullable|numeric|min:0',
        ]);

        $afford = $application->affordability()->updateOrCreate(
            ['application_id' => $application->id],
            $data
        );

        // Ensure net salary and disposable income are correctly updated after edits
        $afford->recalculate();
        $afford->save();

        return redirect()->back()->with('success', 'Affordability data updated successfully.');
    }

    public function updateEmployment(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'employer_name'     => 'required|string|max:200',
            'employer_type'     => 'required|string|max:50',
            'employer_category' => 'nullable|string|max:100',
            'job_title'         => 'nullable|string|max:100',
            'department'        => 'nullable|string|max:100',
            'employment_number' => 'nullable|string|max:50',
            'contact_number'    => 'nullable|string|max:20',
            'employment_expiry_date' => 'nullable|date',
        ]);

        $application->employment()->updateOrCreate(['application_id' => $application->id], $data);

        return redirect()->back()->with('success', 'Employment details updated successfully.');
    }

    public function updateBankDetails(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'bank_name'           => 'required|string|max:100',
            'branch_name'         => 'nullable|string|max:100',
            'branch_code'         => 'nullable|string|max:20',
            'account_holder_name' => 'required|string|max:200',
            'account_number'      => 'required|string|max:50',
            'account_type'        => 'required|string|max:50',
        ]);

        $application->bankDetails()->updateOrCreate(['application_id' => $application->id], $data);

        return redirect()->back()->with('success', 'Bank details updated successfully.');
    }

    public function updatePersonal(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'title'          => 'nullable|string|max:10',
            'first_name'     => 'required|string|max:100',
            'surname'        => 'required|string|max:100',
            'maiden_name'    => 'nullable|string|max:100',
            'national_id'    => 'required|string|max:50',
            'date_of_birth'  => 'nullable|date',
            'gender'         => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:20',
            'cell_number'    => 'required|string|max:20',
            'email'          => 'nullable|email|max:100',
        ]);

        $application->update($data);

        return redirect()->back()->with('success', 'Personal information updated successfully.');
    }

    public function updateAddress(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'residential_address' => 'required|string|max:255',
            'village'             => 'required|string|max:100',
            'town'                => 'required|string|max:100',
            'district'            => 'required|string|max:50',
            'address_duration'    => 'nullable|string|max:50',
            'residence_type'      => 'required|string|max:50',
            'nearest_landmark'    => 'required|string|max:255',
            'home_directions'     => 'required|string|max:500',
            'gps_latitude'        => 'nullable|string|max:50',
            'gps_longitude'       => 'nullable|string|max:50',
        ]);

        $application->update($data);

        return redirect()->back()->with('success', 'Address details updated successfully.');
    }

    public function updateLoanRequest(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'requested_amount'  => 'required|numeric|min:1',
            'requested_term'    => 'required|integer|min:1',
            'loan_purpose'      => 'nullable|string|max:500',
            'payout_method'     => 'required|string|max:50',
            'collection_method' => 'required|string|max:50',
            'salary_payday'     => 'required|integer|min:1|max:31',
        ]);

        $application->update($data);

        return redirect()->back()->with('success', 'Loan request details updated successfully.');
    }

    public function verifyPayment(Request $request, LoanApplication $application)
    {
        abort_if($application->status !== 'draft', 403);
        
        $application->update([
            'status'       => 'submitted',
            'fee_paid'     => true,
            'submitted_at' => now(),
        ]);

        $application->notes()->create([
            'created_by'  => auth('admin')->id(),
            'type'        => 'status',
            'content'     => 'Application manually verified and submitted by admin.',
            'is_internal' => true,
        ]);

        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Application payment verified and submitted successfully.');
    }

    public function approve(Request $request, LoanApplication $application)
    {
        $request->validate([
            'approved_amount'   => 'required|numeric|min:1',
            'approved_term'     => 'required|integer|min:1',
            'interest_rate'     => 'required|numeric|min:0',
            'disbursement_date' => 'required|date|after_or_equal:today',
            'notes'             => 'nullable|string',
        ]);

        try {
            $this->svc->approve($application, $request->all(), auth('admin')->user());
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Application approved and loan created successfully.');
    }

    public function decline(Request $request, LoanApplication $application)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $this->svc->decline($application, $request->reason, auth('admin')->user());
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Application declined.');
    }

    public function hold(Request $request, LoanApplication $application)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $this->svc->hold($application, $request->reason, auth('admin')->user());
        return redirect()->route('admin.applications.show', $application)
                         ->with('info', 'Application placed on hold.');
    }

    public function reinstate(Request $request, LoanApplication $application)
    {
        $application->update(['status' => 'submitted', 'admin_notes' => null]);
        $application->notes()->create([
            'created_by'  => auth('admin')->id(),
            'type'        => 'status',
            'content'     => 'Application reinstated by admin.',
            'is_internal' => true,
        ]);
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Application reinstated.');
    }

    public function requestInfo(Request $request, LoanApplication $application)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $this->svc->requestInfo($application, $request->message, auth('admin')->user());
        return redirect()->route('admin.applications.show', $application)
                         ->with('info', 'Information request sent to borrower.');
    }

    public function markUnderReview(LoanApplication $application)
    {
        $application->update(['status' => 'under_review', 'reviewed_at' => now()]);
        $application->notes()->create([
            'created_by'  => auth('admin')->id(),
            'type'        => 'status',
            'content'     => 'Application moved to under review.',
            'is_internal' => true,
        ]);
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Status updated to Under Review.');
    }

    public function overrideTerms(Request $request, LoanApplication $application)
    {
        $request->validate([
            'loan_amount'   => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'term_months'   => 'required|integer|min:1',
        ]);
        $this->svc->overrideLoanTerms($application, $request->all(), auth('admin')->user());
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Loan terms updated.');
    }

    public function setRiskScore(Request $request, LoanApplication $application)
    {
        $request->validate(['risk_score' => 'required|integer|min:0|max:1000']);
        $application->update(['risk_score' => $request->risk_score]);
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Risk score updated.');
    }

    public function autoRiskScore(LoanApplication $application, RiskScoringService $riskService)
    {
        $result = $riskService->calculate($application);
        $application->update(['risk_score' => $result['score']]);
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Risk score auto-calculated: ' . $result['score'] . ' (' . $result['label'] . ')')
                         ->with('risk_breakdown', $result['breakdown']);
    }

    public function addNote(Request $request, LoanApplication $application)
    {
        $request->validate([
            'content'     => 'required|string|max:2000',
            'type'        => 'nullable|string|max:50',
            'is_internal' => 'nullable|boolean',
        ]);
        $application->notes()->create([
            'created_by'  => auth('admin')->id(),
            'type'        => $request->input('type', 'general'),
            'content'     => $request->input('content'),
            'is_internal' => $request->boolean('is_internal', true),
        ]);
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Note added.');
    }

    public function getMessages(LoanApplication $application)
    {
        $messages = $application->messages()->orderBy('created_at', 'asc')->get()->map(function($msg) {
            $senderName = 'Admin';
            if ($msg->sender_type === 'borrower') {
                $senderName = $msg->application->first_name;
            } else if ($msg->sender_type === 'admin') {
                $admin = \App\Models\User::find($msg->sender_id);
                $senderName = $admin ? $admin->name : 'Admin';
            }
            return [
                'id' => $msg->id,
                'content' => $msg->message,
                'sender_type' => $msg->sender_type,
                'sender_name' => $senderName,
                'sender_initial' => substr($senderName, 0, 1),
                'created_at' => $msg->created_at->format('d M H:i'),
            ];
        });
        return response()->json($messages);
    }

    public function sendMessage(Request $request, LoanApplication $application)
    {
        $request->validate(['content' => 'required|string|max:2000']);
        
        $msg = $application->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => auth('admin')->id(),
            'message' => $request->input('content'),
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteNote(Request $request, LoanApplication $application, $note)
    {
        $application->notes()->findOrFail($note)->delete();
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Note deleted.');
    }

    public function schedulePreview(Request $request, LoanApplication $application)
    {
        $product  = $application->loanProduct;
        $amount   = (float) $request->input('amount', $application->approved_amount ?? $application->requested_amount ?? 0);
        $rateP    = (float) $request->input('rate',   $application->approved_interest_rate ?? $product?->interest_rate ?? 15);
        $term     = (int)   $request->input('term',   $application->approved_term ?? $application->requested_term ?? 1);

        // ── FLAT interest (same every month on original principal) ──
        $rate            = $rateP / 100;
        $initiationRate  = ($product?->initiation_fee_rate ?? 40) / 100;
        $adminPerMonth   = (float)($product?->admin_fee_fixed ?? 50);

        $totalInterest   = round($amount * $rate * $term, 2);
        $totalInitiation = round($amount * $initiationRate, 2);
        $totalRepay      = $amount + $totalInterest + $totalInitiation + ($adminPerMonth * $term);
        $monthly         = round($totalRepay / $term, 2);

        $principalPerMonth  = round($amount / $term, 2);
        $interestPerMonth   = round($amount * $rate, 2);
        $initiationPerMonth = round($totalInitiation / $term, 2);

        $schedule        = [];
        $remainPrincipal = $amount;

        for ($i = 1; $i <= $term; $i++) {
            $isLast = ($i === $term);
            $prin   = $isLast ? round($remainPrincipal, 2) : $principalPerMonth;
            $init   = $isLast ? round($totalInitiation - $initiationPerMonth * ($term - 1), 2) : $initiationPerMonth;
            $total  = round($prin + $interestPerMonth + $adminPerMonth + $init, 2);
            $remainPrincipal = max(0, round($remainPrincipal - $prin, 2));

            $schedule[] = [
                'no'             => $i,
                'principal'      => $prin,
                'interest'       => $interestPerMonth,
                'initiation_fee' => $init,
                'admin_fee'      => $adminPerMonth,
                'payment'        => $total,
                'balance'        => $remainPrincipal,
            ];
        }

        return response()->json([
            'schedule'        => $schedule,
            'monthly'         => $monthly,
            'total_repay'     => $totalRepay,
            'total_interest'  => $totalInterest,
            'total_initiation'=> $totalInitiation,
            'total_admin'     => $adminPerMonth * $term,
            'cash_received'   => $amount,
        ]);
    }

    public function export(Request $request)
    {
        $filters      = $request->only(['status','product','date_from','date_to','search']);
        $applications = $this->svc->getPaginated($filters, 9999);

        $csv = "App#,Applicant,Email,Product,Amount,Term,Status,Submitted\n";
        foreach ($applications as $a) {
            $csv .= implode(',', [
                $a->application_number,
                '"'.$a->applicant_name.'"',
                $a->email ?? $a->user?->email,
                '"'.($a->loanProduct?->name ?? '—').'"',
                $a->requested_amount,
                $a->requested_term,
                $a->status,
                $a->submitted_at?->format('Y-m-d') ?? $a->created_at->format('Y-m-d'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="applications-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
    public function uploadDocument(\Illuminate\Http\Request $request, \App\Models\LoanApplication $application)
    {
        $request->validate([
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type'  => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file')->store(
            'documents/' . $application->user_id, 'public'
        );

        \App\Models\Document::create([
            'user_id'        => $application->user_id,
            'application_id' => $application->id,
            'type'           => $request->type,
            'filename'       => $request->file('file')->getClientOriginalName(),
            'original_name'  => $request->file('file')->getClientOriginalName(),
            'path'           => $path,
            'status'         => 'pending',
            'notes'          => $request->notes,
            'uploaded_by'    => auth('admin')->id(), // track who uploaded
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function experianTemplate(\App\Models\LoanApplication $application)
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Experian_Enquiry_{$application->national_id}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $callback = function() use ($application) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Enquiry Date',
                'ID Number',
                'First Name',
                'Surname',
                'Maiden Name',
                'Date of Birth',
                'Gender',
                'Mobile Number',
                'Address Line 1',
                'Address Line 2',
                'Address Line 3',
                'Address Line 4',
                'Employer Name',
                'Gross Salary',
                'Net Salary'
            ]);
            
            fputcsv($file, [
                now()->format('Y-m-d'),
                $application->national_id,
                $application->first_name,
                $application->surname,
                $application->maiden_name,
                $application->date_of_birth?->format('Y-m-d'),
                $application->gender,
                $application->cell_number,
                $application->residential_address,
                $application->village,
                $application->town,
                $application->district,
                $application->employment?->employer_name,
                $application->affordability?->monthly_earnings,
                $application->affordability?->net_salary
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
