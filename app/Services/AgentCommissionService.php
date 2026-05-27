<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\AgentProfile;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentCommissionService
{
    /**
     * Trigger agent commission calculation when a payment is successfully verified.
     */
    public function handlePayment(Loan $loan): void
    {
        // 1. Get the client loan application
        $application = $loan->application;
        if (!$application) {
            return;
        }

        // 2. Check if the application was submitted by an agent
        $agentId = $application->agent_id;
        if (!$agentId) {
            return;
        }

        // 3. Find the agent's profile
        $profile = AgentProfile::where('user_id', $agentId)->first();
        if (!$profile) {
            Log::warning("Agent application found with agent_id: {$agentId} but no AgentProfile exists.");
            return;
        }

        // 4. Verify if this is the FIRST payment that has been successfully verified for this loan
        // A repayment is qualified if the loan has exactly one 'verified' payment record
        $verifiedPaymentsCount = $loan->payments()->where('status', 'verified')->count();

        if ($verifiedPaymentsCount !== 1) {
            // Already paid commission or no verified payment found
            Log::info("Loan {$loan->loan_number}: verified payments count is {$verifiedPaymentsCount}. Commission only triggers on the 1st payment.");
            return;
        }

        // 5. Check if commission was already processed to prevent duplicate payouts (idempotency check)
        $alreadyProcessed = AuditLog::where('associated_type', 'Loan')
            ->where('associated_id', $loan->id)
            ->where('action', 'agent.commission_paid')
            ->exists();

        if ($alreadyProcessed) {
            Log::info("Loan {$loan->loan_number}: Agent commission was already processed previously. Skipping.");
            return;
        }

        // 6. Apply commission (M50 payout)
        DB::beginTransaction();
        try {
            $commissionAmount = 50.00;

            // Increment earned, decrement pending
            $profile->increment('total_earned', $commissionAmount);
            if ($profile->pending_earnings >= $commissionAmount) {
                $profile->decrement('pending_earnings', $commissionAmount);
            } else {
                $profile->update(['pending_earnings' => 0]);
            }

            // Log audit trail
            AuditLog::record(
                'agent.commission_paid',
                "M50.00 commission paid to agent {$profile->agent_id} ({$profile->user->name}) for first successful repayment on Loan {$loan->loan_number}.",
                $loan
            );

            DB::commit();

            Log::info("Agent commission of M50.00 successfully paid to agent {$profile->agent_id} for Loan {$loan->loan_number}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to pay agent commission for Loan {$loan->loan_number}: " . $e->getMessage());
        }
    }
}
