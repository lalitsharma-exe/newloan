<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BorrowerActive {
    public function handle(Request $request, Closure $next): Response {
        $user = Auth::guard('borrower')->user();
        if (!$user || !$user->is_active) {
            Auth::guard('borrower')->logout();
            return redirect()->route('borrower.login')->with('error', 'Your account has been disabled. Please contact support.');
        }
        if ($user->role !== 'borrower') {
            Auth::guard('borrower')->logout();
            return redirect()->route('borrower.login')->with('error', 'Access denied.');
        }
        return $next($request);
    }
}
