<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankBranch;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::withCount('branches')->orderBy('name')->get();
        return view('admin.banks.index', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:banks,name']);
        Bank::create($request->all());
        return back()->with('success', 'Bank added successfully.');
    }

    public function update(Request $request, Bank $bank)
    {
        $request->validate(['name' => 'required|string|unique:banks,name,' . $bank->id]);
        $bank->update($request->all());
        return back()->with('success', 'Bank updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return back()->with('success', 'Bank removed successfully.');
    }

    public function branches(Bank $bank)
    {
        $branches = $bank->branches()->orderBy('name')->get();
        return view('admin.banks.branches', compact('bank', 'branches'));
    }

    public function storeBranch(Request $request, Bank $bank)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
        ]);
        $bank->branches()->create($request->all());
        return back()->with('success', 'Branch added successfully.');
    }

    public function updateBranch(Request $request, Bank $bank, BankBranch $branch)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
        ]);
        $branch->update($request->all());
        return back()->with('success', 'Branch updated successfully.');
    }

    public function destroyBranch(Bank $bank, BankBranch $branch)
    {
        $branch->delete();
        return back()->with('success', 'Branch removed successfully.');
    }

    // API for wizard
    public function getBanks()
    {
        return response()->json(Bank::orderBy('name')->get());
    }

    public function getBranches(Bank $bank)
    {
        return response()->json($bank->branches()->orderBy('name')->get());
    }
}
