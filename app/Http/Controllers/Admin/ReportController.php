<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $svc) {}

    public function index()                { return view('admin.reports.index'); }

    public function portfolio(Request $r)  {
        return view('admin.reports.portfolio', [
            'data'    => $this->svc->getPortfolioReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function disbursement(Request $r) {
        return view('admin.reports.disbursement', [
            'data'    => $this->svc->getDisbursementReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function repayment(Request $r) {
        return view('admin.reports.repayment', [
            'data'    => $this->svc->getRepaymentReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function arrears(Request $r) {
        return view('admin.reports.arrears', [
            'data'    => $this->svc->getArrearsReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function collections(Request $r) {
        return view('admin.reports.collections', [
            'data'    => $this->svc->getCollectionsReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function productPerformance(Request $r) {
        return view('admin.reports.product-performance', [
            'data'    => $this->svc->getProductPerformanceReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function officerPerformance(Request $r) {
        return view('admin.reports.officer-performance', [
            'data'    => $this->svc->getOfficerPerformanceReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function incomeStatement(Request $r) {
        return view('admin.reports.income-statement', [
            'data'    => $this->svc->getIncomeStatementReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function outstanding(Request $r) {
        return view('admin.reports.outstanding', ['data' => $this->svc->getOutstandingReport($r->all()), 'filters' => $r->all()]);
    }
    public function par(Request $r) {
        return view('admin.reports.par', ['data' => $this->svc->getParReport($r->all()), 'filters' => $r->all()]);
    }
    public function default(Request $r) {
        return view('admin.reports.default', ['data' => $this->svc->getDefaultReport($r->all()), 'filters' => $r->all()]);
    }
    public function applications(Request $r) {
        return view('admin.reports.applications', ['data' => $this->svc->getApplicationReport($r->all()), 'filters' => $r->all()]);
    }
    public function paymentFailures(Request $r) {
        return view('admin.reports.payment-failures', ['data' => $this->svc->getPaymentFailureReport($r->all()), 'filters' => $r->all()]);
    }
    public function borrowerDemographics(Request $r) {
        return view('admin.reports.borrower-demographics', ['data' => $this->svc->getBorrowerDemographicsReport($r->all()), 'filters' => $r->all()]);
    }

    public function collectionSheet(Request $r) {
        return view('admin.reports.collection-sheet', [
            'data'    => $this->svc->getCollectionSheetReport($r->all()),
            'filters' => $r->all(),
        ]);
    }

    public function exportCollectionSheet(Request $r) {
        $data = $this->svc->getCollectionSheetReport($r->all());
        $installments = $data['installments'];
        $month = $data['month'];

        $filename = "collection-sheet-{$month}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Name of client', 'Bank Account Number', 'Bank Branch Code', 'Instalment Amount', 'Collection Month'];

        $callback = function() use($installments, $columns, $month) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($installments as $i) {
                $loan = $i->loan;
                if (!$loan || !$loan->user) continue;

                $branchCode = $loan->application->bankDetails->branch_code ?? '';
                // Pad to 6 digits as requested
                $branchCode = str_pad($branchCode, 6, '0', STR_PAD_LEFT);
                
                fputcsv($file, [
                    $loan->user->name,
                    $loan->application->bankDetails->account_number ?? '',
                    $branchCode,
                    number_format($i->total_amount, 2, '.', ''),
                    $month
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Scheduled reports
    public function scheduledIndex() {
        $scheduled = \DB::table('scheduled_reports')->orderBy('created_at','desc')->get();
        return view('admin.reports.scheduled', compact('scheduled'));
    }

    public function scheduledStore(Request $request) {
        $request->validate([
            'report_type' => 'required|string',
            'frequency'   => 'required|in:daily,weekly,monthly',
            'email'       => 'required|email',
            'format'      => 'required|in:csv,pdf',
        ]);
        \DB::table('scheduled_reports')->insert([
            'report_type' => $request->report_type,
            'frequency'   => $request->frequency,
            'email'       => $request->email,
            'format'      => $request->format,
            'created_by'  => auth('admin')->id(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        return back()->with('success', 'Scheduled report created.');
    }

    public function scheduledDestroy($id) {
        \DB::table('scheduled_reports')->where('id', $id)->delete();
        return back()->with('success', 'Scheduled report removed.');
    }

    public function export(Request $request) {
        $type   = $request->input('type','portfolio');
        $format = $request->input('format','csv');
        return $this->svc->export($type, $format, $request->all());
    }
}
