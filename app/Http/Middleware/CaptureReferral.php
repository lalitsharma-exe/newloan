<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $code = $request->query('ref');
            // Store in session for 30 days (Laravel session lifetime handles this if configured, 
            // but we'll just put it in session now)
            session(['referral_code' => $code]);
            
            // Also store in cookie just in case session expires but they come back
            return $next($request)->cookie('referral_code', $code, 60 * 24 * 30);
        }

        return $next($request);
    }
}
