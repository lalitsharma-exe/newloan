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
            // Step 1: Personal
            'title'                 => 'required|string|max:10',
            'first_name'            => 'required|string|max:60',
            'surname'               => 'required|string|max:60',
            'maiden_name'           => 'nullable|string|max:60',
            'national_id'           => 'required|string|max:50',
            'date_of_birth'         => 'required|date|before:today',
            'gender'                => 'required|string|in:male,female',
            'marital_status'        => 'required|string|in:single,married,divorced,widowed',
            'cell_number'           => 'required|string|max:20',
            'email'                 => 'nullable|email|max:100',

            // Step 2: Address
            'residential_address'   => 'required|string|max:255',
            'village'               => 'required|string|max:120',
            'town'                  => 'required|string|max:120',
            'district'              => 'required|string|max:120',
            'address_duration'      => 'required|string|max:60',
            'residence_type'        => 'required|string|max:60',
            'nearest_landmark'      => 'required|string|max:255',
            'home_directions'       => 'required|string',
            'gps_latitude'          => 'nullable|numeric',
            'gps_longitude'         => 'nullable|numeric',

            // Step 3: Employment
            'employer_name'         => 'required|string|max:120',
            'employer_type'         => 'required|string|in:government,private,sme',
            'employer_category'     => 'nullable|string|max:120',
            'job_title'             => 'required|string|max:100',
            'department'            => 'nullable|string|max:100',
            'employment_number'     => 'required|string|max:50',
            'contact_number'        => 'required|string|max:20',
            'employment_expiry_date'=> 'nullable|date',

            // Step 4: Bank
            'bank_name'             => 'required|string|max:120',
            'branch_name'           => 'required|string|max:120',
            'branch_code'           => 'nullable|string|max:50',
            'account_holder_name'   => 'required|string|max:120',
            'account_number'        => 'required|string|max:50',
            'account_type'          => 'required|string|in:savings,cheque',

            // Step 5: Kin
            'nok_1_first_name'      => 'required|string|max:60',
            'nok_1_last_name'       => 'required|string|max:60',
            'nok_1_relationship'    => 'required|string|max:50',
            'nok_1_phone'           => 'required|string|max:20',

            // Step 6: Affordability
            'monthly_earnings'          => 'required|numeric|min:0',
            'tax_deduction'             => 'nullable|numeric|min:0',
            'existing_loans_deduction'  => 'nullable|numeric|min:0',
            'pension_deduction'         => 'nullable|numeric|min:0',
            'insurance_deduction'       => 'nullable|numeric|min:0',
            'subscriptions_deduction'   => 'nullable|numeric|min:0',
            'other_deductions'          => 'nullable|numeric|min:0',

            'rent'                      => 'nullable|numeric|min:0',
            'groceries'                 => 'nullable|numeric|min:0',
            'transport'                 => 'nullable|numeric|min:0',
            'utilities'                 => 'nullable|numeric|min:0',
            'education'                 => 'nullable|numeric|min:0',
            'communication'             => 'nullable|numeric|min:0',
            'medical'                   => 'nullable|numeric|min:0',
            'other_loan_repayments'     => 'nullable|numeric|min:0',
            'other_expenses'            => 'nullable|numeric|min:0',

            // Step 7: Loan Details
            'loan_product_id'       => 'required|exists:loan_products,id',
            'requested_amount'      => 'required|numeric|min:100',
            'requested_term'        => 'required|integer|min:1',
            'loan_purpose'          => 'required|string',
            'payout_method'         => 'required|string|in:bank_transfer,mobile_money',
            'collection_method'     => 'required|string|in:salary_deduction,card_payment,debit_order',
            'salary_payday'         => 'required|integer|between:1,31',

            // Step 8: Docs
            'national_id_photo'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'payslip_photo'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'bank_statement_photo'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'selfie_photo'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Step 9: Signature
            'signature_data'        => 'required|string',
        ]);

        $product = LoanProduct::findOrFail($request->loan_product_id);
        $amount  = (float) $request->requested_amount;
        $term    = (int) $request->requested_term;
        
        // Income / Expenses validation
        $gross = (float) $request->monthly_earnings;
        $ded = (float)($request->tax_deduction ?? 0)
             + (float)($request->existing_loans_deduction ?? 0)
             + (float)($request->pension_deduction ?? 0)
             + (float)($request->insurance_deduction ?? 0)
             + (float)($request->subscriptions_deduction ?? 0)
             + (float)($request->other_deductions ?? 0);
        $net = $gross - $ded;

        // Schedule calculations
        $rate           = $product->interest_rate / 100;
        $totalInterest  = round($amount * $rate * $term, 2);
        $initiationFee  = round($amount * ($product->initiation_fee_rate / 100), 2);
        $totalAdmin     = (float) $product->admin_fee_fixed * $term;
        $totalRepayable = $amount + $totalInterest + $initiationFee + $totalAdmin;
        $monthlyInstalment = round($totalRepayable / $term, 2);

        // 2× capital limit check
        if ($totalRepayable > (2 * $amount)) {
            return back()->withInput()->with('error', "Total repayment of M" . number_format($totalRepayable, 2) . " violates the 2× capital limit. Maximum allowable total repayment is M" . number_format(2 * $amount, 2) . ".");
        }

        // 30% Net Salary Affordability check
        $maxInstalment = $net * 0.30;
        if ($monthlyInstalment > $maxInstalment) {
            return back()->withInput()->with('error', "Estimated monthly instalment (M" . number_format($monthlyInstalment, 2) . ") exceeds the 30% Net Salary limit (M" . number_format($maxInstalment, 2) . "). Please select a different product, lower amount or increase the term.");
        }

        DB::beginTransaction();
        try {
            // Find or create borrower profile
            $borrower = User::where('national_id', $request->national_id)->where('role', 'borrower')->first();
            if (!$borrower) {
                $borrower = User::create([
                    'name'          => trim($request->first_name . ' ' . $request->surname),
                    'email'         => $request->email ?? ($request->national_id . '@agent.prosperityloans.co.ls'),
                    'phone'         => $request->cell_number,
                    'national_id'   => $request->national_id,
                    'date_of_birth' => $request->date_of_birth,
                    'address'       => $request->residential_address,
                    'password'      => bcrypt(\Illuminate\Support\Str::random(16)),
                    'role'          => 'borrower',
                    'is_active'     => true,
                ]);
            } else {
                $borrower->update([
                    'name'          => trim($request->first_name . ' ' . $request->surname),
                    'phone'         => $request->cell_number,
                    'date_of_birth' => $request->date_of_birth,
                    'address'       => $request->residential_address,
                ]);
            }

            // Create Loan Application matching borrower flow
            $application = LoanApplication::create([
                'user_id'            => $borrower->id,
                'agent_id'           => $agent->id,
                'loan_product_id'    => $request->loan_product_id,
                'status'             => 'submitted',
                'step'               => 9,

                'title'              => $request->title,
                'first_name'         => $request->first_name,
                'surname'            => $request->surname,
                'maiden_name'        => $request->maiden_name,
                'national_id'        => $request->national_id,
                'date_of_birth'      => $request->date_of_birth,
                'gender'             => $request->gender,
                'marital_status'     => $request->marital_status,
                'cell_number'        => $request->cell_number,
                'email'              => $request->email,

                'residential_address'=> $request->residential_address,
                'village'            => $request->village,
                'town'               => $request->town,
                'district'           => $request->district,
                'address_duration'   => $request->address_duration,
                'residence_type'     => $request->residence_type,
                'nearest_landmark'   => $request->nearest_landmark,
                'home_directions'    => $request->home_directions,
                'gps_latitude'       => $request->gps_latitude,
                'gps_longitude'      => $request->gps_longitude,

                'requested_amount'   => $amount,
                'requested_term'     => $term,
                'loan_purpose'       => $request->loan_purpose,
                'payout_method'      => $request->payout_method,
                'collection_method'  => $request->collection_method,
                'salary_payday'      => $request->salary_payday,

                'submitted_at'       => now(),
                'verification_status'=> 'pending',
            ]);

            // Save Employment details
            $application->employment()->create([
                'employer_name'     => $request->employer_name,
                'employer_type'     => $request->employer_type,
                'employer_category' => in_array($request->employer_type, ['government', 'sme', 'private']) ? $request->employer_category : null,
                'job_title'         => $request->job_title,
                'department'        => $request->department,
                'employment_number' => $request->employment_number,
                'contact_number'    => $request->contact_number,
                'employment_expiry_date' => $request->employment_expiry_date,
            ]);

            // Save Bank Details
            $application->bankDetails()->create([
                'bank_name'     => $request->bank_name,
                'branch_name'   => $request->branch_name,
                'branch_code'   => $request->branch_code,
                'account_holder_name' => $request->account_holder_name,
                'account_number'      => $request->account_number,
                'account_type'        => $request->account_type,
            ]);

            // Save Next of Kin
            $application->nextOfKin()->create([
                'first_name'     => $request->nok_1_first_name,
                'last_name'      => $request->nok_1_last_name,
                'relationship'   => $request->nok_1_relationship,
                'contact_number' => $request->nok_1_phone,
                'sort_order'     => 1,
            ]);

            // Save Affordability
            $affordability = \App\Models\AffordabilityAssessment::create([
                'application_id'            => $application->id,
                'monthly_earnings'          => $request->monthly_earnings ?? 0,
                'tax_deduction'             => $request->tax_deduction ?? 0,
                'existing_loans_deduction'  => $request->existing_loans_deduction ?? 0,
                'pension_deduction'         => $request->pension_deduction ?? 0,
                'insurance_deduction'       => $request->insurance_deduction ?? 0,
                'subscriptions_deduction'   => $request->subscriptions_deduction ?? 0,
                'other_deductions'          => $request->other_deductions ?? 0,
                
                'rent'                      => $request->rent ?? 0,
                'groceries'                 => $request->groceries ?? 0,
                'transport'                 => $request->transport ?? 0,
                'utilities'                 => $request->utilities ?? 0,
                'education'                 => $request->education ?? 0,
                'communication'             => $request->communication ?? 0,
                'medical'                   => $request->medical ?? 0,
                'other_loan_repayments'     => $request->other_loan_repayments ?? 0,
                'other_expenses'            => $request->other_expenses ?? 0,
            ]);
            $affordability->recalculate();
            $affordability->save();

            // Decode and save digital signature
            $signaturePath = null;
            if ($request->filled('signature_data')) {
                $data = $request->input('signature_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]);
                    if (in_array($type, ['jpg', 'jpeg', 'png'])) {
                        $decoded = base64_decode(str_replace(' ', '+', $data));
                        if ($decoded !== false) {
                            $filename = 'signatures/' . uniqid() . '.' . $type;
                            Storage::disk('public')->put($filename, $decoded);
                            $signaturePath = $filename;
                        }
                    }
                }
            }
            $application->update(['signature_path' => $signaturePath]);

            // Save Uploaded Documents
            $docs = [
                'national_id_photo'    => 'national_id',
                'payslip_photo'        => 'payslip',
                'bank_statement_photo' => 'bank_statement',
                'selfie_photo'         => 'photo',
            ];
            foreach ($docs as $field => $label) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $path = $file->store("agent-docs/{$application->id}", 'public');
                    Document::create([
                        'user_id'        => $borrower->id,
                        'application_id' => $application->id,
                        'type'           => $label,
                        'filename'       => $file->getClientOriginalName(),
                        'original_name'  => $file->getClientOriginalName(),
                        'path'           => $path,
                        'size'           => $file->getSize(),
                        'mime_type'      => $file->getMimeType(),
                        'status'         => 'pending',
                    ]);
                }
            }

            // Run the automated 5-step verification pipeline instantly
            $pipeline = app(\App\Services\VerificationPipelineService::class);
            $pipeline->verify($application);

            DB::commit();

            return redirect()->route('agent.applications.show', $application)
                ->with('success', "Application {$application->application_number} has been submitted successfully and run through the automated verification pipeline.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to submit client application: ' . $e->getMessage());
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
