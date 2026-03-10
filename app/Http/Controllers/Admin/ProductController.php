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
        $request->validate(["name"=>"required|string|max:100|unique:loan_products","interest_rate"=>"required|numeric|min:0","max_amount"=>"required|numeric|min:1","min_amount"=>"required|numeric|min:1","min_term_months"=>"required|integer|min:1","max_term_months"=>"required|integer|min:1","processing_fee_type"=>"required|in:fixed,percentage"]);
        $p = $this->svc->create($request->all());
        return redirect()->route("admin.products.index")->with("success","Product created: ".$p->name);
    }
    public function edit(LoanProduct $product) { return view("admin.products.edit",compact("product")); }
    public function update(Request $request, LoanProduct $product) {
        $request->validate(["name"=>"required|string|max:100|unique:loan_products,name,".$product->id,"interest_rate"=>"required|numeric|min:0","max_amount"=>"required|numeric|min:1","min_amount"=>"required|numeric|min:1","min_term_months"=>"required|integer|min:1","max_term_months"=>"required|integer|min:1","processing_fee_type"=>"required|in:fixed,percentage"]);
        $this->svc->update($product,$request->all());
        return redirect()->route("admin.products.index")->with("success","Product updated.");
    }
    public function destroy(LoanProduct $product) {
        if ($product->loans()->count()) return back()->with("error","Cannot delete product with existing loans.");
        $this->svc->delete($product);
        return redirect()->route("admin.products.index")->with("success","Product deleted.");
    }
}
