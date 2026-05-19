<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $fillable = [
        "payment_reference","loan_id","installment_id","user_id","application_id",
        "amount","method","gateway_reference","reference",
        "status","notes","is_manual",
        "verified_by","verified_at",
        "principal_portion","interest_portion","initiation_fee_portion","admin_fee_portion","penalty_portion","repayment_components"
    ];

    protected $casts = [
        "verified_at" => "datetime",
        "is_manual"   => "boolean",
    ];

    protected static function booted()
    {
        static::updated(function ($payment) {
            if ($payment->isDirty('status')) {
                if ($payment->status === 'verified') {
                    self::postToTreasury($payment);
                } elseif ($payment->status === 'reversed') {
                    self::postReversalToTreasury($payment);
                }
            }
        });

        static::created(function ($payment) {
            if ($payment->status === 'verified') {
                self::postToTreasury($payment);
            }
        });
    }

    /**
     * Post payment collection to the corresponding Treasury account.
     */
    public static function postToTreasury($payment)
    {
        try {
            // Re-verify if payment is verified
            if ($payment->status !== 'verified') {
                return;
            }

            // Check if transaction is already recorded to prevent duplicates
            $exists = \App\Models\TreasuryTransaction::where('payment_id', $payment->id)
                ->where('direction', 'in')
                ->exists();
            if ($exists) {
                return;
            }

            $accountId = match ($payment->method) {
                'mobile_money', 'mpesa', 'mobile_wallet' => 1,
                'bank_transfer', 'eft', 'card'           => 2,
                default                                  => 2, // Standard Lesotho Bank
            };

            $financialService = resolve(\App\Services\Admin\FinancialService::class);
            $financialService->recordTransaction(
                $accountId,
                'collection',
                $payment->amount,
                'in',
                [
                    'reference'   => $payment->payment_reference,
                    'description' => "Repayment for Loan " . ($payment->loan->loan_number ?? '#'.$payment->loan_id),
                    'payment_id'  => $payment->id,
                    'loan_id'     => $payment->loan_id,
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to post payment transaction to treasury: " . $e->getMessage());
        }
    }

    /**
     * Post payment reversal to the corresponding Treasury account.
     */
    public static function postReversalToTreasury($payment)
    {
        try {
            // Re-verify if payment is reversed
            if ($payment->status !== 'reversed') {
                return;
            }

            // Check if transaction is already recorded to prevent duplicates
            $exists = \App\Models\TreasuryTransaction::where('payment_id', $payment->id)
                ->where('type', 'adjustment')
                ->exists();
            if ($exists) {
                return;
            }

            $accountId = match ($payment->method) {
                'mobile_money', 'mpesa', 'mobile_wallet' => 1,
                'bank_transfer', 'eft', 'card'           => 2,
                default                                  => 2, // Standard Lesotho Bank
            };

            $financialService = resolve(\App\Services\Admin\FinancialService::class);
            $financialService->recordTransaction(
                $accountId,
                'adjustment',
                $payment->amount,
                'out',
                [
                    'reference'   => $payment->payment_reference,
                    'description' => "Payment reversal: " . ($payment->notes ?? 'Reversal of Payment'),
                    'payment_id'  => $payment->id,
                    'loan_id'     => $payment->loan_id,
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to post payment reversal to treasury: " . $e->getMessage());
        }
    }

    public function application() { return $this->belongsTo(LoanApplication::class, "application_id"); }
    public function loan()       { return $this->belongsTo(Loan::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function installment(){ return $this->belongsTo(LoanInstallment::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, "verified_by"); }

    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'verified' => 'ok',
            'pending'  => 'w',
            'rejected',
            'failed'   => 'e',
            default    => 's',
        };
    }
}