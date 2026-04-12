<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, Loan, LoanProduct, Payment};
use App\Services\Admin\LoanService;
use App\Services\CPayService;
use App\Services\MpesaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use User;

class LoanController extends Controller
{
    public function __construct(
        private LoanService $svc,
        private CPayService $cpay,
        private MpesaService $mpesa
    ) {}

    public function index(Request $request)
    {
        return view('admin.loans.index', [
            'loans'   => $this->svc->getPaginated($request->all()),
            'stats'   => $this->svc->getStats(),
            'products'=> LoanProduct::all(),
            'filters' => $request->only(['status','product','search','date_from','date_to']),
        ]);
    }

    public function show(Loan $loan)
    {
        $loan->load(['user','loanProduct','application.bankDetails','installments','payments.verifiedBy']);
        return view('admin.loans.show', compact('loan'));
    }

    public function schedule(Loan $loan)
    {
        $loan->load(['installments','loanProduct','user']);
        return view('admin.loans.schedule', compact('loan'));
    }

    // ── Disbursement confirmation page ───────────────────────────────────────
    public function disbursementConfirm(Loan $loan)
    {
        $loan->load(['user','loanProduct','application.bankDetails','application.documents','application.affordability']);
        $checks         = $this->svc->preDisbursementChecks($loan);
        $reference      = 'DISB-' . $loan->loan_number . '-' . now()->format('Ymd');
        $cpayConfigured = $this->cpay->isConfigured();
        $cpayIsSandbox  = $this->cpay->isSandbox();
        $mpesaConfigured = $this->mpesa->isConfigured();
        return view('admin.loans.disburse-confirm', compact('loan','checks','reference','cpayConfigured','cpayIsSandbox','mpesaConfigured'));
    }

    // ── DISBURSE — Mobile Money + Bank Transfer + Cash + CPay Wallet ─────────
    public function disburse(Request $request, Loan $loan)
    {
        $request->validate([
            'disbursement_date'      => 'required|date',
            'disbursement_reference' => 'required|string|max:80',
            'disbursement_method'    => 'required|in:bank_transfer,cash,cpay_wallet,mpesa_b2c',
            'disbursement_phone'     => 'nullable|string|max:30',
            'disbursement_provider'  => 'nullable|string|max:50',
            'confirm'                => 'required|accepted',
        ]);

        $checks  = $this->svc->preDisbursementChecks($loan);
        $blocked = collect($checks)->contains(fn($c) => !$c['pass'] && $c['required']);
        if ($blocked) {
            return back()->with('error', 'Disbursement blocked — resolve required check failures first.');
        }

        $method    = $request->disbursement_method;
        $phone     = $request->disbursement_phone ?? $loan->user->phone;
        $provider  = $request->disbursement_provider ?? 'MPESA';
        $reference = $request->disbursement_reference;
        $disbDate  = Carbon::parse($request->disbursement_date);

        // ── CPay API Disbursement (auto for non-cash if configured) ───────────
        $cpayTxnId  = null;
        $cpayStatus = 'manual';
        $cpayError  = null;

        // bank_transfer is always recorded manually (EFT/bank processing happens outside system)
        // cpay_wallet uses the CPay wallet-topup-advance API
        if ($method === 'cpay_wallet' && $this->cpay->isConfigured()) {
            $result = $this->cpay->disburseToWallet($loan, $phone, $reference);

            if ($result['success']) {
                $cpayTxnId  = $result['cpay_txn_id'];
                $cpayStatus = $result['status'];
            } else {
                $cpayError = $result['error'];
                Log::warning('CPay disbursement failed — recorded manually', [
                    'loan' => $loan->loan_number, 'error' => $cpayError,
                ]);
            }
        }

        // ── M-Pesa B2C Disbursement ───────────────────────────────────────────
        if ($method === 'mpesa_b2c' && $this->mpesa->isConfigured()) {
            $result = $this->mpesa->disburseLoan($loan, $phone, $reference);

            if ($result['success']) {
                $cpayTxnId  = $result['conversation_id']; // Store it in the same field for now or log it
                $cpayStatus = 'accepted';
            } else {
                $cpayError = $result['error'];
                Log::warning('M-Pesa disbursement failed', [
                    'loan' => $loan->loan_number, 'error' => $cpayError,
                ]);
            }
        }

        // ── Block if CPay API failed ──────────────────────────────────────────
        // (Admins can still record manually by selecting 'cash' or turning off API)
        if ($cpayError && $method !== 'cash') {
            return back()->with('error', "CPay API Disbursement FAILED: {$cpayError}. The loan status has NOT been updated. Please check the logs or try again.")
                         ->withInput();
        }

        // ── Update loan record ────────────────────────────────────────────────
        $loan->update([
            'status'                 => 'active',
            'disbursement_date'      => $disbDate->toDateString(),
            'disbursement_reference' => $reference,
            'disbursement_method'    => $method,
            'disbursement_phone'     => $phone,
            'disbursement_provider'  => $cpayTxnId 
                ? ($method === 'mpesa_b2c' ? "M-Pesa:{$cpayTxnId}" : "CPay:{$cpayTxnId}") 
                : $provider,
            'first_payment_date'     => $disbDate->copy()->addMonth()->setDay($loan->salary_payday ?? $loan->application?->salary_payday ?? 25)->toDateString(),
            'maturity_date'          => $disbDate->copy()->addMonths($loan->term_months)->setDay($loan->salary_payday ?? $loan->application?->salary_payday ?? 25)->toDateString(),
        ]);

        if ($loan->installments()->count() === 0) {
            $this->svc->generateInstallments($loan);
        }

        if ($loan->application) {
            $loan->application->update(['status' => 'disbursed', 'decided_at' => now()]);
        }

        AuditLog::record(
            'loan.disburse',
            "Loan {$loan->loan_number} disbursed via {$method}. Ref: {$reference}" .
                ($cpayTxnId ? " | CPay TXN: {$cpayTxnId} ({$cpayStatus})" : '') .
                ($cpayError  ? " | CPay Error: {$cpayError}" : ''),
            $loan, [],
            ['method' => $method, 'reference' => $reference, 'cpay_txn' => $cpayTxnId]
        );

        $msg = "Loan {$loan->loan_number} disbursed successfully.";
        if ($cpayTxnId) $msg .= " CPay TXN: {$cpayTxnId} — status: {$cpayStatus}.";
        if ($cpayError) $msg .= " ⚠️ CPay API error: {$cpayError} — please verify manually.";
        if ($method === 'cash') $msg .= ' Cash disbursement recorded.';

        return redirect()->route('admin.loans.show', $loan)->with('success', $msg);
    }

