<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\{Loan, LoanApplication, Payment, Notification};

class DashboardController extends Controller
{
    public function index() {
        $user       = auth('borrower')->user();
        $activeLoan = Loan::where('user_id', $user->id)
            ->whereIn('status', ['active','overdue'])
            ->with(['installments','loanProduct','payments' => fn($q) => $q->where('status','verified')->latest()])
            ->latest()->first();

        $applications = LoanApplication::where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->with('loanProduct')
            ->latest()->take(5)->get();

        $unread = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        return view('borrower.dashboard.index', compact('activeLoan','applications','unread'));
    }
}
