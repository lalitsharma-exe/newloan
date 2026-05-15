<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloatRecord;
use App\Models\User;
use App\Services\Admin\FloatService;
use Illuminate\Http\Request;

class FloatController extends Controller
{
    public function __construct(private FloatService $service) {}

    public function index(Request $request)
    {
        $query = FloatRecord::with('user')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(20);
        
        $stats = [
            'pending' => FloatRecord::where('status', 'pending')->count(),
            'active' => FloatRecord::whereIn('status', ['disbursed', 'due', 'overdue'])->count(),
            'overdue' => FloatRecord::where('status', 'overdue')->count(),
            'total_collected' => 0, // Logic for payments
        ];

        return view('admin.float.index', compact('records', 'stats'));
    }

    public function show(FloatRecord $float)
    {
        $float->load(['user', 'penaltyLogs']);
        $affordability = $this->service->calculateAffordability($float->user);
        return view('admin.float.show', compact('float', 'affordability'));
    }

    public function approve(FloatRecord $float)
    {
        $float->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // In a real system, this might trigger a disbursement job
        return back()->with('success', 'Float application approved.');
    }

    public function reject(Request $request, FloatRecord $float)
    {
        $float->update([
            'status' => 'rejected',
            'admin_notes' => $request->reason,
        ]);

        return back()->with('success', 'Float application rejected.');
    }

    public function disburse(FloatRecord $float)
    {
        $float->update([
            'status' => 'disbursed',
            'disbursed_at' => now(),
            'due_date' => now()->endOfMonth(),
        ]);

        return back()->with('success', 'Float marked as disbursed.');
    }

    public function freeze(Request $request, User $user)
    {
        $user->update([
            'float_frozen' => true,
            'float_freeze_reason' => $request->reason,
        ]);

        return back()->with('success', 'User float access frozen.');
    }
}
