<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\{Document, LoanApplication};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Verification queue — all pending docs on assigned applications
    public function verificationQueue()
    {
        $officer = auth('officer')->user();
        $docs = Document::with(['application.user', 'application.loanProduct'])
            ->whereHas('application', fn($q) => $q->where('assigned_officer_id', $officer->id))
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
        return view('officer.documents.index', compact('docs'));
    }

    public function pending()
    {
        return $this->verificationQueue();
    }

    public function index(LoanApplication $application, Request $request)
    {
        $docs = $application->documents()->latest()->get();
        return view('officer.applications.documents', compact('application', 'docs'));
    }

    public function show(Document $doc)
    {
        return view('officer.documents.show', compact('doc'));
    }

    public function verify(Request $request, $applicationOrDoc, Document $doc = null)
    {
        // Handle both route signatures
        if ($doc === null) {
            $doc = Document::findOrFail($applicationOrDoc);
        }
        $request->validate(['notes' => 'nullable|string|max:500']);
        $doc->update([
            'status'      => 'verified',
            'verified_by' => auth('officer')->id(),
            'verified_at' => now(),
            'notes'       => $request->notes,
        ]);
        return back()->with('success', 'Document verified.');
    }

    public function reject(Request $request, $applicationOrDoc, Document $doc = null)
    {
        if ($doc === null) {
            $doc = Document::findOrFail($applicationOrDoc);
        }
        $request->validate(['notes' => 'required|string|max:500']);
        $doc->update([
            'status' => 'rejected',
            'notes'  => $request->notes,
        ]);
        return back()->with('success', 'Document rejected.');
    }

    public function request(Request $request, LoanApplication $application)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => 'document_request',
            'content'     => 'Documents requested: ' . $request->message,
            'is_internal' => false,
        ]);
        return back()->with('success', 'Document request sent to borrower.');
    }

    public function download($applicationOrDoc, Document $doc = null)
    {
        if ($doc === null) {
            $doc = Document::findOrFail($applicationOrDoc);
        }
        if (!Storage::disk('public')->exists($doc->path)) {
            return back()->with('error', 'File not found.');
        }
        return Storage::disk('public')->download($doc->path, $doc->original_name);
    }

    public function view($applicationOrDoc, Document $doc = null)
    {
        if ($doc === null) {
            $doc = Document::findOrFail($applicationOrDoc);
        }
        if (!Storage::disk('public')->exists($doc->path)) {
            return back()->with('error', 'File not found.');
        }
        $file = Storage::disk('public')->get($doc->path);
        $type = Storage::disk('public')->mimeType($doc->path);
        return response($file, 200)
                ->header('Content-Type', $type)
                ->header('Content-Disposition', 'inline; filename="' . $doc->original_name . '"');
    }
}
