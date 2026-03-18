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

        // TODO: Send SMS: "Your MyLoan login — Phone: {$phone} | Temp Password: {$tempPassword}"

        return redirect()->route('officer.walk-in.apply', $client)
            ->with('success', "Client {$client->name} registered. Temp password: {$tempPassword} — send to client via SMS.");
    }

    // ── Start application ────────────────────────────────────────────────
    public function startApplication(User $client)
    {
        $app = LoanApplication::create([
            'application_number'  => 'APP-' . str_pad(LoanApplication::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT),
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
        $application->update([
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'assigned_officer_id' => auth('officer')->id(),
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
            'national_id'    => 'required|string|max:50',
            'date_of_birth'  => 'required|date|before:'.now()->subYears(18)->format('Y-m-d'),
            'gender'         => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:20',
            'cell_number'    => 'required|string|max:30',
            'email'          => 'nullable|email|max:150',
        ]);
        $application->update($data);
    }

    private function saveAddress(Request $request, LoanApplication $application): void
    {
        $data = $request->validate([
            'current_address' => 'required|string|max:500',
            'home_address'    => 'nullable|string|max:500',
        ]);

        // Store on user model (loan_applications table has no address columns)
        $addressText = $data['current_address'];
        if (!empty($data['home_address'])) {
            $addressText .= "\n(Home: " . $data['home_address'] . ')';
        }
        $application->user?->update(['address' => $addressText]);
    }

    private function saveEmployment(Request $request, LoanApplication $application): void
    {
        $request->validate([
            'employment.employer_name' => 'required|string|max:150',
            'employment.employer_type' => 'required|string|max:50',
            'employment.job_title'     => 'required|string|max:100',
            'employment.department'    => 'nullable|string|max:100',
            'employment.employment_number' => 'nullable|string|max:50',
            'employment.contact_number' => 'nullable|string|max:30',
            'employment.expiry_date'   => 'nullable|date',
        ]);
        $emp = $request->input('employment', []);
        Employment::updateOrCreate(
            ['application_id' => $application->id],
            ['application_id' => $application->id,
             'employer_name'  => $emp['employer_name'] ?? null,
             'employer_type'  => $emp['employer_type'] ?? null,
             'job_title'      => $emp['job_title'] ?? null,
             'department'     => $emp['department'] ?? null,
             'employment_number' => $emp['employment_number'] ?? null,
             'contact_number' => $emp['contact_number'] ?? null,
             'employment_expiry_date' => $emp['expiry_date'] ?? null,
            ]
        );
    }

    private function saveBankDetails(Request $request, LoanApplication $application): void
    {
        $request->validate([
            'bank_details.bank_name'           => 'required|string|max:100',
            'bank_details.account_holder_name' => 'required|string|max:150',
            'bank_details.account_number'      => 'required|string|max:50',
            'bank_details.account_type'        => 'required|string|max:30',
        ]);
        BankDetail::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($request->input('bank_details', []), ['application_id' => $application->id])
        );
    }

    private function saveNextOfKin(Request $request, LoanApplication $application): void
    {
        $request->validate([
            'nok.1.relationship'   => 'required|string|max:50',
            'nok.1.first_name'     => 'required|string|max:80',
            'nok.1.surname'        => 'required|string|max:80',
            'nok.1.contact_number' => 'required|string|max:30',
            'nok.2.relationship'   => 'required|string|max:50',
            'nok.2.first_name'     => 'required|string|max:80',
            'nok.2.surname'        => 'required|string|max:80',
            'nok.2.contact_number' => 'required|string|max:30',
        ]);
        $application->nextOfKin()->delete();
        foreach ($request->input('nok', []) as $d) {
            NextOfKin::create([
                'application_id' => $application->id,
                'relationship'   => $d['relationship'] ?? null,
                'first_name'     => $d['first_name'] ?? null,
                'last_name'      => $d['surname'] ?? null,
                'contact_number' => $d['contact_number'] ?? null,
            ]);
        }
    }

    private function saveAffordability(Request $request, LoanApplication $application): void
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
            'medical'                  => 'nullable|numeric|min:0',
            'other_loan_repayments'    => 'nullable|numeric|min:0',
            'family_support'           => 'nullable|numeric|min:0',
            'other_expenses'           => 'nullable|numeric|min:0',
        ]);
        $a = AffordabilityAssessment::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($data, ['application_id' => $application->id])
        );
        $a->recalculate();
        $a->save();
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

        // first_payment_date is on the loans table (created at disbursement), not loan_applications
        // Store as metadata in admin_notes so admin can use it when approving
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
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
}
