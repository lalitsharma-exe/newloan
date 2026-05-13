<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\{Document, LoanApplication};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index() {
        $documents = Document::where('user_id', auth('borrower')->id())->with('application')->latest()->paginate(15);
        return view('borrower.documents.index', compact('documents'));
    }

    public function show(Document $document) {
        abort_if($document->user_id !== auth('borrower')->id(), 403);
        return view('borrower.documents.show', compact('document'));
    }

    public function showUpload() {
        $applications = LoanApplication::where('user_id', auth('borrower')->id())->whereIn('status',['draft','submitted','under_review','info_requested'])->latest()->get();
        return view('borrower.documents.upload', compact('applications'));
    }

    public function upload(Request $request) {
        $request->validate(['file'=>'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240','type'=>'required|string','application_id'=>'nullable|exists:loan_applications,id']);
        $path = $request->file('file')->store('documents/'.auth('borrower')->id(), 'public');
        Document::create(['user_id'=>auth('borrower')->id(),'application_id'=>$request->application_id,'type'=>$request->type,'filename'=>$request->file('file')->getClientOriginalName(),'original_name'=>$request->file('file')->getClientOriginalName(),'path'=>$path,'status'=>'pending']);
        return redirect()->route('borrower.documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function uploadForApplication(Request $request, LoanApplication $application) {
        abort_if($application->user_id !== auth('borrower')->id(), 403);
        $request->validate(['file'=>'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240','type'=>'required|string']);
        $path = $request->file('file')->store('documents/'.auth('borrower')->id(), 'public');
        Document::create(['user_id'=>auth('borrower')->id(),'application_id'=>$application->id,'type'=>$request->type,'filename'=>$request->file('file')->getClientOriginalName(),'original_name'=>$request->file('file')->getClientOriginalName(),'path'=>$path,'status'=>'pending']);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Document uploaded.']);
        }

        return back()->with('success', 'Document uploaded.');
    }

    public function download(Document $document) {
        abort_if($document->user_id !== auth('borrower')->id(), 403);
        if (!Storage::disk('public')->exists($document->path)) return back()->with('error', 'File not found.');
        return Storage::disk('public')->download($document->path, $document->original_name);
    }

    public function destroy(Document $document) {
        abort_if($document->user_id !== auth('borrower')->id(), 403);
        if ($document->status !== 'pending') return back()->with('error', 'Verified documents cannot be deleted.');
        Storage::disk('public')->delete($document->path);
        $document->delete();
        return back()->with('success', 'Document removed.');
    }
}
