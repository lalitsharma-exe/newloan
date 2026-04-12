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
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'portal/webhooks/*',
            'webhooks/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SessionTimeout::class,
        ]);

        $middleware->alias([
            'admin.active'      => \App\Http\Middleware\AdminActive::class,
            'officer.active'    => \App\Http\Middleware\OfficerActive::class,
            'borrower.active'   => \App\Http\Middleware\BorrowerActive::class,
            'borrower.verified' => \App\Http\Middleware\BorrowerVerified::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $path = $request->path();
            if (str_starts_with($path, 'admin')) {
                return route('admin.login');
            }
            if (str_starts_with($path, 'officer')) {
                return route('officer.login');
            }
            return route('borrower.login');
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
            return redirect()->guest(route('borrower.login'));
        });

        // Graceful handling of 419 — Page Expired (Session token mismatch)
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            $path = $request->path();
            $targetRoute = 'borrower.login';
            if (str_starts_with($path, 'admin'))   $targetRoute = 'admin.login';
            if (str_starts_with($path, 'officer')) $targetRoute = 'officer.login';

            return redirect()->route($targetRoute)->with('error', 'Your security token or session has expired. Please sign in again.');
        });
    })

    ->create();
