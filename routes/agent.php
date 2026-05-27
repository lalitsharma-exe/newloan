<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\AuthController;
use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\ApplicationController;
use App\Http\Controllers\Agent\CommissionController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\RegistrationController;

/*
|--------------------------------------------------------------------------
| AGENT ROUTES  —  prefix: /agent   name: agent.*
|--------------------------------------------------------------------------
|
| Community Agents log in via /agent/login
| They can:
|   - View their dashboard with stats & commission summary
|   - Capture & submit client loan applications
|   - Track application statuses
|   - View commission earnings
|   - Manage their profile
|
| Public routes:
|   - /agent/register — agent self-registration form (no login required)
|
*/

Route::prefix('agent')->name('agent.')->group(function () {
    Route::get('/', function() { return redirect()->route('agent.dashboard'); });

    /*
    |------------------------------------------------------------------
    | PUBLIC — Agent Self-Registration (no login required)
    |------------------------------------------------------------------
    */
    Route::get('/register', [RegistrationController::class, 'showForm'])->name('register');
    Route::post('/register', [RegistrationController::class, 'submit'])->name('register.submit');

    /*
    |------------------------------------------------------------------
    | PUBLIC (guest) — Login / Forgot / Reset
    |------------------------------------------------------------------
    */
    Route::middleware('guest:agent')->group(function () {
        Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');

        Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');
    });

    /*
    |------------------------------------------------------------------
    | AUTHENTICATED AGENT
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:agent', 'agent.active'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        | ── DASHBOARD ──────────────────────────────────────────────
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /*
        | ── CLIENT LOAN APPLICATIONS ───────────────────────────────
        | Agent captures loan applications on behalf of clients.
        */
        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/',              [ApplicationController::class, 'index'])->name('index');
            Route::get('/create',        [ApplicationController::class, 'create'])->name('create');
            Route::post('/',             [ApplicationController::class, 'store'])->name('store');
            Route::get('/{application}', [ApplicationController::class, 'show'])->name('show');

            // AJAX — real-time loan schedule calculation
            Route::post('/calculate-schedule', [ApplicationController::class, 'calculateSchedule'])->name('calculate-schedule');
        });

        /*
        | ── COMMISSION TRACKER ─────────────────────────────────────
        */
        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');

        /*
        | ── PROFILE ────────────────────────────────────────────────
        */
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',             [ProfileController::class, 'index'])->name('index');
            Route::put('/',             [ProfileController::class, 'update'])->name('update');
            Route::put('/password',     [ProfileController::class, 'updatePassword'])->name('password');
            Route::post('/photo',       [ProfileController::class, 'updatePhoto'])->name('photo');
        });

    }); // end auth:agent

}); // end prefix agent
