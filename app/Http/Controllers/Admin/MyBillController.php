<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{MyBillLimit, MyBillLoan, User};
use App\Services\MyBillService;
use Illuminate\Http\Request;

class MyBillController extends Controller
{
    public function __construct(private MyBillService $svc) {}

    /**
     * MyBill Admin Dashboard — stats, charts, recent activity.
     */
    public function dashboard()
    {
        $stats = $this->svc->getStats();
        $recent = MyBillLoan::with('user')->latest()->take(15)->get();

        return view('admin.mybill.dashboard', compact('stats', 'recent'));
    }

    /**
     * All MyBill loans with filters.
     */
    public function loans(Request $request)
    {
        $query = MyBillLoan::with('user');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('bill_category', $request->category);
        if ($request->filled('tier'))     $query->where('tier', $request->tier);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('loan_number', 'like', "%{$s}%")
                  ->orWhere('meter_number', 'like', "%{$s}%")
                  ->orWhere('phone_number', 'like', "%{$s}%")
                  ->orWhere('policy_number', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        $loans = $query->latest()->paginate(20);
        $stats = $this->svc->getStats();

        return view('admin.mybill.loans', compact('loans', 'stats'));
    }

    /**
     * Single loan detail.
     */
    public function showLoan(MyBillLoan $loan)
    {
        $loan->load(['user', 'repayments']);
        return view('admin.mybill.loan-show', compact('loan'));
    }

    /**
     * Client limit management.
     */
    public function limits(Request $request)
    {
        $query = MyBillLimit::with('user');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
        }

        $limits = $query->orderByDesc('used_amount')->paginate(20);

        return view('admin.mybill.limits', compact('limits'));
    }

    /**
     * Update a client's credit limit.
     */
    public function updateLimit(Request $request, MyBillLimit $limit)
    {
        $request->validate([
            'total_limit' => 'required|numeric|min:0|max:10000',
        ]);

        $limit->update(['total_limit' => $request->total_limit]);

        return back()->with('success', "Limit updated to M{$request->total_limit} for {$limit->user->name}.");
    }

    /**
     * Manual payday trigger for a client.
     */
    public function triggerPayday(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:0.01',
        ]);

        $user   = User::findOrFail($request->user_id);
        $result = $this->svc->processPayday(
            $user,
            (float) $request->amount,
            'MANUAL-' . now()->format('YmdHis'),
            'manual',
            auth('admin')->id()
        );

        return back()->with('success', "Payday processed: {$result['settled']} loans settled, M{$result['total_deducted']} deducted.");
    }

    /**
     * Export MyBill loans to CSV.
     */
    public function export(Request $request)
    {
        $loans = MyBillLoan::with('user')->latest()->get();
        $csv   = "Loan #,Client,Category,Bill Value,Tier,Upfront,Payday Amount,Settled,Status,Date\n";

        foreach ($loans as $l) {
            $csv .= implode(',', [
                $l->loan_number,
                '"' . ($l->user->name ?? '') . '"',
                $l->bill_category,
                $l->bill_value,
                $l->tier . '%',
                $l->upfront_amount,
                $l->payday_amount,
                $l->settled_amount,
                $l->status,
                $l->created_at->format('Y-m-d H:i'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="mybill-loans-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
