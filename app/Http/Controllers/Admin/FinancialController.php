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
            'balance' => 'required|numeric'
        ]);

        TreasuryAccount::create($request->all());
        return back()->with('success', 'Treasury account created successfully.');
    }

    public function updateAccount(Request $request, TreasuryAccount $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,mobile_wallet,cash_float,investment',
            'institution' => 'nullable|string',
            'account_number' => 'nullable|string',
            'balance' => 'required|numeric'
        ]);

        $account->update($request->all());
        return back()->with('success', 'Treasury account updated successfully.');
    }

    public function refreshForecasts()
    {
        $this->svc->refreshForecasts();
        return back()->with('success', 'Repayment forecasts refreshed based on current borrower behavior.');
    }

    public function transfers()
    {
        $transfers = \App\Models\InternalTransfer::with(['fromAccount', 'toAccount', 'initiator', 'confirmer'])->latest()->paginate(20);
        $accounts = TreasuryAccount::where('is_active', true)->get();
        return view('admin.financial.transfers', compact('transfers', 'accounts'));
    }

    public function initiateTransfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:treasury_accounts,id',
            'to_account_id' => 'required|exists:treasury_accounts,id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
            'bank_reference' => 'required|string|max:100',
        ]);

        if ($request->from_account_id == $request->to_account_id) {
            return back()->with('error', 'Source and destination accounts must be different.');
        }

        \App\Models\InternalTransfer::create([
            'from_account_id' => $request->from_account_id,
            'to_account_id' => $request->to_account_id,
            'amount' => $request->amount,
            'transfer_date' => $request->transfer_date,
            'bank_reference' => $request->bank_reference,
            'initiated_by_user_id' => auth()->id(),
            'status' => 'pending'
        ]);

        return back()->with('success', 'Transfer initiated. Awaiting M-Pesa confirmation.');
    }

    public function confirmTransfer(Request $request, \App\Models\InternalTransfer $transfer)
    {
        $request->validate([
            'mpesa_confirmation' => 'required|string|max:100'
        ]);

        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Transfer is already processed.');
        }

        // Dual auth check for > 10,000
        if ($transfer->amount > 10000 && $transfer->initiated_by_user_id == auth()->id()) {
            return back()->with('error', 'Dual authorization required. Another admin must confirm this transfer.');
        }

        $transfer->update(['mpesa_confirmation' => $request->mpesa_confirmation]);
        $this->svc->recordInternalTransfer($transfer, auth()->id());

        return back()->with('success', 'Transfer confirmed and balances updated.');
    }
}
