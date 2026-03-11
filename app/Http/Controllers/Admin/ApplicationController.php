<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, LoanProduct, User};
use App\Services\Admin\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $svc) {}

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
            'national_id'        => 'nullable|string|max:50',
            'date_of_birth'      => 'nullable|date',
            'gender'             => 'nullable|string|max:20',
            'marital_status'     => 'nullable|string|max:20',
            'cell_number'        => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'assigned_officer_id'=> 'nullable|exists:users,id',
            'admin_notes'        => 'nullable|string|max:1000',
        ]);

        $data['application_number'] = 'APP-' . str_pad(
            LoanApplication::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT
        );
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
        $officers = User::where('role','loan_officer')->where('is_active',true)->orderBy('name')->get();
        return view('admin.applications.show', compact('application','officers'));
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
        $this->svc->approve($application, $request->all(), auth('admin')->user());
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
            'content'     => $request->content,
            'is_internal' => $request->boolean('is_internal', true),
        ]);
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Note added.');
    }

    public function deleteNote(Request $request, LoanApplication $application, $note)
    {
        $application->notes()->findOrFail($note)->delete();
        return redirect()->route('admin.applications.show', $application)
                         ->with('success', 'Note deleted.');
    }

    public function schedulePreview(Request $request, LoanApplication $application)
    {
        $amount   = $request->input('amount', $application->approved_amount ?? $application->requested_amount);
        $rate     = $request->input('rate', $application->approved_interest_rate ?? $application->loanProduct?->interest_rate ?? 0) / 100;
        $term     = $request->input('term', $application->approved_term ?? $application->requested_term);

        $schedule = [];
        $balance  = $amount;
        $monthly  = $rate > 0
            ? $amount * ($rate * pow(1+$rate,$term)) / (pow(1+$rate,$term)-1)
            : $amount / $term;

        for ($i = 1; $i <= $term; $i++) {
            $interest  = round($balance * $rate, 2);
            $principal = round($monthly - $interest, 2);
            $balance   = max(0, round($balance - $principal, 2));
            $schedule[] = [
                'no'        => $i,
                'payment'   => round($monthly, 2),
                'principal' => $principal,
                'interest'  => $interest,
                'balance'   => $balance,
            ];
        }

        return response()->json(['schedule' => $schedule, 'monthly' => round($monthly, 2)]);
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
}