<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, LoanProduct, AffordabilityAssessment};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
                'application_number'  => 'APP-' . str_pad(LoanApplication::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT),
                'user_id'             => $user->id,
                'status'              => 'draft',
                'step'                => 1,
                'first_name'          => explode(' ', $user->name)[0] ?? '',
                'surname'             => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: '',
                'cell_number'         => $user->phone,
                'national_id'         => $user->national_id,
            ]);
        }
        return redirect()->route('borrower.apply.step.show', [$draft, 1]);
    }

    public function showStep(LoanApplication $application, int $step) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        if ($step > $application->step + 1) return redirect()->route('borrower.apply.step.show', [$application, $application->step]);
        $application->load(['loanProduct','affordability','employment','bankDetails','nextOfKin']);
        $products = LoanProduct::active()->get();
        return view('borrower.applications.step', compact('application','step','products'));
    }

    public function saveStep(Request $request, LoanApplication $application, int $step) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $data     = $request->except(['_token','_method']);
        $nextStep = min($step + 1, 9);

        // Step-specific saves
        if ($step === 6) $this->saveAffordability($request, $application);
        elseif ($step === 3) $this->saveEmployment($request, $application);
        elseif ($step === 4) $this->saveBankDetails($request, $application);
        elseif ($step === 5) $this->saveNextOfKin($request, $application);

        $appData = array_filter($data, fn($k) => !in_array($k, ['employment_number','employer_name','employer_type','job_title','department','contact_number','bank_name','account_holder_name','account_number','account_type','nok_1_first_name','monthly_earnings','tax_deduction']), ARRAY_FILTER_USE_KEY);
        $application->update(array_merge($appData, ['step' => max($application->step, $nextStep)]));

        if ($step < 9) return redirect()->route('borrower.apply.step.show', [$application, $nextStep]);
        return redirect()->route('borrower.apply.step.show', [$application, 9]);
    }

    public function saveDraft(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $application->update($request->except(['_token']));
        return back()->with('success', 'Draft saved.');
    }

    public function submit(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $application->update(['status' => 'submitted', 'submitted_at' => now()]);
        return redirect()->route('borrower.apply.submitted', $application);
    }

    public function submitted(LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        return view('borrower.applications.submitted', compact('application'));
    }

    public function respondInfo(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $request->validate(['response' => 'required|string|max:2000']);
        $application->notes()->create(['created_by' => auth('borrower')->id(), 'type' => 'borrower_response', 'content' => $request->response, 'is_internal' => false]);
        if ($application->status === 'info_requested') $application->update(['status' => 'submitted']);
        return back()->with('success', 'Response submitted. We will review shortly.');
    }

    public function cancel(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        if (!in_array($application->status, ['draft','submitted'])) return back()->with('error', 'Cannot cancel at this stage.');
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
        $application->notes()->create(['created_by' => auth('borrower')->id(), 'type' => 'terms_accepted', 'content' => 'Borrower accepted loan terms on '.now()->format('d M Y H:i'), 'is_internal' => false]);
        return redirect()->route('borrower.applications.show', $application)->with('success', 'Terms accepted. Awaiting disbursement.');
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

    private function saveAffordability(Request $request, LoanApplication $application) {
        $a = AffordabilityAssessment::updateOrCreate(['application_id' => $application->id], [
            'application_id'           => $application->id,
            'monthly_earnings'         => $request->monthly_earnings ?? 0,
            'tax_deduction'            => $request->tax_deduction ?? 0,
            'existing_loans_deduction' => $request->existing_loans_deduction ?? 0,
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
        ]);
        $a->recalculate(); $a->save();
    }

    private function saveEmployment(Request $request, LoanApplication $application) {
        $application->employment()->updateOrCreate(['application_id' => $application->id], [
            'employer_name'       => $request->employer_name,
            'employer_type'       => $request->employer_type,
            'job_title'           => $request->job_title,
            'department'          => $request->department,
            'employment_number'   => $request->employment_number,
            'contact_number'      => $request->contact_number,
            'employment_expiry_date' => $request->employment_expiry_date,
        ]);
    }

    private function saveBankDetails(Request $request, LoanApplication $application) {
        $application->bankDetails()->updateOrCreate(['application_id' => $application->id], [
            'bank_name'            => $request->bank_name,
            'account_holder_name'  => $request->account_holder_name,
            'account_number'       => $request->account_number,
            'account_type'         => $request->account_type,
        ]);
    }

    private function saveNextOfKin(Request $request, LoanApplication $application) {
        if ($request->filled('nok_1_first_name')) {
            $application->nextOfKin()->updateOrCreate(['application_id' => $application->id, 'sort_order' => 1], [
                'first_name'     => $request->nok_1_first_name,
                'last_name'      => $request->nok_1_last_name,
                'relationship'   => $request->nok_1_relationship,
                'contact_number' => $request->nok_1_phone,
            ]);
        }
    }
}


class AffordabilityController extends Controller
{
    public function calculate(\Illuminate\Http\Request $request) {
        $p       = (float) $request->principal ?? 0;
        $rate    = (float) ($request->rate ?? 15) / 100;
        $term    = (int)   ($request->term ?? 1);
        $initR   = (float) ($request->initiation_rate ?? 40) / 100;
        $admin   = (float) ($request->admin_fee ?? 50);
        $income  = (float) ($request->net_income ?? 0);

        $totalInt  = round($p * $rate * $term, 2);
        $totalInit = round($p * $initR, 2);
        $totalRepay= $p + $totalInt + $totalInit + ($admin * $term);
        $monthly   = $term > 0 ? round($totalRepay / $term, 2) : 0;
        $passes    = $income > 0 && $monthly <= $income;

        return response()->json(['monthly' => $monthly, 'total_repay' => $totalRepay, 'total_interest' => $totalInt, 'initiation_fee' => $totalInit, 'passes' => $passes, 'surplus' => round($income - $monthly, 2)]);
    }
}