    // ── Manual payment (admin records cash/bank/etc) ──────────────────────────
    public function recordPayment(Request $request, Loan $loan)
    {
        $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'method'    => 'required|in:cash,bank_transfer,mobile_money,card,cheque',
            'notes'     => 'nullable|string|max:500',
            'paid_date' => 'nullable|date',
        ]);
        $payment = $this->svc->recordManualPayment($loan, $request->all(), auth('admin')->user());
        AuditLog::record('loan.record_payment', "Manual payment M{$request->amount} on {$loan->loan_number}", $loan, [], ['amount' => $request->amount, 'method' => $request->method]);
        return redirect()->route('admin.loans.show', $loan)->with('success', "Payment of M{$request->amount} recorded. Ref: {$payment->payment_reference}");
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
        $inst->update(['status' => 'waived', 'notes' => $request->reason]);
        AuditLog::record('loan.waive_installment', "Installment #{$inst->installment_number} waived on {$loan->loan_number}", $loan);
        return back()->with('success', "Installment #{$inst->installment_number} waived.");
    }

    public function addLateFee(Request $request, Loan $loan)
    {
        $request->validate(['installment_id' => 'required|exists:loan_installments,id', 'fee' => 'required|numeric|min:0.01']);
        $inst = $loan->installments()->findOrFail($request->installment_id);
        $inst->increment('late_fee', $request->fee);
        $inst->increment('total_amount', $request->fee);
        $inst->increment('outstanding_amount', $request->fee);
        AuditLog::record('loan.add_late_fee', "Late fee M{$request->fee} on installment #{$inst->installment_number} of {$loan->loan_number}", $loan);
        return back()->with('success', "Late fee M{$request->fee} added.");
    }

    public function adjustSchedule(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500', 'new_term' => 'nullable|integer|min:1']);
        $this->svc->adjustSchedule($loan, $request->all(), auth('admin')->user());
        return back()->with('success', 'Repayment schedule adjusted.');
    }

    public function close(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->svc->closeLoan($loan, $request->reason, auth('admin')->user());
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan closed.');
    }

    public function writeOff(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $loan->update(['status' => 'written_off', 'closed_reason' => $request->reason, 'closed_at' => now(), 'closed_by' => auth('admin')->id()]);
        AuditLog::record('loan.write_off', "Loan {$loan->loan_number} written off: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan written off.');
    }

    public function markDefaulted(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $loan->update(['status' => 'defaulted']);
        AuditLog::record('loan.mark_defaulted', "Loan {$loan->loan_number} marked defaulted: {$request->reason}", $loan);
        return back()->with('success', 'Loan marked as defaulted.');
    }

    public function restructure(Request $request, Loan $loan)
    {
        $request->validate(['new_term' => 'required|integer|min:1|max:120', 'reason' => 'required|string|max:500']);
        $this->svc->adjustSchedule($loan, $request->all(), auth('admin')->user());
        AuditLog::record('loan.restructure', "Loan {$loan->loan_number} restructured to {$request->new_term} months", $loan);
        return back()->with('success', 'Loan restructured.');
    }

    public function statement(Loan $loan)   { $loan->load(['user','loanProduct','installments','payments']); return view('admin.loans.statement', compact('loan')); }
    public function agreement(Loan $loan)   { $loan->load(['user','loanProduct','application.bankDetails']); return view('admin.loans.agreement-pdf', compact('loan')); }
    public function receipt(Loan $loan, Payment $payment) { return view('admin.loans.receipt', compact('loan','payment')); }

    public function settlementQuotation(Loan $loan)
    {
        $loan->load(['user','loanProduct','installments']);
        $outstanding = $loan->installments()->whereNotIn('status',['paid','waived'])->sum('outstanding_amount');
        $validDate = now()->day > 24 ? now()->addMonth()->day(24) : now()->day(24);
        $validUntil = $validDate->format('d M Y');
        return view('admin.loans.settlement-quotation', compact('loan','outstanding','validUntil'));
    }

    public function settlementLetter(Loan $loan) { $loan->load(['user','loanProduct']); return view('admin.loans.settlement-letter', compact('loan')); }

    public function export(Request $request)
    {
        $loans = $this->svc->getPaginated($request->all(), 9999);
        $csv = "Loan #,Borrower,Product,Principal,Outstanding,Status,Disbursed,Maturity\n";
        foreach ($loans as $l) {
            $csv .= implode(',', [$l->loan_number,'"'.($l->user->name??'').'"','"'.($l->loanProduct->name??'').'"',$l->principal_amount,$l->outstanding_balance,$l->status,$l->disbursement_date,$l->maturity_date])."\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="loans-'.now()->format('Y-m-d').'.csv"']);
    }

    public function overdue(Request $request) { return $this->index($request->merge(['status'=>'overdue'])); }

    public function lookup(Request $request)
    {
        $s = $request->input('q','');
        return response()->json(
            Loan::with('user')->where(fn($q)=>$q->where('loan_number','like',"%{$s}%")->orWhereHas('user',fn($u)=>$u->where('name','like',"%{$s}%")->orWhere('phone','like',"%{$s}%")))->limit(10)->get(['id','loan_number','user_id','status','outstanding_balance'])
        );
    }

    public function bulkRepayment(Request $request)
    {
        if ($request->isMethod('get')) return view('admin.loans.bulk-repayment');
        $request->validate(['method'=>'required|string','rows'=>'required|array|min:1|max:30','rows.*.loan_number'=>'required|string','rows.*.amount'=>'required|numeric|min:0.01']);
        $result = $this->svc->recordBulkPayments($request->rows, $request->method, auth('admin')->user());
        return back()->with('success', "{$result['success']} payments recorded. {$result['failed']} failed.");
    }

    public function importLoans(Request $request)
    {
        if ($request->isMethod('get')) return view('admin.loans.import');
        $request->validate(['file'=>'required|file|mimes:csv,txt|max:5120']);
        $rows   = array_map('str_getcsv', file($request->file('file')->path()));
        $header = array_map('trim', array_shift($rows));
        $data   = array_map(fn($r)=>array_combine($header,array_map('trim',$r)), $rows);
        $result = $this->svc->importLoansFromCsv($data, auth('admin')->user());
        return back()->with('success', "{$result['imported']} loans imported. {$result['skipped']} skipped.");
    }

    public function collectionSheet(Request $request)
    {
        $date      = $request->input('date', today()->format('Y-m-d'));
        $officerId = $request->input('officer_id');

        $officers = \App\Models\User::loanOfficers()
            ->orderBy('name')
            ->get(['id', 'name']);

        $data = $this->svc->getCollectionSheet($date, $officerId);

        return view('admin.loans.collection-sheet', compact('data', 'date', 'officers', 'officerId'));
    }

    public function repaymentChart(Request $request)
    {
        return view('admin.loans.repayment-chart', ['data' => $this->svc->getRepaymentChartData()]);
    }

    // ── Edit loan details (payday, payout, collection) ────────────────────────
    public function updateDetails(Request $request, Loan $loan)
    {
        $request->validate([
            'salary_payday'    => 'required|integer|min:1|max:31',
            'payout_method'    => 'required|string',
            'collection_method'=> 'required|string',
            'edit_reason'      => 'required|string|max:500',
        ]);

        $oldPayday = $loan->salary_payday;
        $loan->update([
            'salary_payday'     => $request->salary_payday,
            'payout_method'     => $request->payout_method,
            'collection_method' => $request->collection_method,
        ]);

        // Sync application too
        if ($loan->application) {
            $loan->application->update([
                'salary_payday'     => $request->salary_payday,
                'payout_method'     => $request->payout_method,
                'collection_method' => $request->collection_method,
            ]);
        }

        // If payday changed, update future (unpaid) instalment due dates
        if ($oldPayday != $request->salary_payday) {
            $loan->installments()
                ->whereNotIn('status', ['paid', 'waived'])
                ->get()
                ->each(function ($inst) use ($request) {
                    $inst->update([
                        'due_date' => $inst->due_date->setDay(
                            min($request->salary_payday, $inst->due_date->daysInMonth)
                        ),
                    ]);
                });
        }

        AuditLog::record(
            'loan.edit_details',
            "Loan {$loan->loan_number} details edited. Reason: {$request->edit_reason}",
            $loan, [],
            ['payday' => $request->salary_payday, 'payout' => $request->payout_method, 'collection' => $request->collection_method]
        );

        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan details updated successfully.');
    }

}
