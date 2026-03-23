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
    })
    ->create();
