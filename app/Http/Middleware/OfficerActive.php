<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OfficerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('officer')->user();
        if (!$user || !$user->is_active) {
            Auth::guard('officer')->logout();
            return redirect()->route('officer.login')->with('error', 'Your account has been disabled. Contact admin.');
        }
        if ($user->role !== 'loan_officer') {
            Auth::guard('officer')->logout();
            return redirect()->route('officer.login')->with('error', 'Access denied.');
        }
        return $next($request);
    }
}
