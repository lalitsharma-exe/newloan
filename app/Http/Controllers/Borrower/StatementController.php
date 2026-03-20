<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    public function index() {
        $loans = Loan::where('user_id', auth('borrower')->id())->with('loanProduct')->latest()->get();
        return view('borrower.statements.index', compact('loans'));
    }
    public function loanStatement(Loan $loan) {
        abort_if($loan->user_id !== auth('borrower')->id(), 403);
        $loan->load(['loanProduct','installments','payments']);
        return view('borrower.loans.statement', compact('loan'));
    }
    public function generate(Request $request) {
        $loan = Loan::where('user_id', auth('borrower')->id())->findOrFail($request->loan_id);
        return redirect()->route('borrower.statements.loan', $loan);
    }
    public function download(Request $request, $statement) {
        return redirect()->route('borrower.statements.index');
    }
}
