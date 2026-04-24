<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompuscanService;
use Carbon\Carbon;

class CompuscanController extends Controller
{
    private CompuscanService $compuscanService;

    public function __construct(CompuscanService $compuscanService)
    {
        $this->compuscanService = $compuscanService;
    }

    public function index()
    {
        return view('admin.compuscan.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'submission_type' => 'required|in:daily,monthly',
            'target_date' => 'required|date',
        ]);

        $date = Carbon::parse($request->target_date)->format('Y-m-d');
        
        $srn = env('COMPUSCAN_SRN', 'LSO250');
        $formattedDate = str_replace('-', '', $date);
        
        try {
            if ($request->submission_type === 'daily') {
                $content = $this->compuscanService->buildDailyFile($date);
                $filename = "{$srn}_CS_L702_D_{$formattedDate}_1_1.txt";
            } else {
                $content = $this->compuscanService->buildMonthlyFile($date);
                $filename = "{$srn}_CS_L702_M_{$formattedDate}_1_1.txt";
            }

            return response($content, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating file: ' . $e->getMessage());
        }
    }
}
