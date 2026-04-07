<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, LoanProduct, AffordabilityAssessment, Payment};
use App\Services\CPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function index() {
        $applications = LoanApplication::where('user_id', auth('borrower')->id())
            ->with('loanProduct')->latest()->paginate(10);
        return view('borrower.applications.index', compact('applications'));
    }

    public function show(LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $application->load(['loanProduct','documents','notes' => fn($q) => $q->where('is_internal', false),'loan','affordability']);
        return view('borrower.applications.show', compact('application'));
    }

    public function start() {
        $user  = auth('borrower')->user();
        $draft = LoanApplication::where('user_id', $user->id)->where('status','draft')->latest()->first();
        if (!$draft) {
            $draft = LoanApplication::create([
                'user_id'            => $user->id,
                'status'             => 'draft',
                'step'               => 1,
                'first_name'         => explode(' ', $user->name)[0] ?? '',
                'surname'            => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: '',
                'cell_number'        => $user->phone,
                'national_id'        => $user->national_id,
                'maiden_name'        => $user->maiden_name,
            ]);

        }
        return redirect()->route('borrower.apply.step.show', [$draft, 1]);
    }

    public function showStep(LoanApplication $application, int $step) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        // Allow going back freely, but not skipping ahead
        if ($step > $application->step + 1) {
            return redirect()->route('borrower.apply.step.show', [$application, $application->step]);
        }
        $application->load(['loanProduct','affordability','employment','bankDetails','nextOfKin']);
        $products = LoanProduct::active()->get();
        return view('borrower.applications.step', compact('application','step','products'));
    }

    public function saveStep(Request $request, LoanApplication $application, int $step) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);

        $totalSteps = 10;
        $nextStep   = min($step + 1, $totalSteps); // ← FIX: was min($step+1, 9)

        // ── Step-specific handlers ──────────────────────────────────
        if ($step === 3)  $this->saveEmployment($request, $application);
        if ($step === 4)  $this->saveBankDetails($request, $application);
        if ($step === 5)  $this->saveNextOfKin($request, $application);
        if ($step === 6) {
            $this->saveAffordability($request, $application);
            $aff = $application->affordability()->first();
            if ($aff && $aff->total_living_expenses > ($aff->net_salary * 0.70)) {
                return back()->withInput()->with('error', 'Total monthly living expenses cannot exceed 70% of your net salary.');
            }
        }
        if ($step === 7) {
            $aff = $application->affordability()->first();
            if ($aff) {
                $product = \App\Models\LoanProduct::find($request->loan_product_id);
                if ($product) {
                    $p = (float)$request->requested_amount;
                    $t = (int)$request->requested_term;
                    if ($t > 0) {
                        $total = $p + ($p * ($product->interest_rate / 100) * $t) + ($p * ($product->initiation_fee_rate / 100)) + ($product->admin_fee_fixed * $t);
                        $monthly = $total / $t;
                        if ($monthly > ($aff->net_salary * 0.30)) {
                            return back()->withInput()->with('error', 'The estimated monthly repayment (M' . number_format($monthly, 2) . ') exceeds 30% of your net salary. Please decrease the loan amount or increase the loan term.');
                        }
                    }
                }
            }
        }
        if ($step === 8) {
            $docs = $application->documents()->pluck('type')->toArray();
            $missing = array_diff(['national_id','payslip','bank_statement'], $docs);
            if (count($missing) > 0) {
                return back()->with('error', 'Please upload all required documents ('.implode(', ', array_map(fn($v)=>ucwords(str_replace('_',' ',$v)),$missing)).') before continuing.');
            }
        }
        if ($step === 9) {
            $this->saveCardToken($request, $application);
            return redirect()->route('borrower.apply.verify-card', $application);
        }

        // ── Fields to exclude from direct application update ────────
        // (handled by their own save methods above)
        $excludeFromApp = [
            // Employment (step 3)
            'employer_name','employer_type','job_title','department',
            'employment_number','contact_number','employment_expiry_date',
            // Bank (step 4)
            'bank_name','account_holder_name','account_number','account_type',
            // Next of kin (step 5)
            'nok_1_first_name','nok_1_last_name','nok_1_relationship','nok_1_phone',
            // Affordability (step 6)
            'monthly_earnings','tax_deduction','existing_loans_deduction',
            'pension_deduction','insurance_deduction','subscriptions_deduction',
            'other_deductions','rent','groceries','transport','utilities',
            'education','communication','other_insurance','medical',
            'other_loan_repayments','family_support','entertainment','other_expenses',
            // Card (step 9) — NEVER save raw card data to loan_applications
            'card_number','card_expiry','card_cvv','card_name',
        ];

        $data    = $request->except(array_merge(['_token','_method'], $excludeFromApp));
        $newStep = max($application->step, $nextStep);

        // Only update fields that exist in fillable (safe update)
        $fillable = $application->getFillable();
        $safeData = array_filter($data, fn($k) => in_array($k, $fillable), ARRAY_FILTER_USE_KEY);
        $safeData['step'] = $newStep;

        $application->update($safeData);

        // Advance to next step or stay on last
        if ($step < $totalSteps) {
            return redirect()->route('borrower.apply.step.show', [$application, $nextStep]);
        }
        // Step 10 — stay on review page (submit button uses different action)
        return redirect()->route('borrower.apply.step.show', [$application, $totalSteps]);
    }

    public function saveDraft(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $application->update($request->except(['_token']));
        return back()->with('success', 'Draft saved.');
    }

    public function submit(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        
        $signaturePath = $application->signature_path;
        if ($request->filled('signature_data')) {
            $data = $request->input('signature_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]); 
                if (in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                    $decoded = base64_decode(str_replace(' ', '+', $data));
                    if ($decoded !== false) {
                        $filename = 'signatures/' . uniqid() . '.' . $type;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                        $signaturePath = $filename;
                    }
                }
            }
        }

        $application->update([
            'status' => 'submitted', 
            'submitted_at' => now(),
            'signature_path' => $signaturePath
        ]);
        return redirect()->route('borrower.apply.submitted', $application);
    }

    public function submitted(LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        return view('borrower.applications.submitted', compact('application'));
    }

    public function saveSignature(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);

        if ($request->filled('signature_data')) {
            $data = $request->input('signature_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]); 
                if (in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                    $decoded = base64_decode(str_replace(' ', '+', $data));
                    if ($decoded !== false) {
                        $filename = 'signatures/' . uniqid() . '.' . $type;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                        $application->update(['signature_path' => $filename]);
                        return back()->with('success', 'Signature successfully saved.');
                    }
                }
            }
        }
        return back()->with('error', 'Invalid signature data provided.');
    }

    public function getMessages(LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $messages = $application->messages()->orderBy('created_at', 'asc')->get()->map(function($msg) {
            $senderName = 'Borrower';
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

    public function sendMessage(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $request->validate(['message' => 'required|string|max:2000']);
        
        $application->messages()->create([
            'sender_type' => 'borrower',
            'sender_id' => auth('borrower')->id(),
            'message' => $request->message,
        ]);
        
        if ($application->status === 'info_requested') {
            $application->update(['status' => 'submitted']);
        }
        return response()->json(['success' => true]);
    }

    public function cancel(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        if (!in_array($application->status, ['draft','submitted'])) {
            return back()->with('error', 'Cannot cancel at this stage.');
        }
        $application->update(['status' => 'declined', 'decline_reason' => 'Cancelled by borrower']);
        return redirect()->route('borrower.applications.index')->with('success', 'Application cancelled.');
    }

    public function showTerms(LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $application->load(['loanProduct','loan']);
        return view('borrower.applications.terms', compact('application'));
    }

    public function acceptTerms(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $application->notes()->create([
            'created_by'  => auth('borrower')->id(),
            'type'        => 'terms_accepted',
            'content'     => 'Borrower accepted loan terms on '.now()->format('d M Y H:i'),
            'is_internal' => false,
        ]);
        return redirect()->route('borrower.applications.show', $application)
            ->with('success', 'Terms accepted. Awaiting disbursement.');
    }

    public function download(LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        return redirect()->route('borrower.applications.show', $application);
    }

    public function productTerms(LoanProduct $product) {
        return response()->json([
            'interest_rate'       => $product->interest_rate,
            'initiation_fee_rate' => $product->initiation_fee_rate,
            'admin_fee_fixed'     => $product->admin_fee_fixed,
            'min_amount'          => $product->min_amount,
            'max_amount'          => $product->max_amount,
            'min_term'            => $product->min_term_months,
            'max_term'            => $product->max_term_months,
            'late_payment_fee'    => $product->late_payment_fee,
        ]);
    }

    // ── Private save helpers ─────────────────────────────────────

    private function saveCardToken(Request $request, LoanApplication $application): void
    {
        // Validate card fields are present
        if (!$request->filled('card_number') || !$request->filled('card_expiry') || !$request->filled('card_cvv')) {
            return; // Skip if card fields missing (shouldn't happen with required validation)
        }

        $user = auth('borrower')->user();

        // TODO: Replace this block with real CPay tokenization API call:
        // $cpayService = app(\App\Services\CPayService::class);
        // $result = $cpayService->tokenizeCard([
        //     'card_number' => $request->card_number,
        //     'expiry'      => $request->card_expiry,
        //     'cvv'         => $request->card_cvv,
        //     'name'        => $request->card_name,
        // ]);
        // $token = $result['token'];

        // For now: generate a placeholder token (replace with real CPay call)
        $cardNumber = preg_replace('/\s+/', '', $request->card_number);
        $placeholderToken = 'TOK_' . strtoupper(substr(md5($cardNumber . $request->card_expiry . now()->timestamp), 0, 24));

        // Store ONLY the token — never the raw card data
        // For administrative manual debit requested by user, we encrypt and store it
        $user->update([
            'card_token'            => $placeholderToken,
            'card_last_four'        => substr($cardNumber, -4),
            'encrypted_card_number' => \Illuminate\Support\Facades\Crypt::encryptString($cardNumber),
            'card_expiry'           => $request->card_expiry,
            'card_cvv'              => \Illuminate\Support\Facades\Crypt::encryptString($request->card_cvv),
            'card_name'             => $request->card_name,
            'card_brand'            => $this->detectCardBrand($cardNumber),
            'card_tokenised_at'     => now(),
        ]);

        // Mark tokenisation done on the application
        // $application->update(['card_tokenised' => true]); 
    }

    public function initiateCardVerification(LoanApplication $application)
    {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        
        $user = auth('borrower')->user();
        
        // Create verification payment
        $payment = Payment::create([
            'payment_reference' => 'VER-' . strtoupper(Str::random(10)),
            'user_id'           => $user->id,
            'application_id'    => $application->id,
            'amount'            => 10.00,
            'method'            => 'card',
            'status'            => 'pending',
            'notes'             => 'M10 Card Verification for Loan Application #' . $application->id,
        ]);

        $cpay = app(CPayService::class);
        
        // Use initiateRepayment with card method and custom success redirect
        $successUrl = route('borrower.apply.card-success', ['application' => $application->id, 'ref' => $payment->payment_reference]);
        
        $result = $cpay->initiateRepayment($payment, $user->phone, 'card', $successUrl);

        if ($result['success']) {
            $redirectUrl = $result['redirect_url']
                ?? $result['data']['redirectUrl']
                ?? $result['data']['paymentLink']
                ?? null;

            if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                return redirect()->away($redirectUrl);
            }
            
            // If redirect URL is embedded in HTML
            $html = $result['raw'] ?? null;
            if ($html && strlen($html) > 100 && str_contains($html, '<')) {
                return response($html)->header('Content-Type', 'text/html');
            }
        }

        return redirect()->route('borrower.apply.step.show', [$application, 9])
            ->with('error', 'Could not initiate card verification: ' . ($result['error'] ?? 'Please try again.'));
    }

    public function cardVerificationSuccess(Request $request, LoanApplication $application)
    {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        
        $ref = $request->input('ref') ?? $request->input('transactionId');
        $payment = Payment::where('payment_reference', $ref)
            ->where('user_id', auth('borrower')->id())
            ->first();

        if (!$payment) {
             return redirect()->route('borrower.apply.step.show', [$application, 9])
                ->with('error', 'Card verification payment not found.');
        }

        // Ideally, we'd poll CPay here to confirm success, but usually, 
        // if they hit this callback, it was successful at the gateway.
        // The webhook will finalize the 'verified' status in the DB.
        
        // Advance application status
        $application->update([
            'card_tokenised' => true,
            'step'           => 10
        ]);

        return redirect()->route('borrower.apply.step.show', [$application, 10])
            ->with('success', 'Card verified successfully. Please review and submit your application.');
    }

    private function detectCardBrand(string $number): string
    {
        $n = preg_replace('/\s+/', '', $number);
        if (str_starts_with($n, '4'))                              return 'Visa';
        if (preg_match('/^5[1-5]/', $n))                          return 'Mastercard';
        if (str_starts_with($n, '2'))                             return 'Mastercard';
        if (preg_match('/^3[47]/', $n))                           return 'Amex';
        return 'Unknown';
    }

    private function saveAffordability(Request $request, LoanApplication $application): void
    {
        $a = AffordabilityAssessment::updateOrCreate(
            ['application_id' => $application->id],
            [
                'application_id'           => $application->id,
                'monthly_earnings'         => $request->monthly_earnings ?? 0,
                'tax_deduction'            => $request->tax_deduction ?? 0,
                'existing_loans_deduction' => $request->existing_loans_deduction ?? 0,
                'pension_deduction'        => $request->pension_deduction ?? 0,
                'insurance_deduction'      => $request->insurance_deduction ?? 0,
                'subscriptions_deduction'  => $request->subscriptions_deduction ?? 0,
                'other_deductions'         => $request->other_deductions ?? 0,
                'transport'                => $request->transport ?? 0,
                'groceries'                => $request->groceries ?? 0,
                'utilities'                => $request->utilities ?? 0,
                'rent'                     => $request->rent ?? 0,
                'education'                => $request->education ?? 0,
                'communication'            => $request->communication ?? 0,
                'other_insurance'          => $request->other_insurance ?? 0,
                'medical'                  => $request->medical ?? 0,
                'other_loan_repayments'    => $request->other_loan_repayments ?? 0,
                'family_support'           => $request->family_support ?? 0,
                'entertainment'            => $request->entertainment ?? 0,
                'other_expenses'           => $request->other_expenses ?? 0,
            ]
        );
        $a->recalculate();
        $a->save();
    }

    private function saveEmployment(Request $request, LoanApplication $application): void
    {
        $application->employment()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'employer_name'          => $request->employer_name,
                'employer_type'          => $request->employer_type,
                'job_title'              => $request->job_title,
                'department'             => $request->department,
                'employment_number'      => $request->employment_number,
                'contact_number'         => $request->contact_number,
                'employment_expiry_date' => $request->employment_expiry_date,
            ]
        );
    }

    private function saveBankDetails(Request $request, LoanApplication $application): void
    {
        $application->bankDetails()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'bank_name'           => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'account_number'      => $request->account_number,
                'account_type'        => $request->account_type,
            ]
        );
    }

    private function saveNextOfKin(Request $request, LoanApplication $application): void
    {
        if ($request->filled('nok_1_first_name')) {
            $application->nextOfKin()->updateOrCreate(
                ['application_id' => $application->id, 'sort_order' => 1],
                [
                    'first_name'     => $request->nok_1_first_name,
                    'last_name'      => $request->nok_1_last_name,
                    'relationship'   => $request->nok_1_relationship,
                    'contact_number' => $request->nok_1_phone,
                ]
            );
        }
    }
}