<?php
// ─────────────────────────────────────────────────────────────
// ADD THESE ROUTES to your existing routes/admin.php
// Place them inside the existing applications route group
// ─────────────────────────────────────────────────────────────

// Inside the applications group (near the other document routes):
Route::post('/{application}/documents/upload',
    [\App\Http\Controllers\Admin\ApplicationController::class, 'uploadDocument'])
    ->name('admin.applications.documents.upload');


// ─────────────────────────────────────────────────────────────
// ADD THIS ROUTE to routes/officer.php  
// Place it inside the applications route group
// ─────────────────────────────────────────────────────────────

// Officer document upload (inside officer applications group):
Route::post('/{application}/documents/upload',
    [\App\Http\Controllers\LoanOfficer\ApplicationController::class, 'uploadDocument'])
    ->name('officer.applications.documents.upload');


// ─────────────────────────────────────────────────────────────
// ADD THIS METHOD to app/Http/Controllers/LoanOfficer/ApplicationController.php
// ─────────────────────────────────────────────────────────────

/*
    public function uploadDocument(\Illuminate\Http\Request $request, \App\Models\LoanApplication $application)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
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

        return back()->with('success', 'Document uploaded.');
    }
*/
