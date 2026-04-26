<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Usage in routes: ->middleware('admin.permission:loans.view')
     * Multiple: ->middleware('admin.permission:loans.view,loans.manage')
     * User needs at least ONE of the listed permissions.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Super admins bypass all checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has at least one of the required permissions
        foreach ($permissions as $permission) {
            if ($user->hasAdminPermission($permission)) {
                return $next($request);
            }
        }

        // No permission
        if ($request->expectsJson()) {
            return response()->json(['error' => 'You do not have permission to access this resource.'], 403);
        }

        return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access that page.');
    }
}
