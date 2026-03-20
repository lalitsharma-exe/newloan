<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\{Loan, LoanApplication, Payment, Notification};

class DashboardController extends Controller
{
    public function index() {
        $user        = auth('borrower')->user();
        $activeLoan  = Loan::where('user_id', $user->id)->whereIn('status',['active','overdue'])->with(['installments','loanProduct'])->latest()->first();
        $nextInst    = $activeLoan?->installments->whereIn('status',['pending','overdue','partial'])->sortBy('due_date')->first();
        $applications= LoanApplication::where('user_id', $user->id)->where('status','!=','draft')->latest()->take(5)->get();
        $recentPay   = Payment::where('user_id', $user->id)->where('status','verified')->latest()->take(3)->get();
        $allLoans    = Loan::where('user_id', $user->id)->with('loanProduct')->latest()->take(5)->get();
        $unread      = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        return view('borrower.dashboard.index', compact('activeLoan','nextInst','applications','recentPay','allLoans','unread'));
    }
}
