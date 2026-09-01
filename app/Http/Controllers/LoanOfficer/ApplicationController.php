<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, LoanProduct, AffordabilityAssessment, ApplicationNote};
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    private function baseQuery()
    {
        return LoanApplication::with(['user', 'loanProduct', 'assignedOfficer'])
            ->where('status', '!=', 'draft');
    }

    public function index(Request $request)
    {
        return $this->assigned($request);
    }

    public function assigned(Request $request)
    {
        $officer = auth('officer')->user();
        $q = $this->baseQuery()->where('assigned_officer_id', $officer->id);
        $q = $this->applyFilters($q, $request);
        return view('officer.applications.index', [
            'applications' => $q->latest()->paginate(20),
            'filters'      => $request->only(['status', 'search']),
            'view'         => 'assigned',
        ]);
    }

    public function pending(Request $request)
    {
        $officer = auth('officer')->user();
        $q = $this->baseQuery()
            ->where('assigned_officer_id', $officer->id)
            ->whereIn('status', ['submitted', 'info_requested']);
        return view('officer.applications.index', [
            'applications' => $q->latest()->paginate(20),
            'filters'      => [],
            'view'         => 'pending',
        ]);
    }

    public function all(Request $request)
    {
        $q = $this->baseQuery();
        $q = $this->applyFilters($q, $request);
        return view('officer.applications.index', [
            'applications' => $q->latest()->paginate(20),
            'filters'      => $request->only(['status', 'search']),
            'view'         => 'all',
        ]);
    }

    public function show(LoanApplication $application)
    {
        $application->load([
            'user', 'loanProduct', 'assignedOfficer',
            'documents', 'notes.createdBy',
            'affordability', 'employment', 'bankDetails', 'nextOfKin',
            'creditReport', 'loan',
        ]);
        return view('officer.applications.show', compact('application'));
    }

    public function startReview(LoanApplication $application)
    {
        $application->update([
            'status'      => 'under_review',
            'reviewed_at' => now(),
            'assigned_officer_id' => auth('officer')->id(),
        ]);
        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => 'status',
            'content'     => 'Review started by ' . auth('officer')->user()->name,
            'is_internal' => true,
        ]);
        return back()->with('success', 'Application marked as Under Review.');
    }

    public function completeReview(Request $request, LoanApplication $application)
    {
        $request->validate(['summary' => 'nullable|string|max:1000']);
        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => 'review',
            'content'     => 'Review completed. ' . ($request->summary ?? ''),
            'is_internal' => true,
        ]);
        return back()->with('success', 'Review completed. Application is ready for admin decision.');
    }

    public function requestInfo(Request $request, LoanApplication $application)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $application->update(['status' => 'info_requested', 'admin_notes' => $request->message]);
        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => 'info_request',
            'content'     => $request->message,
            'is_internal' => false,
        ]);
        return back()->with('success', 'Information request sent to borrower.');
    }

    public function routeToAdmin(Request $request, LoanApplication $application)
    {
        $request->validate(['notes' => 'nullable|string|max:500']);
        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => 'route',
            'content'     => 'Routed to admin for decision. ' . ($request->notes ?? ''),
            'is_internal' => true,
        ]);
        return back()->with('success', 'Application routed to admin for approval/decline.');
    }

    public function affordability(LoanApplication $application)
    {
        $application->load(['affordability', 'loanProduct', 'user']);
        return view('officer.applications.affordability', compact('application'));
    }

    public function saveAffordability(Request $request, LoanApplication $application)
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

        foreach ($data as $key => $val) {
            if ($val === null) {
                $data[$key] = 0;
            }
        }

        $assessment = AffordabilityAssessment::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($data, ['application_id' => $application->id])
        );
        $assessment->recalculate();
        $assessment->save();

        return back()->with('success', 'Affordability assessment saved. Net salary: M' . number_format((float)($assessment->net_salary ?? 0), 2) . ', Disposable income: M' . number_format((float)($assessment->disposable_income ?? 0), 2));
    }

    public function schedulePreview(Request $request, LoanApplication $application)
    {
        $product  = $application->loanProduct;
        $amount   = (float) $request->input('amount', $application->approved_amount ?? $application->requested_amount ?? 0);
        $rateP    = (float) $request->input('rate', $application->approved_interest_rate ?? $product?->interest_rate ?? 20);
        $term     = (int)   $request->input('term', $application->approved_term ?? $application->requested_term ?? 1);

        $method          = $product?->interest_method ?? 'reducing';
        $rate            = $rateP / 100;
        $initiationRate  = ($product?->initiation_fee_rate ?? 0) / 100;
        $adminPerMonth   = (float)($product?->admin_fee_fixed ?? 0);
        $totalInitiation = round($amount * $initiationRate, 2);
        $initiationPerMonth = round($totalInitiation / $term, 2);

        $schedule      = [];
        $totalInterest = 0;

        if ($method === 'reducing') {
            $pmt = ($rate > 0)
                ? ($amount * $rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
                : ($amount / $term);
            $monthly = round($pmt + $adminPerMonth + $initiationPerMonth, 2);
            $balance = $amount;

            for ($i = 1; $i <= $term; $i++) {
                $isLast   = ($i === $term);
                $interest = round($balance * $rate, 2);
                $totalInterest += $interest;
                $init     = $isLast ? round($totalInitiation - $initiationPerMonth * ($term - 1), 2) : $initiationPerMonth;

                if ($isLast) {
                    $prin  = $balance;
                    $total = round($prin + $interest + $adminPerMonth + $init, 2);
                } else {
                    $prin  = round($pmt - $interest, 2);
                    if ($prin > $balance) $prin = $balance;
                    $total = $monthly;
                }

                $balance = max(0, round($balance - $prin, 2));

                $schedule[] = [
                    'no'             => $i,
                    'principal'      => $prin,
                    'interest'       => $interest,
                    'initiation_fee' => $init,
                    'admin_fee'      => $adminPerMonth,
                    'payment'        => $total,
                    'balance'        => $balance,
                ];
            }
            $totalRepay = round($monthly * $term, 2);
        } else {
            $totalInterest      = round($amount * $rate * $term, 2);
            $totalRepay         = $amount + $totalInterest + $totalInitiation + ($adminPerMonth * $term);
            $monthly            = round($totalRepay / $term, 2);
            $principalPerMonth  = round($amount / $term, 2);
            $interestPerMonth   = round($amount * $rate, 2);

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
        }

        return response()->json([
            'schedule'         => $schedule,
            'monthly'          => $monthly,
            'total_repay'      => $totalRepay,
            'total_interest'   => $totalInterest,
            'total_initiation' => $totalInitiation,
            'total_admin'      => $adminPerMonth * $term,
            'cash_received'    => $amount,
        ]);
    }

    private function applyFilters($q, Request $request)
    {
        if ($request->filled('status'))  $q->where('status', $request->status);
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function($q) use ($s) {
                $q->where('application_number', 'like', "%$s%")
                  ->orWhere('first_name', 'like', "%$s%")
                  ->orWhere('surname', 'like', "%$s%")
                  ->orWhere('cell_number', 'like', "%$s%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
            });
        }
        return $q;
    }
    public function uploadDocument(\Illuminate\Http\Request $request, \App\Models\LoanApplication $application)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|string',
        ]);

        $path = $request->file('file')->store('documents/' . $application->user_id, 'public');

        \App\Models\Document::create([
            'user_id'        => $application->user_id,
            'application_id' => $application->id,
            'type'           => $request->type,
            'filename'       => $request->file('file')->getClientOriginalName(),
            'original_name'  => $request->file('file')->getClientOriginalName(),
            'path'           => $path,
            'status'         => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Document uploaded.']);
        }

        return back()->with('success', 'Document uploaded.');
    }
}
