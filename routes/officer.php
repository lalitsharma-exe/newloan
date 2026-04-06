<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanOfficer\AuthController;
use App\Http\Controllers\LoanOfficer\DashboardController;
use App\Http\Controllers\LoanOfficer\ApplicationController;
use App\Http\Controllers\LoanOfficer\ClientController;
use App\Http\Controllers\LoanOfficer\DocumentController;
use App\Http\Controllers\LoanOfficer\NoteController;
use App\Http\Controllers\LoanOfficer\WalkInClientController;
use App\Http\Controllers\LoanOfficer\ProfileController;
use App\Http\Controllers\LoanOfficer\NotificationController;

/*
|--------------------------------------------------------------------------
| LOAN OFFICER ROUTES  —  prefix: /officer   name: officer.*
|--------------------------------------------------------------------------
|
| Loan Officers log in via /officer/login
| They can:
|   - View & review assigned applications
|   - Verify documents
|   - Add notes (internal)
|   - Create walk-in client profiles
|   - View client lists and loan history
|   - NOT approve/decline (that's admin only)
|
*/

Route::prefix('officer')->name('officer.')->group(function () {
    Route::get('/', function() { return redirect()->route('officer.dashboard'); });

    /*
    |------------------------------------------------------------------
    | PUBLIC (guest) — Login / Forgot / Reset
    |------------------------------------------------------------------
    */
    Route::middleware('guest:officer')->group(function () {

        Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');

        Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');
    });

    /*
    |------------------------------------------------------------------
    | AUTHENTICATED LOAN OFFICER
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:officer', 'officer.active'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        | ── DASHBOARD ──────────────────────────────────────────────
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // AJAX widgets
        Route::get('/dashboard/stats',              [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/dashboard/my-applications',    [DashboardController::class, 'myApplications'])->name('dashboard.my-applications');

        /*
        | ── APPLICATIONS ───────────────────────────────────────────
        | Officers can view and review applications assigned to them.
        | They CANNOT approve or decline — only route to admin.
        */
        Route::prefix('applications')->name('applications.')->group(function () {

            Route::get('/',                     [ApplicationController::class, 'index'])->name('index');

            // Filters: assigned-to-me, pending, all
            Route::get('/assigned',             [ApplicationController::class, 'assigned'])->name('assigned');
            Route::get('/pending',              [ApplicationController::class, 'pending'])->name('pending');
            Route::get('/all',                  [ApplicationController::class, 'all'])->name('all');

            Route::get('/{application}',        [ApplicationController::class, 'show'])->name('show');

            // Officer actions (review, not approve)
            Route::post('/{application}/start-review',          [ApplicationController::class, 'startReview'])->name('start-review');
            Route::post('/{application}/complete-review',       [ApplicationController::class, 'completeReview'])->name('complete-review');
            Route::post('/{application}/request-info',          [ApplicationController::class, 'requestInfo'])->name('request-info');
            Route::post('/{application}/route-to-admin',        [ApplicationController::class, 'routeToAdmin'])->name('route-to-admin');

            // Affordability check
            Route::get('/{application}/affordability',          [ApplicationController::class, 'affordability'])->name('affordability');
            Route::post('/{application}/affordability',         [ApplicationController::class, 'saveAffordability'])->name('affordability.save');

            // Notes (internal)
            Route::post('/{application}/notes',                 [NoteController::class, 'store'])->name('notes.store');
            Route::put('/{application}/notes/{note}',           [NoteController::class, 'update'])->name('notes.update');
            Route::delete('/{application}/notes/{note}',        [NoteController::class, 'destroy'])->name('notes.destroy');

            // Documents
            Route::get('/{application}/documents',              [DocumentController::class, 'index'])->name('documents.index');
            Route::post('/{application}/documents/{doc}/verify',[DocumentController::class, 'verify'])->name('documents.verify');
            Route::post('/{application}/documents/{doc}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
            Route::post('/{application}/documents/request',     [DocumentController::class, 'request'])->name('documents.request');
            Route::get('/{application}/documents/{doc}/download',[DocumentController::class, 'download'])->name('documents.download');
            Route::get('/{application}/documents/{doc}/view',[DocumentController::class, 'view'])->name('documents.view');
            Route::post('/{application}/documents/upload', [ApplicationController::class, 'uploadDocument'])->name('documents.upload');

            // Repayment preview
            Route::get('/{application}/schedule-preview',       [ApplicationController::class, 'schedulePreview'])->name('schedule-preview');
        });

        /*
        | ── CLIENTS ────────────────────────────────────────────────
        | Officers manage clients (borrowers) — view profiles,
        | loan history, and create walk-in registrations.
        */
        Route::prefix('clients')->name('clients.')->group(function () {

            Route::get('/',                     [ClientController::class, 'index'])->name('index');
            Route::get('/search',               [ClientController::class, 'search'])->name('search');

            Route::get('/{client}',             [ClientController::class, 'show'])->name('show');

            // View client's full profile
            Route::get('/{client}/profile',     [ClientController::class, 'profile'])->name('profile');
            Route::get('/{client}/applications',[ClientController::class, 'applications'])->name('applications');
            Route::get('/{client}/loans',       [ClientController::class, 'loans'])->name('loans');
            Route::get('/{client}/documents',   [ClientController::class, 'documents'])->name('documents');
            Route::get('/{client}/payments',    [ClientController::class, 'payments'])->name('payments');
            Route::get('/{client}/notes',       [ClientController::class, 'notes'])->name('notes');

            // Officer can add notes to a client
            Route::post('/{client}/notes',      [ClientController::class, 'addNote'])->name('notes.store');
        });

        /*
        | ── WALK-IN CLIENT REGISTRATION ────────────────────────────
        | Officer creates an account on behalf of a walk-in borrower
        | and starts the multi-step application on their behalf.
        */
        Route::prefix('walk-in')->name('walk-in.')->group(function () {

            // Registration form + save
            Route::get('/create',               [WalkInClientController::class, 'create'])->name('create');
            Route::post('/',                    [WalkInClientController::class, 'store'])->name('store');

            // Start application for walk-in client
            Route::get('/{client}/apply',       [WalkInClientController::class, 'startApplication'])->name('apply');

            // Multi-step application on behalf of borrower (same 9 steps)
            Route::get('/{application}/step/{step}',   [WalkInClientController::class, 'showStep'])->name('step.show');
            Route::post('/{application}/step/{step}',  [WalkInClientController::class, 'saveStep'])->name('step.save');
            Route::post('/{application}/submit',       [WalkInClientController::class, 'submit'])->name('submit');
        });

        /*
        | ── DOCUMENT VERIFICATION (standalone) ─────────────────────
        | Queue of all pending documents that need verification.
        */
        Route::prefix('documents')->name('documents.')->group(function () {

            Route::get('/',                     [DocumentController::class, 'verificationQueue'])->name('index');
            Route::get('/pending',              [DocumentController::class, 'pending'])->name('pending');
            Route::get('/{doc}',                [DocumentController::class, 'show'])->name('show');
            Route::post('/{doc}/verify',        [DocumentController::class, 'verify'])->name('verify');
            Route::post('/{doc}/reject',        [DocumentController::class, 'reject'])->name('reject');
            Route::get('/{doc}/view',           [DocumentController::class, 'view'])->name('view');
            Route::get('/{doc}/download',       [DocumentController::class, 'download'])->name('download');
        });

        /*
        | ── NOTIFICATIONS ──────────────────────────────────────────
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                     [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read',           [NotificationController::class, 'markRead'])->name('read');
            Route::post('/mark-all-read',       [NotificationController::class, 'markAllRead'])->name('read-all');
        });

        /*
        | ── PROFILE ────────────────────────────────────────────────
        */
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',             [ProfileController::class, 'index'])->name('index');
            Route::put('/',             [ProfileController::class, 'update'])->name('update');
            Route::put('/password',     [ProfileController::class, 'updatePassword'])->name('password');
            Route::post('/photo',       [ProfileController::class, 'updatePhoto'])->name('photo');
        });

    }); // end auth:officer

}); // end prefix officer
