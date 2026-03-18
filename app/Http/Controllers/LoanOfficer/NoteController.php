<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\{LoanApplication, ApplicationNote};
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request, LoanApplication $application)
    {
        $request->validate(['content' => 'required|string|max:2000', 'type' => 'nullable|string|max:50']);
        $application->notes()->create([
            'created_by'  => auth('officer')->id(),
            'type'        => $request->input('type', 'general'),
            'content'     => $request->content,
            'is_internal' => true,
        ]);
        return back()->with('success', 'Note added.');
    }

    public function update(Request $request, LoanApplication $application, ApplicationNote $note)
    {
        $request->validate(['content' => 'required|string|max:2000']);
        if ($note->created_by !== auth('officer')->id()) return back()->with('error', 'You can only edit your own notes.');
        $note->update(['content' => $request->content]);
        return back()->with('success', 'Note updated.');
    }

    public function destroy(LoanApplication $application, ApplicationNote $note)
    {
        if ($note->created_by !== auth('officer')->id()) return back()->with('error', 'You can only delete your own notes.');
        $note->delete();
        return back()->with('success', 'Note deleted.');
    }
}
