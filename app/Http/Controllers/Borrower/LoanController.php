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
    public function update(Loan $loan) {
        // unused
    }
    public function settlement(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        $loan->load(['user','loanProduct','installments']);
        $outstanding = (float) $loan->outstanding_balance;
        $validDate = now()->day > 24 ? now()->addMonth()->day(24) : now()->day(24);
        $validUntil = $validDate->format('d M Y');
        return view('admin.loans.settlement-quotation', compact('loan','outstanding','validUntil'));
    }

    public function settlementLetter(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        abort_if(!in_array($loan->status, ['paid_off', 'closed']), 403);
        $loan->load(['user','loanProduct']);
        $totalPaid = $loan->payments()->where('status', 'verified')->sum('amount');
        return view('admin.loans.settlement-letter', compact('loan', 'totalPaid'));
    }
}
