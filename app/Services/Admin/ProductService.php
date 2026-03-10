<?php
namespace App\Services\Admin;
use App\Models\LoanProduct;
use Illuminate\Support\Str;
class ProductService {
    public function create(array $d):LoanProduct { $d["slug"]=Str::slug($d["name"]); $d["is_active"]=$d["is_active"]??true; return LoanProduct::create($d); }
    public function update(LoanProduct $p, array $d):LoanProduct { $d["slug"]=Str::slug($d["name"]); $p->update($d); return $p; }
    public function delete(LoanProduct $p):void { $p->delete(); }
}
