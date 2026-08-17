<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\{User, LoanApplication, LoanProduct, Employment, BankDetail, NextOfKin, Document, AffordabilityAssessment, ApplicationNote};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};
use Illuminate\Support\Str;

class WalkInClientController extends Controller
{
    // ── Registration form ────────────────────────────────────────────────
    public function create()
    {
        return view('officer.walk-in.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'phone'        => 'required|string|max:30',
            'national_id'  => 'required|string|max:50',
            'date_of_birth'=> 'nullable|date|before:'.now()->subYears(18)->format('Y-m-d'),
            'email'        => 'nullable|email|max:150',
        ]);

        $phone = $this->formatPhone($request->phone);

        if (User::where('national_id', $request->national_id)->exists()) {
            return back()->withErrors(['national_id' => 'A borrower with this ID already exists.'])->withInput();
        }
        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'A borrower with this phone number already exists.'])->withInput();
        }

        $tempPassword = Str::random(10);

        $client = User::create([
            'name'                => $request->name,
            'phone'               => $phone,
            'email'               => $request->filled('email') ? $request->email : null,
            'national_id'         => $request->national_id,
            'date_of_birth'       => $request->date_of_birth,
            'address'             => $request->address,
            'gender'              => $request->gender,
            'role'                => 'borrower',
            'assigned_officer_id' => auth('officer')->id(),
            'password'            => Hash::make($tempPassword),
            'is_active'           => true,
            'email_verified_at'   => now(),
        ]);

        // TODO: Send SMS: "Your Prosperity Loans login — Phone: {$phone} | Temp Password: {$tempPassword}"

        return redirect()->route('officer.walk-in.apply', $client)
            ->with('success', "Client {$client->name} registered. Temp password: {$tempPassword} — send to client via SMS.");
    }

    // ── Start application ────────────────────────────────────────────────
    public function startApplication(User $client)
    {
        $app = LoanApplication::create([
            'user_id'             => $client->id,
            'assigned_officer_id' => auth('officer')->id(),
            'status'              => 'draft',
            'step'                => 1,
            'first_name'          => explode(' ', $client->name)[0] ?? $client->name,
            'surname'             => implode(' ', array_slice(explode(' ', $client->name), 1)) ?: '',
            'cell_number'         => $client->phone,
            'national_id'         => $client->national_id,
            'email'               => $client->email,
        ]);

        return redirect()->route('officer.walk-in.step.show', [$app, 1])
            ->with('info', "Starting application for {$client->name}.");
    }

    // ── Show step ────────────────────────────────────────────────────────
    public function showStep(LoanApplication $application, int $step)
    {
        $step = max(1, min(9, $step));
        $application->load(['user', 'loanProduct', 'affordability', 'employment', 'bankDetails', 'nextOfKin']);
        $products = LoanProduct::active()->get();
        return view('officer.walk-in.step', compact('application', 'step', 'products'));
    }

    // ── Save step ────────────────────────────────────────────────────────
    public function saveStep(Request $request, LoanApplication $application, int $step)
    {
        $step     = max(1, min(9, $step));
        $nextStep = min($step + 1, 9);

        match ($step) {
            1 => $this->savePersonal($request, $application),
            2 => $this->saveAddress($request, $application),
            3 => $this->saveEmployment($request, $application),
            4 => $this->saveBankDetails($request, $application),
            5 => $this->saveNextOfKin($request, $application),
            6 => $this->saveAffordability($request, $application),
            7 => $this->saveLoanDetails($request, $application),
            8 => $this->saveDocuments($request, $application),
            9 => null,
        };

        if ($nextStep > ($application->fresh()->step ?? 1)) {
            $application->update(['step' => $nextStep]);
        }

        return redirect()->route('officer.walk-in.step.show', [$application, $nextStep]);
    }

    // ── Submit ───────────────────────────────────────────────────────────
    public function submit(Request $request, LoanApplication $application)
    {
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
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'assigned_officer_id' => auth('officer')->id(),
            'signature_path'      => $signaturePath
        ]);

        if ($request->filled('officer_notes')) {
            $application->notes()->create([
                'created_by'  => auth('officer')->id(),
                'type'        => 'general',
                'content'     => $request->officer_notes,
                'is_internal' => true,
            ]);
        }

        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => 'status',
            'content'     => 'Walk-in application submitted by Officer ' . auth('officer')->user()->name,
            'is_internal' => true,
        ]);

        return redirect()->route('officer.applications.show', $application)
            ->with('success', "Application {$application->application_number} submitted for admin review.");
    }

    // ── Step save logic ──────────────────────────────────────────────────

    private function savePersonal(Request $request, LoanApplication $application): void
    {
        $data = $request->validate([
            'title'          => 'nullable|string|max:20',
            'first_name'     => 'required|string|max:80',
            'surname'        => 'required|string|max:80',
            'national_id'    => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('users', 'national_id')->ignore($application->user_id)
            ],
            'date_of_birth'  => 'required|date|before:'.now()->subYears(18)->format('Y-m-d'),
            'gender'         => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:20',
            'cell_number'    => 'required|string|max:30',
            'email'          => 'nullable|email|max:150',
        ]);

        // Sync with User record
        $application->user->update([
            'national_id'   => $data['national_id'],
            'date_of_birth' => $data['date_of_birth'],
            'gender'        => $data['gender'],
        ]);

        $application->update($data);
    }

    private function saveAddress(Request $request, LoanApplication $application): void
    {
        $data = $request->validate([
            'residential_address' => 'required|string|max:500',
            'village'             => 'required|string|max:150',
            'town'                => 'required|string|max:150',
            'district'            => 'required|string|max:100',
            'address_duration'    => 'required|string|max:50',
            'residence_type'      => 'required|string|max:50',
            'nearest_landmark'    => 'required|string|max:255',
            'home_directions'     => 'required|string',
            'gps_latitude'        => 'nullable|numeric',
            'gps_longitude'       => 'nullable|numeric',
        ]);

        $application->update($data);
    }

    private function saveEmployment(Request $request, LoanApplication $application): void
    {
        $data = $request->validate([
            'employer_name'          => 'required|string|max:150',
            'employer_type'          => 'required|string|max:50',
            'job_title'              => 'required|string|max:100',
            'department'             => 'nullable|string|max:100',
            'employment_number'      => 'required|string|max:50',
            'contact_number'         => 'required|string|max:30',
            'employer_category'      => 'nullable|string|max:50',
            'employment_expiry_date' => 'nullable|date',
        ]);

        $application->employment()->updateOrCreate(
            ['application_id' => $application->id],
            array_merge($data, [
                'employer_category' => in_array($data['employer_type'], ['government', 'sme', 'private']) ? ($request->employer_category ?? null) : null
            ])
        );
    }

    private function saveBankDetails(Request $request, LoanApplication $application): void
    {
        $id = $application->id;
        $application->bankDetails()->updateOrCreate(
            ['application_id' => $id],
            [
                'bank_name'           => $request->bank_name,
                'branch_name'         => $request->branch_name,
                'branch_code'         => $request->branch_code,
                'account_holder_name' => $request->account_holder_name,
                'account_number'      => $request->account_number,
                'account_type'        => $request->account_type,
            ]
        );
    }

    private function saveNextOfKin(Request $request, LoanApplication $application): void
    {
        $request->validate([
            'nok_1_first_name'     => 'required|string|max:80',
            'nok_1_last_name'      => 'required|string|max:80',
            'nok_1_relationship'   => 'required|string|max:50',
            'nok_1_phone'          => 'required|string|max:30',
        ]);

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

    private function saveAffordability(Request $request, LoanApplication $application): void
    {
        $data = $request->validate([
            'monthly_earnings'         => 'required|numeric|min:0',
            'tax_deduction'            => 'nullable|numeric|min:0',
            'existing_loans_deduction' => 'nullable|numeric|min:0',
            'other_deductions'         => 'nullable|numeric|min:0',
            'rent'                     => 'nullable|numeric|min:0',
            'groceries'                => 'nullable|numeric|min:0',
            'transport'                => 'nullable|numeric|min:0',
            'utilities'                => 'nullable|numeric|min:0',
            'education'                => 'nullable|numeric|min:0',
            'communication'            => 'nullable|numeric|min:0',
            'other_insurance'          => 'nullable|numeric|min:0',
            'medical'                  => 'nullable|numeric|min:0',
            'other_loan_repayments'    => 'nullable|numeric|min:0',
            'family_support'           => 'nullable|numeric|min:0',
            'entertainment'            => 'nullable|numeric|min:0',
            'other_expenses'           => 'nullable|numeric|min:0',
        ]);

        $a = AffordabilityAssessment::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($data, ['application_id' => $application->id])
        );
        $a->recalculate();
        $a->save();

        if ($a->total_living_expenses > ($a->net_salary * 0.70)) {
            // We throw a validation error to stay on the page
            throw \Illuminate\Validation\ValidationException::withMessages([
                'monthly_earnings' => 'Total monthly living expenses cannot exceed 70% of net salary.'
            ]);
        }
    }

    private function saveLoanDetails(Request $request, LoanApplication $application): void
    {
        $data = $request->validate([
            'loan_product_id'    => 'required|exists:loan_products,id',
            'requested_amount'   => 'required|numeric|min:1',
            'requested_term'     => 'required|integer|min:1|max:60',
            'first_payment_date' => 'nullable|date',
            'payout_method'      => 'required|string|max:50',
            'collection_method'  => 'required|string|max:50',
            'loan_purpose'       => 'required|string|max:500',
        ]);

        $aff = $application->affordability()->first();
        if ($aff) {
            $product = LoanProduct::find($data['loan_product_id']);
            if ($product) {
                $p = (float)$data['requested_amount'];
                $t = (int)$data['requested_term'];
                if ($t > 0) {
                    $total = $p + ($p * ($product->interest_rate / 100) * $t) + ($p * ($product->initiation_fee_rate / 100)) + ($product->admin_fee_fixed * $t);
                    $monthly = $total / $t;
                    if ($monthly > ($aff->net_salary * 0.30)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'requested_amount' => 'The estimated monthly repayment (M' . number_format($monthly, 2) . ') exceeds 30% of the net salary.'
                        ]);
                    }
                }
            }
        }

        // first_payment_date logic
        $firstPayment = $data['first_payment_date'] ?? null;
        unset($data['first_payment_date']);
        if ($firstPayment) {
            $existing = $application->admin_notes ?? '';
            $existing = preg_replace('/\s*\[first_payment_date:[^\]]*\]/', '', $existing);
            $data['admin_notes'] = trim($existing . ' [first_payment_date:' . $firstPayment . ']');
        }

        $application->update($data);
    }

    private function saveDocuments(Request $request, LoanApplication $application): void
    {
        $request->validate([
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $type => $file) {
                $existing = $application->documents()->where('type', $type)->first();
                if ($existing) {
                    Storage::disk('public')->delete($existing->path);
                    $existing->delete();
                }
                $path = $file->store("documents/{$application->id}", 'public');
                Document::create([
                    'application_id' => $application->id,
                    'user_id'        => $application->user_id,
                    'type'           => $type,
                    'path'           => $path,
                    'original_name'  => $file->getClientOriginalName(),
                    'mime_type'      => $file->getMimeType(),
                    'size'           => $file->getSize(),
                    'status'         => 'pending',
                ]);
            }
        }
    }

    private function formatPhone(?string $phone): string
    {
        if (!$phone) return '';
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 8) return '+266' . $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '266')) return '+' . $digits;
        if (str_starts_with($phone, '+266')) return $phone;
        return '+266' . substr($digits, -8);
    }

    private function saveCardToken(Request $request, LoanApplication $application): void
    {
        // Skip if they selected manual EFT/Debit Order and didn't provide card details
        if (!$request->filled('card_number') || !$request->filled('card_expiry') || !$request->filled('card_cvv')) {
            return;
        }

        $user = $application->user;

        $cardNumber = preg_replace('/\s+/', '', $request->card_number);
        $placeholderToken = 'TOK_' . strtoupper(substr(md5($cardNumber . $request->card_expiry . now()->timestamp), 0, 24));

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

        $application->update(['card_tokenised' => true]);
    }

    private function detectCardBrand(string $number): string
    {
        $n = preg_replace('/\s+/', '', $number);
        if (str_starts_with($n, '4'))                             return 'Visa';
        if (preg_match('/^5[1-5]/', $n))                          return 'Mastercard';
        if (str_starts_with($n, '2'))                             return 'Mastercard';
        if (preg_match('/^3[47]/', $n))                           return 'Amex';
        return 'Unknown';
    }
}
