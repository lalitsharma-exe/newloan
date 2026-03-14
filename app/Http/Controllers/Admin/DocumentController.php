<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, Document, LoanApplication};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(LoanApplication $application) {
        $application->load(['documents.verifiedBy', 'user']);
        return view('admin.applications.show', compact('application'));
    }

    public function verify(Request $request, LoanApplication $application, Document $doc) {
        $doc->update([
            'status'      => 'verified',
            'verified_by' => auth('admin')->id(),
            'verified_at' => now(),
            'notes'       => $request->input('notes'),
        ]);
        AuditLog::record('document.verify',
            "Document '{$doc->original_name}' verified on application {$application->application_number}",
            $doc
        );
        return back()->with('success', "Document '{$doc->original_name}' verified.");
    }

    public function reject(Request $request, LoanApplication $application, Document $doc) {
        $request->validate(['reason' => 'required|string|max:500']);
        $doc->update([
            'status'      => 'rejected',
            'verified_by' => auth('admin')->id(),
            'verified_at' => now(),
            'notes'       => $request->reason,
        ]);
        AuditLog::record('document.reject',
            "Document '{$doc->original_name}' rejected on application {$application->application_number}: {$request->reason}",
            $doc
        );
        return back()->with('success', "Document '{$doc->original_name}' rejected.");
    }

    public function download(LoanApplication $application, Document $doc) {
        if (!Storage::disk('public')->exists($doc->path)) {
            return back()->with('error', 'File not found on server.');
        }
        AuditLog::record('document.download',
            "Downloaded '{$doc->original_name}' from application {$application->application_number}",
            $doc
        );
        return Storage::disk('public')->download($doc->path, $doc->original_name);
    }
}
