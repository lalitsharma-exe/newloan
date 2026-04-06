<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CreditBureauController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfficerAssignmentController;
use App\Http\Controllers\Admin\DocumentController;

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES  —  prefix: /admin   name: admin.*
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function() { return redirect()->route('admin.dashboard'); });


    /*
    |------------------------------------------------------------------
    | PUBLIC (guest) — Login / Forgot / Reset
    |------------------------------------------------------------------
    */
    Route::middleware('guest:admin')->group(function () {

        // Login
        Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');

        // Password reset
        Route::get('/forgot-password',         [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password',        [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}',  [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password',         [AuthController::class, 'resetPassword'])->name('password.update');
    });

    /*
    |------------------------------------------------------------------
    | AUTHENTICATED ADMIN
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:admin', 'admin.active'])->group(function () {

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        | ── DASHBOARD ──────────────────────────────────────────────
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // AJAX widget refreshes
        Route::get('/dashboard/stats',            [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/dashboard/chart-data',       [DashboardController::class, 'chartData'])->name('dashboard.chart');
        Route::get('/dashboard/notifications',    [DashboardController::class, 'notifications'])->name('dashboard.notifications');

        /*
        | ── APPLICATION MANAGEMENT ─────────────────────────────────
        */
        Route::prefix('applications')->name('applications.')->group(function () {

            Route::get('/',                 [ApplicationController::class, 'index'])->name('index');
            Route::get('/create',           [ApplicationController::class, 'create'])->name('create');
            Route::post('/',                [ApplicationController::class, 'store'])->name('store');
            Route::get('/export',           [ApplicationController::class, 'export'])->name('export');

            Route::get('/{application}',                      [ApplicationController::class, 'show'])->name('show');

            // Status transitions
            Route::post('/{application}/approve',             [ApplicationController::class, 'approve'])->name('approve');
            Route::post('/{application}/decline',             [ApplicationController::class, 'decline'])->name('decline');
            Route::post('/{application}/hold',                [ApplicationController::class, 'hold'])->name('hold');
            Route::post('/{application}/reinstate',           [ApplicationController::class, 'reinstate'])->name('reinstate');
            Route::post('/{application}/request-info',        [ApplicationController::class, 'requestInfo'])->name('request-info');
            Route::post('/{application}/mark-under-review',   [ApplicationController::class, 'markUnderReview'])->name('mark-under-review');

            // Term overrides & scoring
            Route::post('/{application}/override-terms',      [ApplicationController::class, 'overrideTerms'])->name('override-terms');
            Route::post('/{application}/set-risk-score',      [ApplicationController::class, 'setRiskScore'])->name('set-risk-score');
            Route::post('/{application}/auto-risk-score',     [ApplicationController::class, 'autoRiskScore'])->name('auto-risk-score');

            // Officer assignment
            Route::post('/{application}/assign-officer',      [OfficerAssignmentController::class, 'assign'])->name('assign-officer');
            Route::post('/{application}/unassign-officer',    [OfficerAssignmentController::class, 'unassign'])->name('unassign-officer');

            // Notes
            Route::post('/{application}/notes',               [ApplicationController::class, 'addNote'])->name('notes.store');
            Route::delete('/{application}/notes/{note}',      [ApplicationController::class, 'deleteNote'])->name('notes.destroy');

            // Documents (admin view/verify)
            Route::get('/{application}/documents',            [DocumentController::class, 'index'])->name('documents.index');
            Route::post('/{application}/documents/{doc}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
            Route::post('/{application}/documents/{doc}/reject',  [DocumentController::class, 'reject'])->name('documents.reject');
            Route::get('/{application}/documents/{doc}/download',  [DocumentController::class, 'download'])->name('documents.download');
            Route::get('/{application}/documents/{doc}/view',  [DocumentController::class, 'view'])->name('documents.view');
            Route::post('/{application}/documents/upload', [ApplicationController::class, 'uploadDocument'])->name('documents.upload');
            Route::post('/{application}/affordability', [ApplicationController::class, 'updateAffordability'])->name('update-affordability');

            // AJAX Chat
            Route::get('/{application}/messages', [ApplicationController::class, 'getMessages'])->name('messages.get');
            Route::post('/{application}/messages', [ApplicationController::class, 'sendMessage'])->name('messages.send');

            // Repayment schedule preview
            Route::get('/{application}/schedule-preview',    [ApplicationController::class, 'schedulePreview'])->name('schedule-preview');

            // Experian Template
            Route::get('/{application}/experian-template', [ApplicationController::class, 'experianTemplate'])->name('experian-template');
        });

        /*
        | ── LOAN MANAGEMENT ────────────────────────────────────────
        */
        Route::prefix('loans')->name('loans.')->group(function () {

            Route::get('/',             [LoanController::class, 'index'])->name('index');
            Route::get('/export',       [LoanController::class, 'export'])->name('export');
            Route::get('/overdue',      [LoanController::class, 'overdue'])->name('overdue');

            // ── Static feature routes (BEFORE /{loan} wildcard) ──
            Route::get('/bulk-repayment',  [LoanController::class, 'bulkRepayment'])->name('bulk-repayment');
            Route::post('/bulk-repayment', [LoanController::class, 'bulkRepayment'])->name('bulk-repayment.post');
            Route::get('/import',          [LoanController::class, 'importLoans'])->name('import');
            Route::post('/import',         [LoanController::class, 'importLoans'])->name('import.post');
            Route::get('/collection-sheet',[LoanController::class, 'collectionSheet'])->name('collection-sheet');
            Route::get('/repayment-chart', [LoanController::class, 'repaymentChart'])->name('repayment-chart');
            Route::get('/lookup',          [LoanController::class, 'lookup'])->name('lookup');

            Route::get('/{loan}',                   [LoanController::class, 'show'])->name('show');

            // Schedule management
            Route::get('/{loan}/schedule',          [LoanController::class, 'schedule'])->name('schedule');
            Route::post('/{loan}/adjust-schedule',  [LoanController::class, 'adjustSchedule'])->name('adjust-schedule');
            Route::post('/{loan}/waive-installment',[LoanController::class, 'waiveInstallment'])->name('waive-installment');
            Route::post('/{loan}/add-late-fee',     [LoanController::class, 'addLateFee'])->name('add-late-fee');

            // Payments
            Route::post('/{loan}/record-payment',   [LoanController::class, 'recordPayment'])->name('record-payment');
            Route::post('/{loan}/reverse-payment',  [LoanController::class, 'reversePayment'])->name('reverse-payment');

            // Loan lifecycle
            // ── DISBURSEMENT: GET = confirmation screen, POST = execute ──
            Route::get('/{loan}/disburse',          [LoanController::class, 'disbursementConfirm'])->name('disburse.confirm');
            Route::post('/{loan}/disburse',         [LoanController::class, 'disburse'])->name('disburse');
            Route::post('/{loan}/close',            [LoanController::class, 'close'])->name('close');
            Route::post('/{loan}/write-off',        [LoanController::class, 'writeOff'])->name('write-off');
            Route::post('/{loan}/restructure',      [LoanController::class, 'restructure'])->name('restructure');
            Route::post('/{loan}/mark-defaulted',   [LoanController::class, 'markDefaulted'])->name('mark-defaulted');

            // Documents
            Route::get('/{loan}/agreement',              [LoanController::class, 'agreement'])->name('agreement');
            Route::get('/{loan}/statement',              [LoanController::class, 'statement'])->name('statement');
            Route::get('/{loan}/receipt/{payment}',      [LoanController::class, 'receipt'])->name('receipt');
            Route::get('/{loan}/settlement-quotation',   [LoanController::class, 'settlementQuotation'])->name('settlement-quotation');
            Route::get('/{loan}/settlement-letter',      [LoanController::class, 'settlementLetter'])->name('settlement-letter');
        });

        /*
        | ── PAYMENT TRACKING ───────────────────────────────────────
        */
        Route::prefix('payments')->name('payments.')->group(function () {

            // ── Static routes FIRST (before any {payment} wildcard) ──
            Route::get('/',                     [PaymentController::class, 'index'])->name('index');
            Route::get('/export',               [PaymentController::class, 'export'])->name('export');
            Route::get('/pending',              [PaymentController::class, 'pending'])->name('pending');
            Route::get('/reconciliation',       [PaymentController::class, 'reconciliation'])->name('reconciliation');
            Route::post('/reconcile',           [PaymentController::class, 'reconcile'])->name('reconcile');
            Route::post('/bulk-verify',         [PaymentController::class, 'bulkVerify'])->name('bulk-verify');
            Route::post('/bulk-reject',         [PaymentController::class, 'bulkReject'])->name('bulk-reject');

            // ── Wildcard routes LAST ──
            Route::get('/{payment}',            [PaymentController::class, 'show'])->name('show');
            Route::post('/{payment}/verify',    [PaymentController::class, 'verify'])->name('verify');
            Route::post('/{payment}/reject',    [PaymentController::class, 'reject'])->name('reject');
            Route::post('/{payment}/reverse',   [PaymentController::class, 'reverse'])->name('reverse');
        });

        /*
        | ── REPORTS ────────────────────────────────────────────────
        */
        Route::prefix('reports')->name('reports.')->group(function () {

            Route::get('/', [ReportController::class, 'index'])->name('index');

            Route::get('/portfolio',            [ReportController::class, 'portfolio'])->name('portfolio');
            Route::get('/disbursement',         [ReportController::class, 'disbursement'])->name('disbursement');
            Route::get('/repayment',            [ReportController::class, 'repayment'])->name('repayment');
            Route::get('/arrears',              [ReportController::class, 'arrears'])->name('arrears');
            Route::get('/collections',          [ReportController::class, 'collections'])->name('collections');
            Route::get('/outstanding',          [ReportController::class, 'outstanding'])->name('outstanding');
            Route::get('/par',                  [ReportController::class, 'par'])->name('par');
            Route::get('/default',              [ReportController::class, 'default'])->name('default');
            Route::get('/applications',         [ReportController::class, 'applications'])->name('applications');
            Route::get('/payment-failures',     [ReportController::class, 'paymentFailures'])->name('payment-failures');
            Route::get('/product-performance',  [ReportController::class, 'productPerformance'])->name('product-performance');
            Route::get('/officer-performance',  [ReportController::class, 'officerPerformance'])->name('officer-performance');
            Route::get('/income-statement',     [ReportController::class, 'incomeStatement'])->name('income-statement');
            Route::get('/borrower-demographics',[ReportController::class, 'borrowerDemographics'])->name('borrower-demographics');

            Route::post('/export',              [ReportController::class, 'export'])->name('export');
            Route::get('/scheduled',            [ReportController::class, 'scheduledIndex'])->name('scheduled.index');
            Route::post('/scheduled',           [ReportController::class, 'scheduledStore'])->name('scheduled.store');
            Route::delete('/scheduled/{id}',    [ReportController::class, 'scheduledDestroy'])->name('scheduled.destroy');
        });

        /*
        | ── USER MANAGEMENT ────────────────────────────────────────
        */
        Route::prefix('users')->name('users.')->group(function () {

            Route::get('/',                         [UserController::class, 'index'])->name('index');
            Route::get('/create',                   [UserController::class, 'create'])->name('create');
            Route::post('/',                        [UserController::class, 'store'])->name('store');
            Route::get('/export',                   [UserController::class, 'export'])->name('export');
            Route::post('/import',                  [UserController::class, 'import'])->name('import');

            // Profile Change Requests (Moved above {user} to prevent misrouting)
            Route::get('/profile-requests',         [UserController::class, 'profileRequests'])->name('profile-requests');
            Route::post('/profile-requests/{request}', [UserController::class, 'handleProfileRequest'])->name('profile-requests.action');

            Route::get('/{user}',                   [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit',              [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',                   [UserController::class, 'update'])->name('update');
            Route::delete('/{user}',                [UserController::class, 'destroy'])->name('destroy');

            // Status & access
            Route::post('/{user}/toggle-status',    [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{user}/reset-password',   [UserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{user}/impersonate',      [UserController::class, 'impersonate'])->name('impersonate');  // admin only

            // Activity
            Route::get('/{user}/activity',          [UserController::class, 'activity'])->name('activity');
            Route::get('/{user}/loans',             [UserController::class, 'loans'])->name('loans');
            Route::get('/{user}/applications',      [UserController::class, 'applications'])->name('applications');

            // Export
            Route::get('/export',                   [UserController::class, 'export'])->name('export');
        });

        /*
        | ── LOAN PRODUCTS ──────────────────────────────────────────
        */
        Route::prefix('products')->name('products.')->group(function () {

            Route::get('/',                 [ProductController::class, 'index'])->name('index');
            Route::get('/create',           [ProductController::class, 'create'])->name('create');
            Route::post('/',                [ProductController::class, 'store'])->name('store');
            Route::get('/{product}',        [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit',   [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}',        [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}',     [ProductController::class, 'destroy'])->name('destroy');

            // Toggle active/inactive
            Route::post('/{product}/toggle', [ProductController::class, 'toggle'])->name('toggle');

            // Analytics for this product
            Route::get('/{product}/stats',   [ProductController::class, 'stats'])->name('stats');
        });

        /*
        | ── CREDIT BUREAU ──────────────────────────────────────────
        */
        Route::prefix('credit-bureau')->name('credit.')->group(function () {

            Route::get('/',                             [CreditBureauController::class, 'index'])->name('index');
            Route::get('/reports',                      [CreditBureauController::class, 'reports'])->name('reports');
            Route::get('/reports/{report}',             [CreditBureauController::class, 'viewReport'])->name('view-report');

            Route::post('/pull-report',                 [CreditBureauController::class, 'pullReport'])->name('pull-report');
            Route::post('/submit-monthly',              [CreditBureauController::class, 'submitMonthly'])->name('submit-monthly');
            Route::get('/monthly-submissions',          [CreditBureauController::class, 'monthlySubmissions'])->name('monthly-submissions');
            Route::get('/monthly-submissions/{id}',     [CreditBureauController::class, 'viewSubmission'])->name('view-submission');
        });

        /*
        | ── COMPUSCAN (CCI) ──────────────────────────────────────────
        */
        Route::prefix('compuscan')->name('compuscan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CompuscanController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Admin\CompuscanController::class, 'generate'])->name('generate');
        });

        /*
        | ── SYSTEM SETTINGS ────────────────────────────────────────
        */
        Route::prefix('settings')->name('settings.')->group(function () {

            Route::get('/', [SettingsController::class, 'index'])->name('index');

            // Grouped setting saves
            Route::post('/general',          [SettingsController::class, 'updateGeneral'])->name('general');
            Route::post('/company',          [SettingsController::class, 'updateCompany'])->name('company');
            Route::post('/payment-gateway',  [SettingsController::class, 'updatePaymentGateway'])->name('payment-gateway');
            Route::post('/credit-bureau',    [SettingsController::class, 'updateCreditBureau'])->name('credit-bureau');
            Route::post('/notifications',    [SettingsController::class, 'updateNotifications'])->name('notifications');
            Route::post('/security',         [SettingsController::class, 'updateSecurity'])->name('security');
            Route::post('/email-templates',  [SettingsController::class, 'updateEmailTemplates'])->name('email-templates');

            // Test connections
            Route::post('/test-email',       [SettingsController::class, 'testEmail'])->name('test-email');
            Route::post('/test-sms',         [SettingsController::class, 'testSms'])->name('test-sms');
            Route::post('/test-gateway',     [SettingsController::class, 'testGateway'])->name('test-gateway');
        });

        /*
        | ── NOTIFICATIONS ──────────────────────────────────────────
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                         [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read',               [NotificationController::class, 'markRead'])->name('read');
            Route::post('/mark-all-read',           [NotificationController::class, 'markAllRead'])->name('read-all');
            Route::delete('/{id}',                  [NotificationController::class, 'destroy'])->name('destroy');
        });

        /*
        | ── AUDIT LOG ──────────────────────────────────────────────
        */
        Route::prefix('audit-log')->name('audit.')->group(function () {
            Route::get('/',             [AuditLogController::class, 'index'])->name('index');
            Route::get('/export',       [AuditLogController::class, 'export'])->name('export');
            Route::get('/{log}',        [AuditLogController::class, 'show'])->name('show');
        });

        /*
        | ── PROFILE (admin's own account) ──────────────────────────
        */
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',                     [AuthController::class, 'profile'])->name('index');
            Route::put('/',                     [AuthController::class, 'updateProfile'])->name('update');
            Route::put('/password',             [AuthController::class, 'updatePassword'])->name('password');
            Route::post('/photo',               [AuthController::class, 'updatePhoto'])->name('photo');
        });

    }); // end auth:admin

}); // end prefix admin
