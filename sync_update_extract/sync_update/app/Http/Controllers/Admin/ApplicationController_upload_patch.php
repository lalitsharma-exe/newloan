<?php
// ADD THIS METHOD to app/Http/Controllers/Admin/ApplicationController.php
// This handles admin uploading documents on behalf of a borrower/application

// In the existing ApplicationController class, add:

    /**
     * Admin uploads a document to an application
     * Route: POST /admin/applications/{application}/documents/upload
     * Name:  admin.applications.documents.upload
     */
    public function uploadDocument(\Illuminate\Http\Request $request, \App\Models\LoanApplication $application)
    {
        $request->validate([
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'type'  => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file')->store(
            'documents/' . $application->user_id, 'public'
        );

        \App\Models\Document::create([
            'user_id'        => $application->user_id,
            'application_id' => $application->id,
            'type'           => $request->type,
            'filename'       => $request->file('file')->getClientOriginalName(),
            'original_name'  => $request->file('file')->getClientOriginalName(),
            'path'           => $path,
            'status'         => 'pending',
            'notes'          => $request->notes,
            'uploaded_by'    => auth('admin')->id(), // track who uploaded
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }
