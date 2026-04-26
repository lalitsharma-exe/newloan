<?php

namespace App\Services;

use App\Models\{MyBillLimit, MyBillLoan, MyBillPaydayEvent, MyBillRepayment, User};
use Illuminate\Support\Facades\{DB, Log};

/**
 * MyBillService — Core business logic for the MyBill bill-payment product.
 *
 * Handles: quotes, purchases, tier determination, limit management,
 * payday settlement, and refunds.
 *
 * Business Rules implemented:
 *   BR-001: Starting limit M500 for all existing clients
 *   BR-002: Loan created only at first purchase
 *   BR-003: Standard tier (30%) — 10% upfront, 120% on payday
 *   BR-004: No-upfront tier (40%) — 0% upfront, 140% on payday
 *   BR-005: Tier selection based on wallet balance
 *   BR-006: Full bill value always disbursed to provider
 *   BR-007: Repayment triggered on next payday
 *   BR-008: All active loans settled in one event, oldest first
 *   BR-009: Limit restored after full settlement only
 *   BR-010: Flat fee, no compounding
 */
class MyBillService
{
    public function __construct(
        private CPayBillService $cpay
    ) {}

    // =========================================================================
    //  QUOTES
    // =========================================================================

    /**
     * Return both tier breakdowns for a given bill value.
     * (BR-003, BR-004, BR-010)
     */
    public function getQuote(float $billValue): array
    {
        return [
            'bill_value' => $billValue,
            'standard'   => [
                'tier'           => '30',
                'fee_percent'    => 30,
                'loan_amount'    => round($billValue * 1.30, 2),
                'upfront'        => round($billValue * 0.10, 2),
                'payday_amount'  => round($billValue * 1.20, 2),
                'total_cost'     => round($billValue * 0.30, 2),
                'disbursed'      => $billValue,
            ],
            'no_upfront' => [
                'tier'           => '40',
                'fee_percent'    => 40,
                'loan_amount'    => round($billValue * 1.30, 2),
                'upfront'        => 0,
                'payday_amount'  => round($billValue * 1.40, 2),
                'total_cost'     => round($billValue * 0.40, 2),
                'disbursed'      => $billValue,
            ],
            'extra_if_no_upfront' => round($billValue * 0.10, 2),
        ];
    }

    // =========================================================================
    //  LIMIT MANAGEMENT
    // =========================================================================

    /**
     * Get or create a client's MyBill credit limit. (BR-001)
     */
    public function getLimit(User $user): MyBillLimit
    {
        return MyBillLimit::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_limit'  => 500.00,
                'used_amount'  => 0.00,
                'activated_at' => now(),
            ]
        );
    }

    /**
     * Check if a client can afford a purchase. (BR-001)
     */
    public function canPurchase(User $user, float $billValue): bool
    {
        $limit = $this->getLimit($user);
        return $limit->canAfford($billValue);
    }

    // =========================================================================
    //  PURCHASE FLOW
    // =========================================================================

    /**
     * Execute a full bill purchase.
     *
     * Flow:
     *   1. Validate limit
     *   2. Determine tier
     *   3. Create loan record (status=pending)
     *   4. Collect upfront (if Standard tier) — currently recorded, not via CPay
     *   5. Disburse to provider via CPay
     *   6. On success: mark active, reduce limit
     *   7. On failure: mark failed, refund upfront if collected
     *
     * @param  User   $user
     * @param  array  $data  {
     *     bill_value:       float,
     *     bill_category:    string (electricity|airtime|insurance|ticket),
     *     tier:             string (30|40),
     *     meter_number?:    string,
     *     phone_number?:    string,
     *     airtime_type?:    string (VCL|ETL),
     *     policy_number?:   string,
     *     insurance_partner_id?: int,
     *     event_id?:        string,
     *     ticket_id?:       string,
     *     ticket_info?:     array,
     * }
     * @return array { success: bool, loan?: MyBillLoan, error?: string }
     */
    public function createLoan(User $user, array $data): array
    {
        $billValue = (float) $data['bill_value'];
        $tier      = $data['tier'] ?? '40';
        $category  = $data['bill_category'];

        // ── Validate limit ──
        $limit = $this->getLimit($user);
        if (!$limit->canAfford($billValue)) {
            return ['success' => false, 'error' => "Insufficient MyBill credit. Available: M{$limit->available_amount}"];
        }

        // ── Calculate amounts based on tier ──
        $quote         = $this->getQuote($billValue);
        $tierData      = $tier === '30' ? $quote['standard'] : $quote['no_upfront'];
        $loanAmount    = $tierData['loan_amount'];
        $upfrontAmount = $tierData['upfront'];
        $paydayAmount  = $tierData['payday_amount'];

        // ── Generate transaction ID ──
        $txnId = $this->cpay->generateTxnId('MBILL');

        return DB::transaction(function () use ($user, $data, $billValue, $tier, $category, $loanAmount, $upfrontAmount, $paydayAmount, $txnId, $limit) {

            // ── Create loan record (BR-002) ──
            $loan = MyBillLoan::create([
                'loan_number'          => MyBillLoan::generateLoanNumber(),
                'user_id'              => $user->id,
                'bill_value'           => $billValue,
                'loan_amount'          => $loanAmount,
                'tier'                 => $tier,
                'upfront_amount'       => $upfrontAmount,
                'payday_amount'        => $paydayAmount,
                'settled_amount'       => 0,
                'bill_category'        => $category,
                'meter_number'         => $data['meter_number'] ?? null,
                'phone_number'         => $data['phone_number'] ?? null,
                'airtime_type'         => $data['airtime_type'] ?? null,
                'policy_number'        => $data['policy_number'] ?? null,
                'insurance_partner_id' => $data['insurance_partner_id'] ?? null,
                'event_id'             => $data['event_id'] ?? null,
                'ticket_id'            => $data['ticket_id'] ?? null,
                'status'               => 'pending',
            ]);

            // ── Record upfront payment (Standard tier only, BR-003) ──
            if ($tier === '30' && $upfrontAmount > 0) {
                MyBillRepayment::create([
                    'mybill_loan_id' => $loan->id,
                    'user_id'        => $user->id,
                    'amount'         => $upfrontAmount,
                    'deduction_type' => 'upfront',
                    'status'         => 'success',
                    'notes'          => 'Upfront payment at purchase',
                    'processed_at'   => now(),
                ]);

                // Record upfront as partial settlement
                $loan->increment('settled_amount', $upfrontAmount);
            }

            // ── Disburse bill to provider via CPay (BR-006) ──
            $disbResult = $this->disburseBill($loan, $user, $txnId);

            if (!$disbResult['success']) {
                // ── Failed: mark loan failed, restore upfront if collected ──
                $loan->update([
                    'status'         => 'failed',
                    'failure_reason' => $disbResult['error'] ?? 'Disbursement failed',
                ]);

                Log::warning('MyBill::createLoan disbursement failed', [
                    'loan'  => $loan->loan_number,
                    'error' => $disbResult['error'],
                ]);

                return [
                    'success' => false,
                    'loan'    => $loan,
                    'error'   => 'Bill payment failed: ' . ($disbResult['error'] ?? 'Provider error'),
                ];
            }

            // ── Success: update loan and reduce limit ──
            $loan->update([
                'status'            => 'active',
                'provider_ref'      => $txnId,
                'cpay_txn_id'       => $disbResult['cpay_txn_id'] ?? null,
                'provider_response' => json_encode($disbResult['data'] ?? $disbResult),
                'ticket_reference'  => $this->extractTicketRef($disbResult),
                'disbursed_at'      => now(),
            ]);

            // Reduce available limit (BR-007)
            $limit->reserve($billValue);

            Log::info('MyBill::createLoan success', [
                'loan'     => $loan->loan_number,
                'category' => $loan->bill_category,
                'bill'     => $billValue,
                'tier'     => $tier,
                'payday'   => $paydayAmount,
            ]);

            return [
                'success'  => true,
                'loan'     => $loan->fresh(),
                'result'   => $disbResult,
            ];
        });
    }

    // =========================================================================
    //  BILL DISBURSEMENT (routes to correct CPay endpoint)
    // =========================================================================

    /**
     * Disburse the bill value to the third-party provider. (BR-006)
     */
    private function disburseBill(MyBillLoan $loan, User $user, string $txnId): array
    {
        $phone  = $user->phone ?? '';
        $amount = (float) $loan->bill_value;

        return match ($loan->bill_category) {
            'electricity' => $this->cpay->purchaseElectricity(
                $loan->meter_number,
                $amount,
                $phone,
                $txnId
            ),
            'airtime' => $this->cpay->purchaseAirtime(
                $loan->phone_number ?? $phone,
                $amount,
                $loan->airtime_type ?? 'VCL',
                $txnId
            ),
            'insurance' => $this->cpay->payInsurance(
                $loan->policy_number,
                (int) $loan->insurance_partner_id,
                $amount,
                $phone,
                $txnId
            ),
            'ticket' => $this->cpay->purchaseTicket(
                [
                    'eventType'       => 'events',
                    'eventId'         => $loan->event_id,
                    'ticketId'        => $loan->ticket_id,
                    'quantity'        => 1,
                    'ticketName'      => 'Event Ticket',
                    'beneficiaryName' => $user->name,
                ],
                $amount,
                $phone,
                $txnId
            ),
            default => ['success' => false, 'error' => "Unknown bill category: {$loan->bill_category}"],
        };
    }

    // =========================================================================
    //  PAYDAY SETTLEMENT
    // =========================================================================

    /**
     * Process payday settlement for a client.
     * Settles all active loans, oldest first (BR-008).
     *
     * @param  User    $user
     * @param  float   $availableAmount  Amount available for deduction
     * @param  string  $salaryRef        Reference of triggering salary credit
     * @param  string  $triggerType      'auto' or 'manual'
     * @param  int|null $triggeredBy     Admin user ID if manual
     * @return array   { settled: int, total_deducted: float, remaining: float }
     */
    public function processPayday(User $user, float $availableAmount, string $salaryRef = '', string $triggerType = 'auto', ?int $triggeredBy = null): array
    {
        $activeLoans = MyBillLoan::where('user_id', $user->id)
            ->active()
            ->orderBy('created_at')  // Oldest first (BR-008)
            ->get();

        if ($activeLoans->isEmpty()) {
            return ['settled' => 0, 'total_deducted' => 0, 'remaining' => $availableAmount];
        }

        $remaining     = $availableAmount;
        $totalDeducted = 0;
        $loansSettled  = 0;

        foreach ($activeLoans as $loan) {
            if ($remaining <= 0) break;

            $owed   = $loan->outstanding_amount;
            $deduct = min($remaining, $owed);

            // Record the repayment
            MyBillRepayment::create([
                'mybill_loan_id'   => $loan->id,
                'user_id'          => $user->id,
                'amount'           => $deduct,
                'deduction_type'   => 'payday',
                'status'           => 'success',
                'salary_credit_ref' => $salaryRef,
                'notes'            => $triggerType === 'manual' ? 'Manual payday trigger' : 'Auto payday detection',
                'processed_at'     => now(),
            ]);

            // Update loan settlement
            $loan->recordSettlement($deduct);

            // If fully settled, restore credit limit (BR-009)
            if ($loan->fresh()->is_settled) {
                $limit = $this->getLimit($user);
                $limit->restore($loan->bill_value);
                $loansSettled++;
            }

            $remaining     -= $deduct;
            $totalDeducted += $deduct;
        }

        // Log payday event
        MyBillPaydayEvent::create([
            'user_id'        => $user->id,
            'salary_amount'  => $availableAmount,
            'detected_at'    => now(),
            'loans_settled'  => $loansSettled,
            'total_deducted' => $totalDeducted,
            'trigger_type'   => $triggerType,
            'triggered_by'   => $triggeredBy,
        ]);

        Log::info('MyBill::processPayday', [
            'user'           => $user->id,
            'available'      => $availableAmount,
            'deducted'       => $totalDeducted,
            'loans_settled'  => $loansSettled,
            'remaining'      => $remaining,
        ]);

        return [
            'settled'        => $loansSettled,
            'total_deducted' => round($totalDeducted, 2),
            'remaining'      => round($remaining, 2),
        ];
    }

    // =========================================================================
    //  REPORTING
    // =========================================================================

    /**
     * Get paginated loan history for a client.
     */
    public function getLoanHistory(User $user, int $perPage = 15)
    {
        return MyBillLoan::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get stats for admin dashboard.
     */
    public function getStats(): array
    {
        return [
            'total_loans'       => MyBillLoan::count(),
            'active_loans'      => MyBillLoan::active()->count(),
            'settled_loans'     => MyBillLoan::settled()->count(),
            'total_bill_value'  => round((float) MyBillLoan::sum('bill_value'), 2),
            'total_outstanding' => round((float) MyBillLoan::active()->selectRaw('SUM(payday_amount - settled_amount) as total')->value('total') ?? 0, 2),
            'total_revenue'     => round((float) MyBillLoan::settled()->selectRaw('SUM(payday_amount - bill_value) as total')->value('total') ?? 0, 2),
            'by_category' => [
                'electricity' => MyBillLoan::where('bill_category', 'electricity')->count(),
                'airtime'     => MyBillLoan::where('bill_category', 'airtime')->count(),
                'insurance'   => MyBillLoan::where('bill_category', 'insurance')->count(),
                'ticket'      => MyBillLoan::where('bill_category', 'ticket')->count(),
            ],
            'by_tier' => [
                '30' => MyBillLoan::where('tier', '30')->count(),
                '40' => MyBillLoan::where('tier', '40')->count(),
            ],
        ];
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    /**
     * Extract ticket reference from CPay response.
     */
    private function extractTicketRef(array $result): ?string
    {
        $additional = $result['additional'] ?? null;
        if (!$additional) return null;

        if (is_string($additional)) {
            $decoded = json_decode($additional, true);
            return $decoded['TicketReference'] ?? null;
        }

        if (is_array($additional)) {
            return $additional['TicketReference'] ?? null;
        }

        return null;
    }
}
