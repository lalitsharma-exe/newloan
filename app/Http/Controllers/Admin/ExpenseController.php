<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use App\Models\ExpenseTaxonomyItem;
use App\Models\OperatingExpense;
use App\Models\TreasuryAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('ref_code')->get();
        $accounts = TreasuryAccount::where('is_active', true)->get();
        $expenses = OperatingExpense::with(['taxonomyItem.subcategory.category', 'account', 'recorder'])
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        // KPI Data
        $mtdTotal = OperatingExpense::whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->sum('amount');
        
        $pendingTotal = OperatingExpense::where('status', 'pending')->sum('amount');
        $pendingCount = OperatingExpense::where('status', 'pending')->count();

        $categoryStats = DB::table('operating_expenses')
            ->leftJoin('expense_taxonomy_items', 'operating_expenses.taxonomy_item_id', '=', 'expense_taxonomy_items.id')
            ->leftJoin('expense_subcategories', 'expense_taxonomy_items.subcategory_id', '=', 'expense_subcategories.id')
            ->leftJoin('expense_categories', 'expense_subcategories.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_categories.name')
            ->get();

        return view('admin.finances.expenses.index', compact(
            'categories', 'accounts', 'expenses', 
            'mtdTotal', 'pendingTotal', 'pendingCount', 'categoryStats'
        ));
    }

    public function getSubcategories(Request $request)
    {
        $subcategories = ExpenseSubcategory::where('category_id', $request->category_id)
            ->orderBy('ref_code')
            ->get(['id', 'name', 'ref_code']);

        return response()->json($subcategories);
    }

    public function getTaxonomyItems(Request $request)
    {
        $items = ExpenseTaxonomyItem::where('subcategory_id', $request->subcategory_id)
            ->orderBy('ref_code')
            ->get(['id', 'name', 'ref_code']);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'taxonomy_item_id' => 'required|exists:expense_taxonomy_items,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'treasury_account_id' => 'nullable|exists:treasury_accounts,id',
            'receipt' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only(['taxonomy_item_id', 'amount', 'due_date', 'notes', 'treasury_account_id']);
            
            if ($request->hasFile('receipt')) {
                $data['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
            }

            // Get names for compatibility with old schema if needed
            $item = ExpenseTaxonomyItem::with('subcategory.category')->find($request->taxonomy_item_id);
            $data['category'] = $item->subcategory->category->name;
            $data['title'] = $item->name;
            $data['status'] = $request->filled('payment_date') ? 'paid' : 'pending';
            $data['payment_date'] = $request->payment_date;

            $expense = OperatingExpense::create($data);

            DB::commit();
            return back()->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording expense: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $expenses = OperatingExpense::with(['taxonomyItem.subcategory.category', 'recorder'])->get();
        
        $filename = "myloan_expenses_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Header
        fputcsv($handle, ['ID', 'Reference', 'Category', 'Subcategory', 'Title', 'Amount', 'Date', 'Status', 'Recorded By']);
        
        foreach ($expenses as $e) {
            fputcsv($handle, [
                $e->id,
                $e->taxonomyItem->ref_code ?? 'N/A',
                $e->taxonomyItem->subcategory->category->name ?? 'N/A',
                $e->taxonomyItem->subcategory->name ?? 'N/A',
                $e->title,
                $e->amount,
                $e->due_date->format('Y-m-d'),
                $e->status,
                $e->recorder->name ?? 'System'
            ]);
        }
        
        fclose($handle);
        exit;
    }

    public function pay(Request $request, OperatingExpense $expense)
    {
        $request->validate([
            'treasury_account_id' => 'required|exists:treasury_accounts,id',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $account = TreasuryAccount::findOrFail($request->treasury_account_id);
            
            if ($account->balance < $expense->amount) {
                throw new \Exception('Insufficient balance in selected account.');
            }

            // Update account balance
            $account->decrement('balance', $expense->amount);

            // Update expense status
            $expense->update([
                'status' => 'paid',
                'payment_date' => $request->payment_date,
                'treasury_account_id' => $request->treasury_account_id
            ]);

            // Record treasury transaction
            \App\Models\TreasuryTransaction::create([
                'treasury_account_id' => $account->id,
                'type' => 'expense',
                'amount' => $expense->amount,
                'direction' => 'out',
                'reference' => $expense->uuid,
                'description' => "Expense Payment: {$expense->title}",
                'expense_id' => $expense->id,
                'recorded_by' => auth()->id()
            ]);

            DB::commit();
            return back()->with('success', 'Expense marked as paid and account balance updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
