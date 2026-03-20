<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\Loan;

class LoanController extends Controller
{
    public function index() {
        $loans = Loan::where('user_id', auth('borrower')->id())->with('loanProduct')->latest()->paginate(10);
        return view('borrower.loans.index', compact('loans'));
    }
    public function show(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        $loan->load(['loanProduct','installments','payments','application']);
        return view('borrower.loans.show', compact('loan'));
    }
    public function schedule(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        $loan->load(['loanProduct','installments']);
        return view('borrower.loans.schedule', compact('loan'));
    }
    public function downloadAgreement(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        $loan->load(['loanProduct','installments','application.employment','application.bankDetails']);
        return view('admin.loans.agreement-pdf', compact('loan'));
    }
    public function statement(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        $loan->load(['loanProduct','installments','payments']);
        return view('borrower.loans.statement', compact('loan'));
    }
}
