<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CblReportService;
use App\Models\CblReportArchive;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CblReportController extends Controller
{
    protected $service;

    public function __construct(CblReportService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $archives = CblReportArchive::with('generator')->orderBy('generated_at', 'desc')->paginate(15);
        return view('admin.reports.cbl.index', compact('archives'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'segment' => 'required|in:ALL,Government,Private,SME',
        ]);

        $report = $this->service->generateReport(
            $request->start_date,
            $request->end_date,
            $request->segment
        );

        return view('admin.reports.cbl.show', compact('report'));
    }

    public function archive(Request $request)
    {
        // Store report in archive
        $payload = json_decode($request->payload, true);
        
        $archive = CblReportArchive::create([
            'report_period' => $payload['metadata']['period'],
            'payload' => $payload,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
            'data_hash' => md5(serialize($payload['data'])),
        ]);

        return redirect()->route('admin.reports.cbl.index')->with('success', 'Report archived successfully.');
    }

    public function viewArchive($id)
    {
        $archive = CblReportArchive::findOrFail($id);
        $report = $archive->payload;
        return view('admin.reports.cbl.show', compact('report', 'archive'));
    }

    public function complaints()
    {
        $complaints = Complaint::with(['user', 'loan'])->orderBy('complaint_date', 'desc')->paginate(20);
        return view('admin.reports.cbl.complaints', compact('complaints'));
    }

    public function storeComplaint(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'complaint_date' => 'required|date',
            'complaint_type' => 'required|string',
            'description' => 'required|string',
        ]);

        Complaint::create($request->all());

        return back()->with('success', 'Complaint logged successfully.');
    }

    public function searchUsers(Request $request)
    {
        $q = $request->q;
        $users = User::where('role', 'borrower')
            ->where(function($query) use ($q) {
                $query->where('name', 'LIKE', "%$q%")
                      ->orWhere('phone', 'LIKE', "%$q%")
                      ->orWhere('email', 'LIKE', "%$q%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone']);
        
        return response()->json($users);
    }
}
