<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\{MyBillLoan, User};
use App\Services\{CPayBillService, MyBillService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MyBillController extends Controller
{
    public function __construct(
        private MyBillService   $svc,
        private CPayBillService $cpay
    ) {}

    /**
     * MyBill Dashboard — credit limit, quick-buy, recent transactions.
     */
    public function index()
    {
        $user  = auth('borrower')->user();
        $limit = $this->svc->getLimit($user);
        $loans = MyBillLoan::where('user_id', $user->id)->latest()->take(10)->get();

        return view('borrower.mybill.index', compact('user', 'limit', 'loans'));
    }

    /**
     * Purchase form for a specific category.
     */
    public function showPurchase(string $category)
    {
        $user  = auth('borrower')->user();
        $limit = $this->svc->getLimit($user);

        $categories = ['electricity', 'airtime', 'insurance', 'ticket'];
        if (!in_array($category, $categories)) {
            return redirect()->route('borrower.mybill.index')->with('error', 'Invalid category.');
        }

        // Fetch category-specific data from CPay
        $providerData = [];
        try {
            $providerData = match ($category) {
                'airtime'   => $this->cpay->listAirtime(),
                'insurance' => $this->cpay->listInsuranceProviders(),
                'ticket'    => $this->cpay->listEvents(),
                default     => [],
            };
        } catch (\Throwable $e) {
            Log::warning('MyBill: Failed to load provider data', ['category' => $category, 'error' => $e->getMessage()]);
        }

        return view('borrower.mybill.purchase', compact('user', 'limit', 'category', 'providerData'));
    }

    /**
     * AJAX: Get a quote for a bill amount.
     */
    public function quote(Request $request)
    {
        $request->validate(['bill_value' => 'required|numeric|min:1|max:5000']);
        return response()->json($this->svc->getQuote((float) $request->bill_value));
    }

    /**
     * AJAX: Validate a meter number.
     */
    public function lookupMeter(Request $request)
    {
        $request->validate(['meter_number' => 'required|string|min:5']);
        $result = $this->cpay->lookupMeter($request->meter_number);
        return response()->json($result);
    }

    /**
     * AJAX: Lookup insurance member.
     */
    public function lookupInsurance(Request $request)
    {
        $request->validate([
            'policy_number' => 'required|string',
            'partner_id'    => 'required|integer',
        ]);
        $result = $this->cpay->lookupInsuranceMember($request->policy_number, (int) $request->partner_id);
        return response()->json($result);
    }

    /**
     * AJAX: Get event details.
     */
    public function eventDetails(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);
        $result = $this->cpay->getEventDetails($request->event_id);
        return response()->json($result);
    }

    /**
     * Confirmation page — show fee breakdown before purchase.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'bill_value'    => 'required|numeric|min:1|max:5000',
            'bill_category' => 'required|in:electricity,airtime,insurance,ticket',
            'tier'          => 'required|in:30,40',
        ]);

        $user  = auth('borrower')->user();
        $limit = $this->svc->getLimit($user);
        $quote = $this->svc->getQuote((float) $request->bill_value);

        if (!$limit->canAfford((float) $request->bill_value)) {
            return redirect()->route('borrower.mybill.purchase', $request->bill_category)
                ->with('error', "Insufficient credit. Available: M{$limit->available_amount}");
        }

        return view('borrower.mybill.confirm', [
            'user'     => $user,
            'limit'    => $limit,
            'quote'    => $quote,
            'tier'     => $request->tier,
            'category' => $request->bill_category,
            'data'     => $request->all(),
        ]);
    }

    /**
     * Execute the purchase.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bill_value'           => 'required|numeric|min:1|max:5000',
            'bill_category'        => 'required|in:electricity,airtime,insurance,ticket',
            'tier'                 => 'required|in:30,40',
            'meter_number'         => 'nullable|string|min:5',
            'phone_number'         => 'nullable|string|min:8',
            'airtime_type'         => 'nullable|string|in:VCL,ETL',
            'policy_number'        => 'nullable|string',
            'insurance_partner_id' => 'nullable|integer',
            'event_id'             => 'nullable|string',
            'ticket_id'            => 'nullable|string',
        ]);

        $user   = auth('borrower')->user();
        $result = $this->svc->createLoan($user, $request->all());

        if (!$result['success']) {
            return redirect()->route('borrower.mybill.purchase', $request->bill_category)
                ->with('error', $result['error'] ?? 'Purchase failed. Please try again.');
        }

        return redirect()->route('borrower.mybill.show', $result['loan'])
            ->with('success', 'Bill paid successfully! Your ' . ucfirst($request->bill_category) . ' purchase is confirmed.');
    }

    /**
     * Show single loan detail / receipt.
     */
    public function show(MyBillLoan $loan)
    {
        $user = auth('borrower')->user();
        if ($loan->user_id !== $user->id) abort(403);

        $loan->load('repayments');
        return view('borrower.mybill.show', compact('loan', 'user'));
    }

    /**
     * Full transaction history.
     */
    public function history(Request $request)
    {
        $user  = auth('borrower')->user();
        $query = MyBillLoan::where('user_id', $user->id);

        if ($request->filled('category')) {
            $query->where('bill_category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->paginate(15);
        $limit = $this->svc->getLimit($user);

        return view('borrower.mybill.history', compact('loans', 'limit', 'user'));
    }
}
