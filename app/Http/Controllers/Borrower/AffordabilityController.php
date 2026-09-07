<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffordabilityController extends Controller
{
    public function calculate(Request $request) {
        $p      = (float)($request->principal ?? 0);
        $rate   = (float)($request->rate ?? 20) / 100;
        $term   = (int)  ($request->term ?? 1);
        $initR  = (float)($request->initiation_rate ?? 0) / 100;
        $admin  = (float)($request->admin_fee ?? 16);
        $income = (float)($request->net_income ?? 0);
        $method = $request->interest_method ?? 'reducing';

        $totalInit  = round($p * $initR, 2);
        $totalAdmin = $admin * $term;

        if ($method === 'reducing') {
            $pmt = ($rate > 0 && $term > 0)
                ? ($p * $rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
                : ($term > 0 ? $p / $term : 0);
            $monthly = round($pmt + $admin + ($totalInit / max(1, $term)), 2);
            $totalRepay = round($monthly * $term, 2);
            $totalInt = round($totalRepay - $p - $totalInit - $totalAdmin, 2);
        } else {
            $totalInt   = round($p * $rate * $term, 2);
            $totalRepay = round($p + $totalInt + $totalInit + $totalAdmin, 2);
            $monthly    = $term > 0 ? round($totalRepay / $term, 2) : 0;
        }

        return response()->json([
            'monthly'        => $monthly,
            'total_repay'    => $totalRepay,
            'total_interest' => $totalInt,
            'initiation_fee' => $totalInit,
            'admin_fee'      => $totalAdmin,
            'passes'         => $income > 0 && $monthly <= $income,
            'surplus'        => round($income - $monthly, 2)
        ]);
    }
}
