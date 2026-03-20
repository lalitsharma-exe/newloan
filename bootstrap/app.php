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
    })
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
