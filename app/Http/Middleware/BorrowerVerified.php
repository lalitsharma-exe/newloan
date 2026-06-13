<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BorrowerVerified {
    public function handle(Request $request, Closure $next): Response {
        $user = Auth::guard('borrower')->user();
        if ($user && !$user->email_verified_at && $user->email) {
            return redirect()->route('borrower.verify.pending');
        }
        if ($user && !$user->is_verified) {
            return redirect()->route('borrower.phone.verify.show');
        }
        return $next($request);
    }
}
