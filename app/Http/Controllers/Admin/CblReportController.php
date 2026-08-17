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
            'complaint_date' => 'required|date|before_or_equal:today',
            'customer_first_name' => 'required|string|max:100',
            'customer_surname' => 'required|string|max:100',
            'account_number' => 'required|string',
            'customer_type' => 'required|string',
            'customer_cell_number' => 'required|numeric',
            'age_group' => 'required|string',
            'sex' => 'required|string',
            'mode_of_receipt' => 'required|string',
            'received_at_place' => 'required|string|max:150',
            'district' => 'required|string',
            'product_category' => 'required|string',
            'issue_category' => 'required|string',
            'description' => 'required|string|min:50',
        ]);

        $data = $request->all();
        
        // Auto-derived Section 1 & 2
        $date = Carbon::parse($request->complaint_date);
        $data['financial_year'] = $date->year;
        $data['reporting_period'] = 'Quarter ' . $date->quarter;
        $data['institution_id'] = 'Prosperity Loans Limited';
        $data['reference_number'] = Complaint::generateReference();
        $data['status'] = 'Pending';

        Complaint::create($data);

        return back()->with('success', 'Complaint PLL Reference generated and logged successfully.');
    }

    public function updateComplaint(Request $request, Complaint $complaint)
    {
        $request->validate([
            'status' => 'required|in:Pending,Resolved,Other',
            'status_description' => 'required|string',
            'resolved_date' => 'required_if:status,Resolved|nullable|date|after_or_equal:'.$complaint->complaint_date->format('Y-m-d'),
        ]);

        $data = $request->only(['status', 'status_description', 'resolved_date', 'amount_reimbursed', 'complainant_name_third_party']);
        
        if ($request->status === 'Resolved' && $request->resolved_date) {
            $data['working_days_to_resolve'] = Complaint::calculateWorkingDays($complaint->complaint_date, $request->resolved_date);
        }

        $complaint->update($data);

        return back()->with('success', 'Complaint status updated successfully.');
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
            ->with(['loans' => function($query) {
                $query->select('id', 'user_id', 'loan_number');
            }])
            ->limit(10)
            ->get(['id', 'name', 'phone']);
        
        return response()->json($users);
    }
}
