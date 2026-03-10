<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class AdminActive {
    public function handle(Request $request, Closure $next): Response {
        $user = Auth::guard("admin")->user();
        if (!$user || !$user->is_active) {
            Auth::guard("admin")->logout();
            return redirect()->route("admin.login")->with("error","Account disabled.");
        }
        if (!in_array($user->role,["admin","loan_officer"])) {
            Auth::guard("admin")->logout();
            return redirect()->route("admin.login")->with("error","Access denied.");
        }
        return $next($request);
    }
}
