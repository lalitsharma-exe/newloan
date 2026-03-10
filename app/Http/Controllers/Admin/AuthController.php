<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
    public function showLogin() { return view("admin.auth.login"); }
    public function login(Request $request) {
        $request->validate(["email"=>"required|email","password"=>"required"]);
        $credentials = $request->only("email","password");
        if (Auth::guard("admin")->attempt($credentials, $request->boolean("remember"))) {
            $user = Auth::guard("admin")->user();
            if (!$user->is_active) {
                Auth::guard("admin")->logout();
                throw ValidationException::withMessages(["email"=>"Account disabled."]);
            }
            $request->session()->regenerate();
            $user->update(["last_login_at"=>now()]);
            return redirect()->intended(route("admin.dashboard"))->with("success","Welcome, ".$user->name."!");
        }
        throw ValidationException::withMessages(["email"=>"Invalid credentials."]);
    }
    public function logout(Request $request) {
        Auth::guard("admin")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("admin.login")->with("success","Logged out.");
    }
}
