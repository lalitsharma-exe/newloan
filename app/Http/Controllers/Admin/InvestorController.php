<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Investor;
use App\Models\TreasuryAccount;
use App\Services\Admin\InvestorService;
use App\Services\Admin\ContractService;
use Illuminate\Http\Request;
use Exception;

class InvestorController extends Controller
{
    public function __construct(
        protected InvestorService $svc,
        protected ContractService $contractSvc
    ) {}

    /**
     * Display a listing of investments.
     */
    public function index(Request $request)
    {
        $query = Investment::with(['investor', 'treasuryAccount'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('investor', function($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('id_number', 'like', '%' . $request->search . '%');
            })->orWhere('contract_ref', 'like', '%' . $request->search . '%');
        }

        $investments = $query->paginate(15);

        return view('admin.financial.investments.index', compact('investments'));
    }

    /**
     * Store a newly created investment.
     */
    public function storeInvestment(Request $request)
    {
        $request->validate([
            'investor_id' => 'required|uuid|exists:investors,id',
            'principal' => 'required|numeric|min:1',
            'investment_date' => 'required|date',
            'treasury_account_id' => 'required|exists:treasury_accounts,id',
            'confirm_december' => 'nullable|boolean',
        ]);

        try {
            $investor = Investor::findOrFail($request->investor_id);
            $calc = $this->svc->calculateInvestment($request->principal, $request->investment_date, $investor->investor_type);

            if ($calc['flag'] === 'DEFER' && !$request->confirm_december) {
                return response()->json([
                    'warning' => 'DECEMBER_INVESTMENT',
                    'message' => 'December investments accrue 0 months of interest because they mature in the same calendar month. Would you like to proceed or defer to next year?',
                ], 422);
            }

            $investment = $this->svc->createInvestment(
                $request->investor_id,
                $request->principal,
                $request->investment_date,
                $request->treasury_account_id,
                auth()->id()
            );

            return redirect()->route('admin.investments.show', $investment->id)
                ->with('ok', 'Investment recorded successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('err', $e->getMessage());
        }
    }

    /**
     * Show investment details.
     */
    public function show($id)
    {
        $investment = Investment::with(['investor', 'treasuryAccount', 'accruals'])->findOrFail($id);
        return view('admin.financial.investments.show', compact('investment'));
    }

    /**
     * List all registered investors.
     */
    public function listInvestors(Request $request)
    {
        $query = Investor::withCount('investments')->latest();

        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('id_number', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $investors = $query->paginate(15);
        $accounts = TreasuryAccount::where('is_active', true)->get();

        return view('admin.financial.investors.index', compact('investors', 'accounts'));
    }

    /**
     * Store a newly created investor profile.
     */
    public function storeInvestor(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'investor_type' => 'required|in:MD,PUBLIC',
            'full_name' => 'required|string|max:255',
            'id_number' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
        ]);

        try {
            Investor::create($request->all());
            return redirect()->route('admin.investments.investors.index')
                ->with('ok', 'Investor registered successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('err', $e->getMessage());
        }
    }

    /**
     * Display the specified investor profile.
     */
    public function showInvestor($id)
    {
        $investor = Investor::with(['investments.treasuryAccount', 'user'])->findOrFail($id);
        $accounts = TreasuryAccount::where('is_active', true)->get();

        return view('admin.financial.investors.show', compact('investor', 'accounts'));
    }

    /**
     * Repay matured investment.
     */
    public function repay($id)
    {
        try {
            $investment = Investment::findOrFail($id);
            $this->svc->processMaturityRepayment($investment, auth()->id());

            return back()->with('ok', 'Maturity repayment successfully processed.');
        } catch (Exception $e) {
            return back()->with('err', $e->getMessage());
        }
    }

    /**
     * Submit an early termination request.
     */
    public function requestTermination($id, Request $request)
    {
        $request->validate([
            'request_date' => 'required|date',
        ]);

        try {
            $investment = Investment::findOrFail($id);
            $this->svc->requestTermination($investment, $request->request_date);

            return back()->with('ok', 'Early termination request recorded. 30-day notice period has commenced.');
        } catch (Exception $e) {
            return back()->with('err', $e->getMessage());
        }
    }

    /**
     * Preview early termination payout breakdown.
     */
    public function previewTermination($id)
    {
        try {
            $investment = Investment::findOrFail($id);
            $today = now()->toDateString();
            $preview = $this->svc->calculateEarlyTerminationPayout($investment, $today);

            return response()->json([
                'principal' => number_format($preview['principal_cents'] / 100, 2),
                'earned_interest' => number_format($preview['earned_interest_cents'] / 100, 2),
                'forfeited_interest' => number_format($preview['forfeited_interest_cents'] / 100, 2),
                'termination_fee' => number_format($preview['termination_fee_cents'] / 100, 2),
                'net_payout' => number_format($preview['net_payout_cents'] / 100, 2),
                'posted_months' => $preview['posted_months_count'],
                'forfeited_months' => $preview['forfeited_months_count'],
            ]);
        } catch (Exception $e) {
            return response()->json(['err' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve early termination.
     */
    public function approveTermination($id)
    {
        try {
            $investment = Investment::findOrFail($id);
            $this->svc->approveTermination($investment, auth()->id());

            return back()->with('ok', 'Early termination request approved and funds returned.');
        } catch (Exception $e) {
            return back()->with('err', $e->getMessage());
        }
    }

    /**
     * Decline early termination.
     */
    public function declineTermination($id)
    {
        try {
            $investment = Investment::findOrFail($id);
            $this->svc->declineTermination($investment);

            return back()->with('ok', 'Early termination request declined. Investment is active.');
        } catch (Exception $e) {
            return back()->with('err', $e->getMessage());
        }
    }

    /**
     * Download the investment contract.
     */
    public function downloadContract($id)
    {
        $investment = Investment::with(['investor', 'accruals'])->findOrFail($id);
        
        $filename = "INVESTMENT_CONTRACT_" . $investment->contract_ref . ".doc";
        
        $content = $this->contractSvc->generateContractHtml($investment);
        
        return response($content)
            ->header('Content-Type', 'application/vnd.ms-word')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
