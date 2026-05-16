<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Loan, User, LoanApplication, Payment, TreasuryAccount, AuditLog};
use App\Services\Admin\LoanService;
use App\Services\Admin\FinancialService;
use App\Services\CPayService;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{
    protected $svc;
    protected $financialSvc;
    protected $cpay;
    protected $mpesa;

    public function __construct(LoanService $svc, FinancialService $financialSvc, CPayService $cpay, MpesaService $mpesa)
    {
        $this->svc = $svc;
        $this->financialSvc = $financialSvc;
        $this->cpay = $cpay;
        $this->mpesa = $mpesa;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'product', 'overdue', 'date_from', 'date_to', 'search']);
        $loans   = $this->svc->getPaginated($filters);
        $stats   = $this->svc->getStats();
        $products = \App\Models\LoanProduct::active()->get();

        return view('admin.loans.index', compact('loans', 'stats', 'products', 'filters'));
    }

    public function show(Loan $loan)
    {
        $loan->load(['user', 'loanProduct', 'application', 'installments', 'payments.verifiedBy']);
        $accounts = TreasuryAccount::where('is_active', true)->get();
        return view('admin.loans.show', compact('loan', 'accounts'));
    }

    public function disbursementConfirm(Loan $loan)
    {
        $checks         = $this->svc->preDisbursementChecks($loan);
        $reference      = 'DISB-' . $loan->loan_number . '-' . now()->format('Ymd');
        $cpayConfigured = $this->cpay->isConfigured();
        $cpayIsSandbox  = $this->cpay->isSandbox();
        $mpesaConfigured = $this->mpesa->isConfigured();
        $accounts = TreasuryAccount::where('is_active', true)->get();
        return view('admin.loans.disburse-confirm', compact('loan','checks','reference','cpayConfigured','cpayIsSandbox','mpesaConfigured', 'accounts'));
    }

    public function disburse(Request $request, Loan $loan)
    {
        $request->validate([
            'treasury_account_id'   => 'required|exists:treasury_accounts,id',
            'disbursement_date'     => 'required|date|before_or_equal:today',
            'transaction_reference' => 'required|string|max:100',
            'notes'                 => 'nullable|string|max:500',
            'authorisation'         => 'required|accepted',
            'proof_of_payment'      => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')->store('disbursements', 'public');
        }

        try {
            $data = $request->all();
            $data['proof_path'] = $proofPath;

            $this->svc->finalizeDisbursement($loan, $data, auth('admin')->user());

            AuditLog::record('loan.disburse', "Loan {$loan->loan_number} disbursed. Ref: {$request->transaction_reference}", $loan);
            
            // Notify borrower
            try {
                $loan->user->notify(new \App\Notifications\LoanDisbursedSms($loan));
            } catch (\Throwable $e) {
                Log::error("Failed to send disbursement SMS for loan {$loan->id}: " . $e->getMessage());
            }

            return redirect()->route('admin.loans.show', $loan)->with('success', "Loan {$loan->loan_number} disbursed successfully.");
        } catch (\Exception $e) {
            Log::error("Disbursement failed for loan {$loan->id}: " . $e->getMessage());
            return back()->with('error', 'Disbursement failed: ' . $e->getMessage())->withInput();
        }
    }

    public function recordPayment(Request $request, Loan $loan)
    {
        $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'method'    => 'required|in:cash,bank_transfer,mobile_money,card,cheque',
            'treasury_account_id' => 'required|exists:treasury_accounts,id',
            'notes'     => 'nullable|string|max:500',
            'payment_date' => 'nullable|date',
        ]);
        
        $payment = $this->svc->recordManualPayment($loan, $request->all(), auth('admin')->user());
        
        // Update Treasury Ledger
        $this->financialSvc->recordTransaction(
            $request->treasury_account_id,
            'collection',
            $request->amount,
            'in',
            [
                'description' => "Repayment for Loan {$loan->loan_number}",
                'payment_id' => $payment->id,
                'loan_id' => $loan->id,
                'reference' => $payment->payment_reference
            ]
        );

        AuditLog::record('loan.record_payment', "Manual payment M{$request->amount} on {$loan->loan_number}", $loan, [], ['amount' => $request->amount, 'method' => $request->method]);
        return redirect()->route('admin.loans.show', $loan)->with('success', "Payment of M{$request->amount} recorded and treasury updated. Ref: {$payment->payment_reference}");
    }

    public function reversePayment(Request $request, Loan $loan)
    {
        $request->validate(['payment_id' => 'required|exists:payments,id', 'reason' => 'required|string|max:500']);
        $payment   = Payment::findOrFail($request->payment_id);
        $oldAmount = $payment->amount;
        $payment->update(['status' => 'reversed', 'notes' => 'Reversed: '.$request->reason]);
        if ($payment->installment_id) {
            $inst    = $payment->installment;
            $newPaid = max(0, $inst->paid_amount - $oldAmount);
            $inst->update(['paid_amount'=>$newPaid,'outstanding_amount'=>$inst->total_amount-$newPaid,'status'=>$newPaid<=0?'pending':'partial','paid_at'=>null]);
        }
        $loan->increment('outstanding_balance', $oldAmount);
        if ($loan->status === 'paid_off') $loan->update(['status' => 'active']);
        AuditLog::record('loan.reverse_payment', "Reversed M{$oldAmount} on {$loan->loan_number}: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Payment reversed.');
    }

    public function waiveInstallment(Request $request, Loan $loan)
    {
        $request->validate(['installment_id' => 'required|exists:loan_installments,id', 'reason' => 'required|string|max:300']);
        $inst = $loan->installments()->findOrFail($request->installment_id);
        $amountWaived = $inst->outstanding_amount;
        $inst->update(['status' => 'waived', 'notes' => $request->reason, 'outstanding_amount' => 0]);
        $loan->decrement('outstanding_balance', $amountWaived);
        AuditLog::record('loan.waive_installment', "Installment #{$inst->installment_number} waived on {$loan->loan_number}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Installment waived.');
    }

    public function adjust(Request $request, Loan $loan)
    {
        $this->svc->adjustSchedule($loan, $request->all(), auth('admin')->user());
        AuditLog::record('loan.adjust', "Loan schedule adjusted for {$loan->loan_number}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan schedule updated.');
    }

    public function close(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->svc->closeLoan($loan, $request->reason, auth('admin')->user());
        AuditLog::record('loan.close', "Loan {$loan->loan_number} closed: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan closed.');
    }

    public function writeOff(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->svc->writeOffLoan($loan, $request->reason, auth('admin')->user());
        AuditLog::record('loan.write_off', "Loan {$loan->loan_number} written off: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan written off.');
    }

    public function bulkRepayment(Request $request)
    {
        return view('admin.loans.bulk-repayment');
    }

    public function processBulkRepayment(Request $request)
    {
        $request->validate([
            'method' => 'required',
            'csv'    => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv');
        $data = array_map('str_getcsv', file($file->getPathname()));
        $header = array_shift($data);
        $rows = [];
        foreach ($data as $row) {
            if (count($header) == count($row)) {
                $rows[] = array_combine($header, $row);
            }
        }

        $results = $this->svc->recordBulkPayments($rows, $request->method, auth('admin')->user());
        return back()->with('success', "Processed: {$results['success']} successful, {$results['failed']} failed.")
                     ->with('bulk_errors', $results['errors']);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'product', 'overdue', 'date_from', 'date_to', 'search']);
        $loans = Loan::with(['user', 'loanProduct'])->latest()->get();

        $csv = "Loan Number,Borrower,Product,Principal,Outstanding,Status,Disbursement Date,Maturity Date\n";
        foreach ($loans as $l) {
            $csv .= implode(',', [$l->loan_number,'"'.($l->user->name??'').'"','"'.($l->loanProduct->name??'').'"',$l->principal_amount,$l->outstanding_balance,$l->status,$l->disbursement_date,$l->maturity_date])."\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="loans_export.csv"');
    }

    public function agreement(Loan $loan)
    {
        return view('admin.loans.agreement-pdf', compact('loan'));
    }

    public function statement(Loan $loan)
    {
        $loan->load(['user', 'installments', 'payments']);
        return view('admin.loans.statement', compact('loan'));
    }

    public function settlementQuotation(Loan $loan)
    {
        $outstanding = $loan->installments()->whereNotIn('status', ['paid', 'waived'])->sum('outstanding_amount');
        $validUntil = now()->addHours(48)->format('d M Y');
        return view('admin.loans.settlement-quotation', compact('loan', 'outstanding', 'validUntil'));
    }

    public function settlementLetter(Loan $loan)
    {
        $outstanding = $loan->installments()->whereNotIn('status', ['paid', 'waived'])->sum('outstanding_amount');
        $validUntil = now()->addHours(48)->format('d M Y');
        return view('admin.loans.settlement-letter', compact('loan', 'outstanding', 'validUntil'));
    }

    public function consolidatedSettlementQuotation(User $user)
    {
        $loans = $user->loans()->whereIn('status', ['active', 'overdue'])->with('installments')->get();
        $totalOutstanding = $loans->sum(function($loan) {
            return $loan->installments()->whereNotIn('status', ['paid', 'waived'])->sum('outstanding_amount');
        });
        $validUntil = now()->addHours(48)->format('d M Y');
        
        return view('admin.loans.consolidated-settlement', compact('user', 'loans', 'totalOutstanding', 'validUntil'));
    }

    public function consolidatedSettlementLetter(User $user)
    {
        $loans = $user->loans()->whereIn('status', ['active', 'overdue'])->get();
        return view('admin.loans.consolidated-settlement-letter', compact('user', 'loans'));
    }
}
