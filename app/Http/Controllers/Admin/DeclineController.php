<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeclineCategory;
use App\Models\DeclineRecord;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeclineController extends Controller
{
    /**
     * Display a listing of the decline records.
     */
    public function index(Request $request)
    {
        $query = DeclineRecord::with(['category', 'creator', 'application.user'])
            ->latest('declined_at');

        if ($request->filled('from')) {
            $query->where('declined_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('declined_at', '<=', $request->to);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $records = $query->paginate(50);
        $categories = DeclineCategory::orderBy('display_order')->get();

        return view('admin.declines.index', compact('records', 'categories'));
    }

    /**
     * Show the decline summary report.
     */
    public function report(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $totalDeclines = DeclineRecord::whereBetween('declined_at', [$from, $to])->count();
        $totalValue = DeclineRecord::whereBetween('declined_at', [$from, $to])->sum('loan_amount');

        $breakdown = DeclineRecord::select(
                'category_id',
                DB::raw('count(*) as count'),
                DB::raw('sum(loan_amount) as total_value')
            )
            ->whereBetween('declined_at', [$from, $to])
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) use ($totalDeclines) {
                $item->percentage = $totalDeclines > 0 ? round(($item->count / $totalDeclines) * 100, 1) : 0;
                return $item;
            })
            ->sortByDesc('count');

        $topCategory = $breakdown->first();
        $categoriesUsed = $breakdown->count();

        return view('admin.declines.report', compact(
            'from', 'to', 'totalDeclines', 'totalValue', 
            'breakdown', 'topCategory', 'categoriesUsed'
        ));
    }

    /**
     * Store a newly created decline record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:loan_applications,id',
            'category_id'    => 'required|exists:decline_categories,id',
            'reason'         => 'required|string',
            'declined_at'    => 'required|date|before_or_equal:today',
            'loan_amount'    => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $application = LoanApplication::findOrFail($request->application_id);

        // Verify reason belongs to category
        $category = DeclineCategory::findOrFail($request->category_id);
        if (!in_array($request->reason, $category->reasons)) {
            return back()->withErrors(['reason' => 'The selected reason is not valid for this category.'])->withInput();
        }

        DB::transaction(function () use ($request, $application) {
            DeclineRecord::create([
                'application_id'     => $application->id,
                'application_number' => $application->application_number,
                'applicant_name'     => $application->applicant_name ?? ($application->first_name . ' ' . $application->surname),
                'category_id'        => $request->category_id,
                'reason'             => $request->reason,
                'loan_amount'        => $request->loan_amount ?? $application->requested_amount,
                'declined_at'        => $request->declined_at,
                'notes'              => $request->notes,
                'created_by'         => auth('admin')->id(),
            ]);

            // Update application status
            $application->update([
                'status'         => 'declined',
                'decline_reason' => $request->reason,
                'decided_at'     => now(),
            ]);

            // Log note
            $application->notes()->create([
                'created_by'  => auth('admin')->id(),
                'type'        => 'status',
                'content'     => "Application declined. Reason: {$request->reason}. Notes: {$request->notes}",
                'is_internal' => true,
            ]);
        });

        return redirect()->route('admin.applications.show', $application)
            ->with('success', 'Application declined and reason tracked successfully.');
    }

    /**
     * Get the taxonomy for dropdowns.
     */
    public function taxonomy()
    {
        $categories = DeclineCategory::orderBy('display_order')->get();
        return response()->json($categories);
    }
}
