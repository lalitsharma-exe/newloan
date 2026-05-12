<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    /**
     * View receipt in browser
     */
    public function show(Loan $loan, Payment $payment)
    {
        $payment->load(['loan.user', 'verifiedBy', 'loan.installments']);
        
        // Calculate balance before payment
        $balanceBefore = $payment->loan->outstanding_balance + $payment->amount;
        
        // Get next due date
        $nextDue = $payment->loan->installments()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->first();

        return view('admin.loans.receipt', [
            'payment'       => $payment,
            'loan'          => $payment->loan,
            'balanceBefore' => $balanceBefore,
            'nextDue'       => $nextDue,
            'isPdf'         => false
        ]);
    }

    /**
     * Download receipt as PDF
     */
    public function download(Loan $loan, Payment $payment)
    {
        $payment->load(['loan.user', 'verifiedBy', 'loan.installments']);
        
        $balanceBefore = $payment->loan->outstanding_balance + $payment->amount;
        
        $nextDue = $payment->loan->installments()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->first();

        $pdf = Pdf::loadView('admin.loans.receipt', [
            'payment'       => $payment,
            'loan'          => $payment->loan,
            'balanceBefore' => $balanceBefore,
            'nextDue'       => $nextDue,
            'isPdf'         => true
        ]);

        return $pdf->download("Receipt-{$payment->payment_reference}.pdf");
    }

    /**
     * Public verification page
     */
    public function verify(Request $request)
    {
        $ref = $request->query('ref');
        $payment = null;
        
        if ($ref) {
            $payment = Payment::where('payment_reference', $ref)
                ->where('status', 'verified')
                ->with(['loan.user'])
                ->first();
        }

        return view('public.verify-receipt', compact('payment', 'ref'));
    }
}
