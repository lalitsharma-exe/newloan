<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use App\Services\Admin\ProductService;
use Illuminate\Http\Request;
class ProductController extends Controller {
    public function __construct(private ProductService $svc) {}
    public function index() {
        return view("admin.products.index",["products"=>LoanProduct::withCount("loans")->orderByDesc("created_at")->get()]);
    }
    public function create() { return view("admin.products.create"); }
    public function store(Request $request) {
        $request->validate([
            "name"                => "required|string|max:100|unique:loan_products",
            "interest_rate"       => "required|numeric|min:0|max:100",
            "initiation_fee_rate" => "required|numeric|min:0|max:100",
            "admin_fee_fixed"     => "required|numeric|min:0",
            "interest_method"     => "required|in:flat,reducing",
            "max_amount"          => "required|numeric|min:1",
            "min_amount"          => "required|numeric|min:1",
            "min_term_months"     => "required|integer|min:1",
            "max_term_months"     => "required|integer|min:1",
            "late_payment_fee"    => "nullable|numeric|min:0",
        ]);
        $p = $this->svc->create($request->all());
        return redirect()->route("admin.products.index")->with("success","Product created: ".$p->name);
    }

    public function update(Request $request, LoanProduct $product) {
        $request->validate([
            "name"                => "required|string|max:100|unique:loan_products,name,".$product->id,
            "interest_rate"       => "required|numeric|min:0|max:100",
            "initiation_fee_rate" => "required|numeric|min:0|max:100",
            "admin_fee_fixed"     => "required|numeric|min:0",
            "interest_method"     => "required|in:flat,reducing",
            "max_amount"          => "required|numeric|min:1",
            "min_amount"          => "required|numeric|min:1",
            "min_term_months"     => "required|integer|min:1",
            "max_term_months"     => "required|integer|min:1",
            "late_payment_fee"    => "nullable|numeric|min:0",
        ]);
        $this->svc->update($product,$request->all());
        return redirect()->route("admin.products.index")->with("success","Product updated.");
    }
    public function edit(LoanProduct $product) {
        return view("admin.products.edit", compact("product"));
    }

    public function show(LoanProduct $product) {
        $product->loadCount('loans');
        $product->load(['loans' => fn($q) => $q->latest()->limit(10)]);
        return view("admin.products.show", compact("product"));
    }

    public function toggle(LoanProduct $product) {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->fresh()->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "{$product->name} {$status}.");
    }

    public function stats(LoanProduct $product) {
        $stats = [
            'total_loans'      => $product->loans()->count(),
            'active_loans'     => $product->loans()->where('status','active')->count(),
            'total_disbursed'  => $product->loans()->sum('principal_amount'),
            'outstanding'      => $product->loans()->sum('outstanding_balance'),
            'overdue_count'    => $product->loans()->where('status','overdue')->count(),
        ];
        return response()->json($stats);
    }
}
