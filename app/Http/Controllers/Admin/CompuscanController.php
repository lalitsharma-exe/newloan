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
        
        $filename = 'compuscan_';
        
        try {
            if ($request->submission_type === 'daily') {
                $content = $this->compuscanService->buildDailyFile($date);
                $filename .= 'daily_' . str_replace('-', '', $date);
            } else {
                $content = $this->compuscanService->buildMonthlyFile($date);
                $filename .= 'monthly_' . str_replace('-', '', $date);
            }
            $filename .= '.txt';

            return response($content, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating file: ' . $e->getMessage());
        }
    }
}
