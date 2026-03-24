<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Illuminate\Http\Request;

class OfficerAssignmentController extends Controller
{
    /**
     * Assign an officer to the loan application.
     */
    public function assign(Request $request, LoanApplication $application)
    {
        $validated = $request->validate([
            'officer_id' => 'required|exists:users,id',
        ]);

        $application->assigned_officer_id = $validated['officer_id'];
        $application->save();

        return redirect()->back()->with('success', 'Officer assigned successfully.');
    }

    /**
     * Unassign the officer from the loan application.
     */
    public function unassign(LoanApplication $application)
    {
        $application->assigned_officer_id = null;
        $application->save();

        return redirect()->back()->with('success', 'Officer unassigned successfully.');
    }
}
