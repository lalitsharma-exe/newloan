<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/officer.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/borrower.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/agent.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'portal/webhooks/*',
            'webhooks/*',
            'admin/logout',
            'borrower/logout',
            'officer/logout',
            'agent/logout',
            'portal/apply/*/fee-success',
            'portal/payments/callback/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SessionTimeout::class,
            \App\Http\Middleware\CaptureReferral::class,
        ]);

        $middleware->alias([
            'admin.active'      => \App\Http\Middleware\AdminActive::class,
            'admin.permission'  => \App\Http\Middleware\CheckAdminPermission::class,
            'officer.active'    => \App\Http\Middleware\OfficerActive::class,
            'borrower.active'   => \App\Http\Middleware\BorrowerActive::class,
            'borrower.verified' => \App\Http\Middleware\BorrowerVerified::class,
            'agent.active'      => \App\Http\Middleware\AgentActive::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $path = $request->path();
            if (str_starts_with($path, 'admin')) {
                return route('admin.login');
            }
            if (str_starts_with($path, 'officer')) {
                return route('officer.login');
            }
            if (str_starts_with($path, 'agent')) {
                return route('agent.login');
            }
            return route('borrower.login');
        });

        $middleware->redirectUsersTo(function () {
            if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
                return route('admin.dashboard');
            }
            if (\Illuminate\Support\Facades\Auth::guard('officer')->check()) {
                return route('officer.dashboard');
            }
            if (\Illuminate\Support\Facades\Auth::guard('agent')->check()) {
                return route('agent.dashboard');
            }
            return route('borrower.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            $path = $request->path();
            if (str_starts_with($path, 'admin')) {
                return redirect()->guest(route('admin.login'));
            }
            if (str_starts_with($path, 'officer')) {
                return redirect()->guest(route('officer.login'));
            }
            if (str_starts_with($path, 'agent')) {
                return redirect()->guest(route('agent.login'));
            }
            return redirect()->guest(route('borrower.login'));
        });

        // Graceful handling of 419 — Page Expired (Session token mismatch)
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            $path = $request->path();
            if (str_contains($path, 'admin/logout')) return redirect()->route('admin.login');
            if (str_contains($path, 'borrower/logout')) return redirect()->route('borrower.login');
            if (str_contains($path, 'officer/logout')) return redirect()->route('officer.login');
            if (str_contains($path, 'agent/logout')) return redirect()->route('agent.login');

            if (str_starts_with($path, 'admin')) return redirect()->route('admin.login')->with('error', 'Your session has expired. Please log in again.');
            if (str_starts_with($path, 'officer')) return redirect()->route('officer.login')->with('error', 'Your session has expired. Please log in again.');
            if (str_starts_with($path, 'agent')) return redirect()->route('agent.login')->with('error', 'Your session has expired. Please log in again.');
            if (str_starts_with($path, 'borrower')) return redirect()->route('borrower.login')->with('error', 'Your session has expired. Please log in again.');

            return redirect()->route('home')->with('error', 'Your session has expired for security reasons. Please try again.');
        });
    })

    ->create();
