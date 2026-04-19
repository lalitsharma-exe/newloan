<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timeout = SystemSetting::get('session_timeout', 30); // Default to 30 minutes if not set
        
        // Laravel's config(['session.lifetime' => ...]) must be set BEFORE the session is started.
        // But since this is middleware, the session might already be started if it's in the 'web' group.
        // However, setting it here helps for subsequent logic if needed.
        // Better: Use this to track custom last activity
        
        if ($request->hasSession()) {
            config(['session.lifetime' => (int)$timeout]);
        }

        return $next($request);
    }
}
