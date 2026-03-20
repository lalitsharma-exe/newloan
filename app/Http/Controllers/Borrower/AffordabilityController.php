<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffordabilityController extends Controller
{
    public function calculate(Request $request) {
        $p      = (float)($request->principal ?? 0);
        $rate   = (float)($request->rate ?? 15) / 100;
        $term   = (int)  ($request->term ?? 1);
        $initR  = (float)($request->initiation_rate ?? 40) / 100;
        $admin  = (float)($request->admin_fee ?? 50);
        $income = (float)($request->net_income ?? 0);
        $totalInt   = round($p * $rate * $term, 2);
        $totalInit  = round($p * $initR, 2);
        $totalRepay = $p + $totalInt + $totalInit + ($admin * $term);
        $monthly    = $term > 0 ? round($totalRepay / $term, 2) : 0;
        return response()->json(['monthly'=>$monthly,'total_repay'=>$totalRepay,'total_interest'=>$totalInt,'initiation_fee'=>$totalInit,'passes'=>$income > 0 && $monthly <= $income,'surplus'=>round($income - $monthly, 2)]);
    }
}
