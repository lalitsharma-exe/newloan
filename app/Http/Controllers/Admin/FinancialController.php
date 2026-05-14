<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{TreasuryAccount, TreasuryTransaction, OperatingExpense, RepaymentForecast};
use App\Services\Admin\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FinancialController extends Controller
{
    public function __construct(private FinancialService $svc) {}

    public function dashboard()
    {
        $stats = $this->svc->getLiquidityStats();
        $accounts = TreasuryAccount::where('is_active', true)->get();
        $recentTransactions = TreasuryTransaction::with(['account', 'recorder'])->latest()->limit(10)->get();
        
        $expenseCategories = OperatingExpense::groupBy('category')
            ->selectRaw('category, sum(amount) as total')
            ->where('status', 'paid')
            ->where('payment_date', '>=', now()->subMonth())
            ->get();

        $snapshots = \App\Models\LiquiditySnapshot::latest()
            ->limit(30)
            ->get()
            ->reverse();

        return view('admin.financial.dashboard', compact('stats', 'accounts', 'recentTransactions', 'expenseCategories', 'snapshots'));
    }

    public function accounts()
    {
        $accounts = TreasuryAccount::withCount('transactions')->get();
        return view('admin.financial.accounts', compact('accounts'));
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,mobile_wallet,cash_float,investment',
            'institution' => 'nullable|string',
            'account_number' => 'nullable|string',
            'balance' => 'required|numeric|min:0'
        ]);

        TreasuryAccount::create($request->all());
        return back()->with('success', 'Treasury account created successfully.');
    }

    public function expenses()
    {
        $expenses = OperatingExpense::with(['account', 'approver'])->latest()->paginate(20);
        $accounts = TreasuryAccount::where('is_active', true)->get();
        return view('admin.financial.expenses', compact('expenses', 'accounts'));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'is_recurring' => 'boolean',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        $data = $request->all();
        
        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        OperatingExpense::create($data);
        return back()->with('success', 'Expense recorded successfully.');
    }

    public function payExpense(Request $request, OperatingExpense $expense)
    {
        $request->validate([
            'treasury_account_id' => 'required|exists:treasury_accounts,id',
            'payment_date' => 'required|date'
        ]);

        \DB::transaction(function () use ($request, $expense) {
            $expense->update([
                'status' => 'paid',
                'payment_date' => $request->payment_date,
                'treasury_account_id' => $request->treasury_account_id,
                'approved_by' => auth()->id()
            ]);

            $this->svc->recordTransaction(
                $request->treasury_account_id,
                'expense',
                $expense->amount,
                'out',
                [
                    'description' => "Payment for: {$expense->title}",
                    'expense_id' => $expense->id,
                    'reference' => "EXP-{$expense->uuid}"
                ]
            );
        });

        return back()->with('success', 'Expense paid and ledger updated.');
    }

    public function refreshForecasts()
    {
        $this->svc->refreshForecasts();
        return back()->with('success', 'Repayment forecasts refreshed based on current borrower behavior.');
    }
}
