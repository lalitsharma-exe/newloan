<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, LoanProduct, User, Document};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class ApplicationController extends Controller
{
    /**
     * List all applications submitted by this agent.
     */
    public function index(Request $request)
    {
        $agent = auth('agent')->user();

        $query = LoanApplication::with(['user', 'loanProduct'])
            ->where('agent_id', $agent->id)
            ->where('status', '!=', 'draft');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(15);

        return view('agent.applications.index', compact('applications'));
    }

    /**
     * Show the details of a specific application (agent can only see their own).
     */
    public function show(LoanApplication $application)
    {
        $agent = auth('agent')->user();
        if ($application->agent_id !== $agent->id) {
            abort(403, 'You do not have access to this application.');
        }
        $application->load(['user', 'loanProduct', 'documents', 'employment']);
        return view('agent.applications.show', compact('application'));
    }

    /**
     * Show the client loan application capture form.
     */
    public function create()
    {
        $products = LoanProduct::active()->get();
        return view('agent.applications.create', compact('products'));
    }

    /**
     * Store a new client loan application captured by the agent.
     */
    public function store(Request $request)
    {
        $agent = auth('agent')->user();

        $request->validate([
            'first_name'     => 'required|string|max:60',
            'surname'        => 'required|string|max:60',
            'national_id'    => 'required|string|size:13',
            'date_of_birth'  => 'required|date|before:today',
            'cell_number'    => 'required|string|max:20',
            'email'          => 'nullable|email|max:100',
            'residential_address' => 'required|string|max:255',
            'village'        => 'nullable|string|max:120',
            'employer_name'  => 'required|string|max:120',
            'employee_number'=> 'required|string|max:50',
            'job_title'      => 'required|string|max:100',
            'monthly_net_salary' => 'required|numeric|min:0',
            'requested_amount'   => 'required|numeric|min:100|max:5000',
            'requested_term'     => 'required|integer|in:1,2,3',
            'loan_product_id'    => 'required|exists:loan_products,id',
            'national_id_photo'  => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'payslip_photo'      => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'selfie_photo'       => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // ── Affordability & 2× capital cap checks ──
        $product = LoanProduct::findOrFail($request->loan_product_id);
        $amount  = (float) $request->requested_amount;
        $term    = (int) $request->requested_term;
        $salary  = (float) $request->monthly_net_salary;

        // Calculate total repayable
        $rate           = $product->interest_rate / 100;
        $totalInterest  = round($amount * $rate * $term, 2);
        $initiationFee  = round($amount * ($product->initiation_fee_rate / 100), 2);
        $totalAdmin     = (float) $product->admin_fee_fixed * $term;
        $totalRepayable = $amount + $totalInterest + $initiationFee + $totalAdmin;
        $monthlyInstalment = round($totalRepayable / $term, 2);

        // 2× capital cap
        if ($totalRepayable > (2 * $amount)) {
            return back()->withInput()->withErrors([
                'requested_amount' => "This loan combination exceeds the 2× capital limit. Total repayable M" . number_format($totalRepayable, 2) . " exceeds M" . number_format(2 * $amount, 2) . ". Please select a different amount or term."
            ]);
        }

        // 30% affordability check
        $maxInstalment = $salary * 0.30;
        if ($monthlyInstalment > $maxInstalment) {
            return back()->withInput()->withErrors([
                'requested_amount' => "Monthly instalment M" . number_format($monthlyInstalment, 2) . " exceeds 30% of client's net salary (M" . number_format($maxInstalment, 2) . "). Maximum qualifying instalment: M" . number_format($maxInstalment, 2) . "."
            ]);
        }

        DB::beginTransaction();
        try {
            // Find or create borrower user
            $borrower = User::where('national_id', $request->national_id)->where('role', 'borrower')->first();
            if (!$borrower) {
                $borrower = User::create([
                    'name'        => trim($request->first_name . ' ' . $request->surname),
                    'email'       => $request->email ?? ($request->national_id . '@agent.myloan.co.ls'),
                    'phone'       => $request->cell_number,
                    'national_id' => $request->national_id,
                    'date_of_birth' => $request->date_of_birth,
                    'address'     => $request->residential_address,
                    'password'    => bcrypt(\Illuminate\Support\Str::random(16)),
                    'role'        => 'borrower',
                    'is_active'   => true,
                ]);
            }

            // Create loan application
            $application = LoanApplication::create([
                'user_id'            => $borrower->id,
                'agent_id'           => $agent->id,
                'loan_product_id'    => $request->loan_product_id,
                'status'             => 'submitted',
                'first_name'         => $request->first_name,
                'surname'            => $request->surname,
                'national_id'        => $request->national_id,
                'date_of_birth'      => $request->date_of_birth,
                'cell_number'        => $request->cell_number,
                'email'              => $request->email,
                'residential_address'=> $request->residential_address,
                'village'            => $request->village,
                'requested_amount'   => $amount,
                'requested_term'     => $term,
                'submitted_at'       => now(),
                'verification_status'=> 'pending',
            ]);

            // Create employment record
            $application->employment()->create([
                'employer_name'     => $request->employer_name,
                'employee_number'   => $request->employee_number,
                'job_title'         => $request->job_title,
                'monthly_net_salary'=> $salary,
            ]);

            // Upload documents
            $docs = [
                'national_id_photo' => 'National ID',
                'payslip_photo'     => 'Payslip',
                'selfie_photo'      => 'Selfie with ID',
            ];
            foreach ($docs as $field => $label) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store("agent-docs/{$application->id}", 'public');
                    Document::create([
                        'user_id'        => $borrower->id,
                        'application_id' => $application->id,
                        'type'           => $label,
                        'file_path'      => $path,
                        'status'         => 'pending',
                    ]);
                }
            }

            // Run the 5-step automated verification pipeline instantly
            $pipeline = app(\App\Services\VerificationPipelineService::class);
            $pipeline->verify($application);

            DB::commit();

            return redirect()->route('agent.applications.show', $application)
                ->with('success', "Application {$application->application_number} submitted successfully and verified through the automated 5-step pipeline.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Something went wrong. Please try again. ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Calculate loan schedule for real-time affordability display.
     */
    public function calculateSchedule(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:loan_products,id',
            'amount'     => 'required|numeric|min:100|max:5000',
            'term'       => 'required|integer|in:1,2,3',
            'salary'     => 'required|numeric|min:0',
        ]);

        $product = LoanProduct::findOrFail($request->product_id);
        $amount  = (float) $request->amount;
        $term    = (int) $request->term;
        $salary  = (float) $request->salary;

        $rate           = $product->interest_rate / 100;
        $totalInterest  = round($amount * $rate * $term, 2);
        $initiationFee  = round($amount * ($product->initiation_fee_rate / 100), 2);
        $totalAdmin     = (float) $product->admin_fee_fixed * $term;
        $totalRepayable = $amount + $totalInterest + $initiationFee + $totalAdmin;
        $instalment     = round($totalRepayable / $term, 2);
        $maxInstalment  = round($salary * 0.30, 2);
        $capExceeded    = $totalRepayable > (2 * $amount);

        return response()->json([
            'total_interest'    => $totalInterest,
            'initiation_fee'   => $initiationFee,
            'total_admin'      => $totalAdmin,
            'total_repayable'  => $totalRepayable,
            'instalment'       => $instalment,
            'max_instalment'   => $maxInstalment,
            'affordable'       => $instalment <= $maxInstalment,
            'cap_exceeded'     => $capExceeded,
            'cap_limit'        => 2 * $amount,
        ]);
    }
}
