<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
class UserController extends Controller {
    public function __construct(private UserService $svc) {}
    public function index(Request $request) {
        $filters = $request->only(["role","status","search"]);
        return view("admin.users.index",["users"=>$this->svc->getPaginated($filters),"stats"=>$this->svc->getStats(),"filters"=>$filters]);
    }
    public function create() { return view("admin.users.create"); }
    public function store(Request $request) {
        $request->validate(["name"=>"required|string|max:100","email"=>"required|email|unique:users","phone"=>"required|string|unique:users","role"=>"required|in:admin,loan_officer,borrower","password"=>"required|string|min:8|confirmed"]);
        $user = $this->svc->create($request->all());
        return redirect()->route("admin.users.index")->with("success","User created: ".$user->name);
    }
    public function edit(User $user) { return view("admin.users.edit",compact("user")); }
    public function update(Request $request, User $user) {
        $request->validate(["name"=>"required|string|max:100","email"=>"required|email|unique:users,email,".$user->id,"phone"=>"required|string|unique:users,phone,".$user->id,"role"=>"required|in:admin,loan_officer,borrower","password"=>"nullable|string|min:8|confirmed"]);
        $this->svc->update($user,$request->all());
        return redirect()->route("admin.users.index")->with("success","User updated.");
    }
    public function toggleStatus(User $user) {
        $this->svc->toggleStatus($user,auth("admin")->user());
        return back()->with("success","User ".($user->fresh()->is_active?"enabled":"disabled").".");
    }
    public function destroy(User $user) {
        if ($user->id===auth("admin")->id()) return back()->with("error","Cannot delete yourself.");
        $this->svc->delete($user);
        return redirect()->route("admin.users.index")->with("success","User deleted.");
    }
}
