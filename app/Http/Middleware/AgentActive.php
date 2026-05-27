<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgentActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('agent')->user();
        if (!$user || !$user->is_active) {
            Auth::guard('agent')->logout();
            return redirect()->route('agent.login')->with('error', 'Your account has been disabled. Contact admin.');
        }
        if ($user->role !== 'agent') {
            Auth::guard('agent')->logout();
            return redirect()->route('agent.login')->with('error', 'Access denied.');
        }
        return $next($request);
    }
}
