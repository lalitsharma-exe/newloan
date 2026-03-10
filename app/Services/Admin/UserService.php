<?php
namespace App\Services\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserService {
    public function getPaginated(array $f) {
        $q=User::query();
        if(!empty($f["role"]))   $q->where("role",$f["role"]);
        if(isset($f["status"]))  $q->where("is_active",$f["status"]==="active");
        if(!empty($f["search"])){ $s=$f["search"]; $q->where(fn($x)=>$x->where("name","like","%$s%")->orWhere("email","like","%$s%")->orWhere("phone","like","%$s%")); }
        return $q->latest()->paginate(20);
    }
    public function getStats():array { return ["total"=>User::count(),"admins"=>User::admins()->count(),"officers"=>User::loanOfficers()->count(),"borrowers"=>User::borrowers()->count(),"active"=>User::active()->count()]; }
    public function create(array $d):User { return User::create(["name"=>$d["name"],"email"=>$d["email"],"phone"=>$d["phone"],"role"=>$d["role"],"password"=>Hash::make($d["password"]),"is_active"=>$d["is_active"]??true]); }
    public function update(User $u, array $d):User { $up=["name"=>$d["name"],"email"=>$d["email"],"phone"=>$d["phone"],"role"=>$d["role"]]; if(!empty($d["password"])) $up["password"]=Hash::make($d["password"]); $u->update($up); return $u; }
    public function toggleStatus(User $u, User $admin):void { $u->update(["is_active"=>!$u->is_active]); }
    public function delete(User $u):void { $u->delete(); }
}
