<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\FloatRecord;
use App\Services\Admin\FloatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FloatController extends Controller
{
    public function __construct(private FloatService $service) {}

    public function index()
    {
        $user = Auth::user();
        $activeFloat = $user->activeFloat;
        $history = $user->floatRecords()->latest()->take(5)->get();

        return view('borrower.float.index', compact('user', 'activeFloat', 'history'));
    }

    public function apply(Request $request)
    {
        try {
            $this->service->submitApplication(Auth::user(), $request->purpose);
            return redirect()->route('borrower.float.index')->with('success', 'Your Float application has been submitted for review.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
