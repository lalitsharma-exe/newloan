<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, Loan, LoanInstallment, LoanProduct, Payment, User};
use App\Services\Admin\LoanService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoanController extends Controller
{
    public function __construct(private LoanService $svc) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status','product','date_from','date_to','search','overdue']);
        return view('admin.loans.index', [
            'loans'    => $this->svc->getPaginated($filters),
            'stats'    => $this->svc->getStats(),
            'products' => LoanProduct::active()->get(),
            'filters'  => $filters,
        ]);
    }

    public function show(Loan $loan)
    {
        $loan->load(['user','loanProduct','application','installments','payments.verifiedBy']);
        return view('admin.loans.show', compact('loan'));
    }

    public function schedule(Loan $loan)
    {
        $loan->load(['user','loanProduct','installments']);
        return view('admin.loans.schedule', compact('loan'));
    }

    // ── Disbursement confirmation screen ────────────────────────────
    public function disbursementConfirm(Loan $loan)
    {
        $loan->load(['user','loanProduct','application.bankDetails','application.affordability','application.documents']);

        $checks    = $this->svc->preDisbursementChecks($loan);
        $reference = $loan->disbursement_reference ?? 'MYLOAN-' . strtoupper($loan->loan_number);

        return view('admin.loans.disburse-confirm', compact('loan', 'checks', 'reference'));
    }

    // ── Execute disbursement ─────────────────────────────────────────
    public function disburse(Request $request, Loan $loan)
    {
        $request->validate([
            'disbursement_date'      => 'required|date',
            'disbursement_reference' => 'required|string|max:50',
            'disbursement_method'    => 'required|in:mobile_money,bank_transfer,cash',
            'disbursement_phone'     => 'nullable|string|max:30',
            'disbursement_provider'  => 'nullable|string|max:50',
            'confirm'                => 'required|accepted',
        ]);

        $checks  = $this->svc->preDisbursementChecks($loan);
        $blocked = collect($checks)->contains(fn($c) => !$c['pass'] && $c['required']);
        if ($blocked) {
            return back()->with('error', 'Disbursement blocked. Please resolve all pre-disbursement check failures first.');
        }

        $disbDate = Carbon::parse($request->disbursement_date);

        $loan->update([
            'status'                 => 'active',
            'disbursement_date'      => $disbDate->toDateString(),
            'disbursement_reference' => $request->disbursement_reference,
            'disbursement_method'    => $request->disbursement_method,
            'disbursement_phone'     => $request->disbursement_phone,
            'disbursement_provider'  => $request->disbursement_provider,
            'first_payment_date'     => $disbDate->copy()->addMonth()->startOfMonth()->toDateString(),
            'maturity_date'          => $disbDate->copy()->addMonths($loan->term_months)->toDateString(),
        ]);

        if ($loan->installments()->count() === 0) {
            $this->svc->generateInstallments($loan);
        }

        if ($loan->application) {
            $loan->application->update(['status' => 'disbursed']);
        }

        AuditLog::record(
            'loan.disburse',
            "Loan {$loan->loan_number} disbursed via {$request->disbursement_method}. Ref: {$request->disbursement_reference}",
            $loan,
            [],
            ['method' => $request->disbursement_method, 'reference' => $request->disbursement_reference]
        );

        return redirect()->route('admin.loans.show', $loan)
            ->with('success', "Loan {$loan->loan_number} disbursed successfully. Reference: {$request->disbursement_reference}");
    }

    // ── Record manual payment ────────────────────────────────────────
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
        return redirect()->route('admin.loans.show', $loan)->with('success', "Payment of M{$request->amount} recorded.");
    }

    public function reversePayment(Request $request, Loan $loan)
    {
        $request->validate(['payment_id' => 'required|exists:payments,id', 'reason' => 'required|string|max:500']);
        $payment = Payment::findOrFail($request->payment_id);
        $oldAmount = $payment->amount;
        $payment->update(['status' => 'reversed', 'notes' => 'Reversed: '.$request->reason]);
        if ($payment->installment_id) {
            $inst    = $payment->installment;
            $newPaid = max(0, $inst->paid_amount - $oldAmount);
            $inst->update(['paid_amount' => $newPaid, 'outstanding_amount' => $inst->total_amount - $newPaid, 'status' => $newPaid <= 0 ? 'pending' : 'partial', 'paid_at' => null]);
        }
        $loan->increment('outstanding_balance', $oldAmount);
        if ($loan->status === 'paid_off') $loan->update(['status' => 'active']);
        AuditLog::record('loan.reverse_payment', "Reversed payment M{$oldAmount} on {$loan->loan_number}: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Payment reversed.');
    }

    public function waiveInstallment(Request $request, Loan $loan)
    {
        $request->validate(['installment_id' => 'required|exists:loan_installments,id', 'reason' => 'required|string|max:500']);
        $inst   = LoanInstallment::findOrFail($request->installment_id);
        $waived = $inst->outstanding_amount;
        $inst->update(['status' => 'waived', 'outstanding_amount' => 0, 'paid_at' => now()]);
        $loan->decrement('outstanding_balance', $waived);
        AuditLog::record('loan.waive_installment', "Waived installment #{$inst->installment_number} (M{$waived}) on {$loan->loan_number}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', "Installment #{$inst->installment_number} waived.");
    }

    public function addLateFee(Request $request, Loan $loan)
    {
        $request->validate(['installment_id' => 'required|exists:loan_installments,id', 'fee_amount' => 'required|numeric|min:0.01', 'reason' => 'nullable|string|max:500']);
        $inst = LoanInstallment::findOrFail($request->installment_id);
        $inst->increment('late_fee', $request->fee_amount);
        $inst->increment('total_amount', $request->fee_amount);
        $inst->increment('outstanding_amount', $request->fee_amount);
        $loan->increment('outstanding_balance', $request->fee_amount);
        AuditLog::record('loan.add_penalty_fee', "Added penalty fee M{$request->fee_amount} to installment #{$inst->installment_number} on {$loan->loan_number}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', "Penalty fee of M{$request->fee_amount} added.");
    }

    public function adjustSchedule(Request $request, Loan $loan)
    {
        $request->validate(['interest_rate' => 'nullable|numeric|min:0', 'reason' => 'required|string|max:500']);
        $this->svc->adjustSchedule($loan, $request->all(), auth('admin')->user());
        AuditLog::record('loan.adjust_schedule', "Schedule adjusted on {$loan->loan_number}: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Repayment schedule updated.');
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
        $loan->update(['status' => 'written_off', 'closed_at' => now(), 'closed_reason' => $request->reason, 'closed_by' => auth('admin')->id()]);
        AuditLog::record('loan.write_off', "Loan {$loan->loan_number} written off: {$request->reason}", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan written off.');
    }

    public function markDefaulted(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $loan->update(['status' => 'defaulted']);
        AuditLog::record('loan.mark_defaulted', "Loan {$loan->loan_number} marked as defaulted", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan marked as defaulted.');
    }

    public function restructure(Request $request, Loan $loan)
    {
        $request->validate(['new_term' => 'required|integer|min:1|max:120', 'new_interest_rate' => 'required|numeric|min:0', 'reason' => 'required|string|max:500']);
        $loan->update(['term_months' => $request->new_term, 'interest_rate' => $request->new_interest_rate]);
        $this->svc->adjustSchedule($loan, ['interest_rate' => $request->new_interest_rate], auth('admin')->user());
        AuditLog::record('loan.restructure', "Loan {$loan->loan_number} restructured: term={$request->new_term}mo, rate={$request->new_interest_rate}%", $loan);
        return redirect()->route('admin.loans.show', $loan)->with('success', 'Loan restructured.');
    }

    public function statement(Loan $loan)
    {
        $loan->load(['user','loanProduct','installments','payments']);
        return view('admin.loans.statement', compact('loan'));
    }

    public function agreement(Loan $loan)
    {
        $loan->load(['user','loanProduct','installments','application.employment','application.bankDetails']);
        return view('admin.loans.agreement-pdf', compact('loan'));
    }

    public function receipt(Loan $loan, Payment $payment)
    {
        $loan->load(['user','loanProduct']);
        return view('admin.loans.receipt', compact('loan','payment'));
    }

    public function settlementQuotation(Loan $loan)
    {
        $loan->load(['user','loanProduct','installments']);
        $outstanding = (float) $loan->outstanding_balance;
        $validUntil  = now()->addDays(7)->format('d M Y');
        return view('admin.loans.settlement-quotation', compact('loan','outstanding','validUntil'));
    }

    public function settlementLetter(Loan $loan)
    {
        if (!in_array($loan->status, ['paid_off','closed'])) {
            return back()->with('error', 'Settlement letter can only be issued for fully settled loans.');
        }
        $loan->load(['user','loanProduct','payments']);
        $totalPaid = $loan->payments->where('status','verified')->sum('amount');
        return view('admin.loans.settlement-letter', compact('loan','totalPaid'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status','product','date_from','date_to']);
        $loans   = $this->svc->getPaginated($filters, 9999);
        $csv     = "Loan#,Borrower,Product,Principal,Outstanding,Monthly,Status,Disbursed\n";
        foreach ($loans as $l) {
            $csv .= implode(',', [$l->loan_number, '"'.($l->user->name ?? '—').'"', '"'.($l->loanProduct->name ?? '—').'"', $l->principal_amount, $l->outstanding_balance, $l->monthly_installment, $l->status, $l->disbursement_date?->format('Y-m-d')]) . "\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="loans-'.now()->format('Y-m-d').'.csv"']);
    }

    public function overdue(Request $request) { return $this->index($request->merge(['status' => 'overdue'])); }

    public function lookup(Request $request)
    {
        $ln   = $request->input('loan_number', '');
        $loan = Loan::where('loan_number', 'like', "%{$ln}%")->whereIn('status', ['active','overdue'])->with(['user','installments'])->first();
        if (!$loan) return response()->json(['error' => 'Not found'], 404);
        $nextInst = $loan->installments->whereIn('status', ['pending','overdue','partial'])->sortBy('due_date')->first();
        return response()->json(['loan_number' => $loan->loan_number, 'borrower_name' => $loan->user->name ?? '—', 'next_due' => $nextInst?->outstanding_amount ?? $loan->monthly_installment, 'due_date' => $nextInst?->due_date?->format('d M Y'), 'status' => $loan->status]);
    }

    public function bulkRepayment(Request $request)
    {
        if ($request->isMethod('GET')) return view('admin.loans.bulk-repayment');
        $request->validate(['method' => 'required|in:cash,bank_transfer,mobile_money,card,cheque,payroll', 'rows' => 'required|array|min:1|max:30', 'rows.*.loan_number' => 'required|string', 'rows.*.amount' => 'required|numeric|min:0.01']);
        $rows    = array_filter($request->rows, fn($r) => !empty($r['loan_number']) && (float)($r['amount'] ?? 0) > 0);
        $results = $this->svc->recordBulkPayments(array_values($rows), $request->method, auth('admin')->user());
        AuditLog::record('loan.bulk_repayment', "Bulk repayment: {$results['success']} recorded, {$results['failed']} failed", null, [], ['method' => $request->method]);
        return redirect()->route('admin.loans.bulk-repayment')->with('bulk_results', $results)->with('success', "{$results['success']} payment(s) recorded.".($results['failed'] ? " {$results['failed']} failed." : ''));
    }

    public function importLoans(Request $request)
    {
        if ($request->isMethod('GET')) return view('admin.loans.import');
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:4096']);
        $path = $request->file('file')->path();
        $rows = array_map('str_getcsv', file($path));
        if (count($rows) < 2) return back()->with('error', 'CSV is empty.');
        $header  = array_map('trim', array_shift($rows));
        $data    = array_filter(array_map(fn($r) => count($r) === count($header) ? array_combine($header, array_map('trim', $r)) : null, $rows));
        $results = $this->svc->importLoansFromCsv(array_values($data), auth('admin')->user());
        return redirect()->route('admin.loans.import')->with('import_results', $results)->with('success', "{$results['success']} loan(s) imported.".($results['failed'] ? " {$results['failed']} failed." : ''));
    }

    public function collectionSheet(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $officerId = $request->input('officer_id');
        $data      = $this->svc->getCollectionSheet($date, $officerId);
        $officers  = User::where('role', 'loan_officer')->orderBy('name')->get();
        return view('admin.loans.collection-sheet', compact('data', 'officers', 'date', 'officerId'));
    }

    public function repaymentChart(Request $request)
    {
        $data = $this->svc->getRepaymentChartData();
        return view('admin.loans.repayment-chart', compact('data'));
    }
}
