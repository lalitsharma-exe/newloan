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
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\FloatController;
use App\Http\Controllers\Admin\ExpenseController;

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
        Route::middleware('admin.permission:dashboard')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            // AJAX widget refreshes
            Route::get('/dashboard/stats',            [DashboardController::class, 'stats'])->name('dashboard.stats');
            Route::get('/dashboard/chart-data',       [DashboardController::class, 'chartData'])->name('dashboard.chart');
            Route::get('/dashboard/notifications',    [DashboardController::class, 'notifications'])->name('dashboard.notifications');
        });

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
            Route::post('/{application}/verify-payment',      [ApplicationController::class, 'verifyPayment'])->name('verify-payment');
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
            
            // Update Routes (POST for saving, GET for fallback redirect)
            Route::post('/{application}/affordability', [ApplicationController::class, 'updateAffordability'])->name('update-affordability');
            Route::post('/{application}/employment', [ApplicationController::class, 'updateEmployment'])->name('update-employment');
            Route::post('/{application}/bank-details', [ApplicationController::class, 'updateBankDetails'])->name('update-bank-details');
            Route::post('/{application}/personal', [ApplicationController::class, 'updatePersonal'])->name('update-personal');
            Route::post('/{application}/address', [ApplicationController::class, 'updateAddress'])->name('update-address');
            Route::post('/{application}/loan-request', [ApplicationController::class, 'updateLoanRequest'])->name('update-loan-request');

            // GET fallbacks to prevent 405 errors
            Route::get('/{application}/affordability', fn($a) => redirect()->route('admin.applications.show', $a));
            Route::get('/{application}/employment',    fn($a) => redirect()->route('admin.applications.show', $a));
            Route::get('/{application}/bank-details',  fn($a) => redirect()->route('admin.applications.show', $a));
            Route::get('/{application}/personal',      fn($a) => redirect()->route('admin.applications.show', $a));
            Route::get('/{application}/address',       fn($a) => redirect()->route('admin.applications.show', $a));
            Route::get('/{application}/loan-request',  fn($a) => redirect()->route('admin.applications.show', $a));

            // AJAX Chat & Management
            Route::middleware('admin.permission:applications.manage')->group(function () {
                // AJAX Chat
                Route::get('/{application}/messages', [ApplicationController::class, 'getMessages'])->name('messages.get');
                Route::post('/{application}/messages', [ApplicationController::class, 'sendMessage'])->name('messages.send');

                // Repayment schedule preview
                Route::get('/{application}/schedule-preview',    [ApplicationController::class, 'schedulePreview'])->name('schedule-preview');
            });

            // Experian Template
            Route::get('/{application}/experian-template', [ApplicationController::class, 'experianTemplate'])->name('experian-template');
        });

        // Decline Tracker
        Route::prefix('declines')->name('declines.')->middleware('admin.permission:applications.view')->group(function () {
            Route::get('/',             [\App\Http\Controllers\Admin\DeclineController::class, 'index'])->name('index');
            Route::get('/report',       [\App\Http\Controllers\Admin\DeclineController::class, 'report'])->name('report');
            Route::get('/taxonomy',     [\App\Http\Controllers\Admin\DeclineController::class, 'taxonomy'])->name('taxonomy');
            Route::post('/',            [\App\Http\Controllers\Admin\DeclineController::class, 'store'])->name('store');
        });

        /*
        | ── LOAN MANAGEMENT ────────────────────────────────────────
        */
        Route::prefix('loans')->name('loans.')->middleware('admin.permission:loans.view')->group(function () {
            Route::get('/',             [LoanController::class, 'index'])->name('index');
            Route::get('/export',       [LoanController::class, 'export'])->name('export');
            Route::get('/overdue',      [LoanController::class, 'overdue'])->name('overdue');

            Route::middleware('admin.permission:loans.manage')->group(function() {
                Route::get('/bulk-repayment',  [LoanController::class, 'bulkRepayment'])->name('bulk-repayment');
                Route::post('/bulk-repayment', [LoanController::class, 'bulkRepayment'])->name('bulk-repayment.post');
                Route::get('/import',          [LoanController::class, 'importLoans'])->name('import');
                Route::post('/import',         [LoanController::class, 'importLoans'])->name('import.post');
                Route::get('/collection-sheet',[LoanController::class, 'collectionSheet'])->name('collection-sheet');
                Route::get('/repayment-chart', [LoanController::class, 'repaymentChart'])->name('repayment-chart');
                Route::get('/lookup',          [LoanController::class, 'lookup'])->name('lookup');
            });

            Route::get('/{loan}',                   [LoanController::class, 'show'])->name('show');
            Route::get('/{loan}/schedule',          [LoanController::class, 'schedule'])->name('schedule');
            Route::get('/{loan}/agreement',              [LoanController::class, 'agreement'])->name('agreement');
            Route::get('/{loan}/statement',              [LoanController::class, 'statement'])->name('statement');
            Route::get('/{loan}/receipt/{payment}',      [\App\Http\Controllers\ReceiptController::class, 'show'])->name('receipt');
            Route::get('/{loan}/receipt/{payment}/pdf',  [\App\Http\Controllers\ReceiptController::class, 'download'])->name('receipt.download');
            Route::get('/{loan}/settlement-quotation',   [LoanController::class, 'settlementQuotation'])->name('settlement-quotation');
            Route::get('/{loan}/settlement-letter',      [LoanController::class, 'settlementLetter'])->name('settlement-letter');

            Route::middleware('admin.permission:loans.manage')->group(function() {
                Route::post('/{loan}/adjust-schedule',  [LoanController::class, 'adjustSchedule'])->name('adjust-schedule');
                Route::post('/{loan}/waive-installment',[LoanController::class, 'waiveInstallment'])->name('waive-installment');
                Route::post('/{loan}/add-late-fee',     [LoanController::class, 'addLateFee'])->name('add-late-fee');
                Route::post('/{loan}/record-payment',   [LoanController::class, 'recordPayment'])->name('record-payment');
                Route::post('/{loan}/reverse-payment',  [LoanController::class, 'reversePayment'])->name('reverse-payment');
                Route::post('/{loan}/close',            [LoanController::class, 'close'])->name('close');
                Route::post('/{loan}/write-off',        [LoanController::class, 'writeOff'])->name('write-off');
                Route::post('/{loan}/restructure',      [LoanController::class, 'restructure'])->name('restructure');
                Route::post('/{loan}/mark-defaulted',   [LoanController::class, 'markDefaulted'])->name('mark-defaulted');
                Route::patch('/{loan}/update-details',  [LoanController::class, 'updateDetails'])->name('update-details');
            });

            Route::middleware('admin.permission:loans.disburse')->group(function() {
                Route::get('/{loan}/disburse',          [LoanController::class, 'disbursementConfirm'])->name('disburse.confirm');
                Route::post('/{loan}/disburse',         [LoanController::class, 'disburse'])->name('disburse');
            });
        });

        /*
        | ── PAYMENT TRACKING ───────────────────────────────────────
        */
        Route::prefix('payments')->name('payments.')->middleware('admin.permission:payments.view')->group(function () {
            Route::get('/',                     [PaymentController::class, 'index'])->name('index');
            Route::get('/export',               [PaymentController::class, 'export'])->name('export');
            Route::get('/pending',              [PaymentController::class, 'pending'])->name('pending');
            Route::get('/{payment}',            [PaymentController::class, 'show'])->name('show');

            Route::middleware('admin.permission:payments.manage')->group(function() {
                Route::get('/reconciliation',       [PaymentController::class, 'reconciliation'])->name('reconciliation');
                Route::post('/reconcile',           [PaymentController::class, 'reconcile'])->name('reconcile');
                Route::post('/bulk-verify',         [PaymentController::class, 'bulkVerify'])->name('bulk-verify');
                Route::post('/bulk-reject',         [PaymentController::class, 'bulkReject'])->name('bulk-reject');
                Route::post('/{payment}/verify',    [PaymentController::class, 'verify'])->name('verify');
                Route::post('/{payment}/reject',    [PaymentController::class, 'reject'])->name('reject');
                Route::post('/{payment}/reverse',   [PaymentController::class, 'reverse'])->name('reverse');
            });
        });

        /*
        | ── FINANCIAL INFRASTRUCTURE (Treasury & Liquidity) ───────
        | ── Added as per Phase 2 Implementation Guide ───────────
        */
        Route::prefix('financial')->name('financial.')->group(function () {
            Route::get('/dashboard', [FinancialController::class, 'dashboard'])->name('dashboard');
            Route::get('/accounts', [FinancialController::class, 'accounts'])->name('accounts');
            Route::post('/accounts', [FinancialController::class, 'storeAccount'])->name('accounts.store');
            Route::post('/accounts/{account}/update', [FinancialController::class, 'updateAccount'])->name('accounts.update');
            
            // Internal Transfers
            Route::get('/transfers', [FinancialController::class, 'transfers'])->name('transfers');
            Route::post('/transfers/initiate', [FinancialController::class, 'initiateTransfer'])->name('transfers.initiate');
            Route::post('/transfers/{transfer}/confirm', [FinancialController::class, 'confirmTransfer'])->name('transfers.confirm');
            
            // Expense Management
            Route::prefix('expenses')->name('expenses.')->group(function () {
                Route::get('/', [ExpenseController::class, 'index'])->name('index');
                Route::post('/', [ExpenseController::class, 'store'])->name('store');
                Route::get('/subcategories', [ExpenseController::class, 'getSubcategories'])->name('subcategories');
                Route::get('/taxonomy-items', [ExpenseController::class, 'getTaxonomyItems'])->name('taxonomy-items');
                Route::get('/export', [ExpenseController::class, 'export'])->name('export');
                Route::post('/{expense}/pay', [ExpenseController::class, 'pay'])->name('pay');
            });
            
            // Forecasting
            Route::post('/forecasts/refresh', [FinancialController::class, 'refreshForecasts'])->name('forecasts.refresh');
        });

        /*
        | ── MYLOAN FLOAT (Emergency Cash) ──────────────────────────
        */
        Route::prefix('float')->name('float.')->group(function () {
            Route::get('/', [FloatController::class, 'index'])->name('index');
            Route::get('/{float}', [FloatController::class, 'show'])->name('show');
            Route::post('/{float}/approve', [FloatController::class, 'approve'])->name('approve');
            Route::post('/{float}/reject', [FloatController::class, 'reject'])->name('reject');
            Route::post('/{float}/disburse', [FloatController::class, 'disburse'])->name('disburse');
            Route::post('/user/{user}/freeze', [FloatController::class, 'freeze'])->name('freeze');
        });

        /*
        | ── REPORTS ────────────────────────────────────────────────
        */
        Route::prefix('reports')->name('reports.')->middleware('admin.permission:reports')->group(function () {

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

            // CBL Regulatory Report
            Route::prefix('cbl')->name('cbl.')->group(function () {
                Route::get('/',                 [\App\Http\Controllers\Admin\CblReportController::class, 'index'])->name('index');
                Route::get('/generate',         [\App\Http\Controllers\Admin\CblReportController::class, 'generate'])->name('generate');
                Route::post('/archive',         [\App\Http\Controllers\Admin\CblReportController::class, 'archive'])->name('archive');
                Route::get('/archive/{id}',     [\App\Http\Controllers\Admin\CblReportController::class, 'viewArchive'])->name('view-archive');
                
                // Complaints Register
                Route::get('/complaints',       [\App\Http\Controllers\Admin\CblReportController::class, 'complaints'])->name('complaints');
                Route::post('/complaints',      [\App\Http\Controllers\Admin\CblReportController::class, 'storeComplaint'])->name('complaints.store');
                Route::post('/complaints/{complaint}/update', [\App\Http\Controllers\Admin\CblReportController::class, 'updateComplaint'])->name('complaints.update');
                Route::get('/search-users',     [\App\Http\Controllers\Admin\CblReportController::class, 'searchUsers'])->name('search-users');
            });
            Route::get('/borrower-demographics',[ReportController::class, 'borrowerDemographics'])->name('borrower-demographics');
            Route::get('/collection-sheet',     [ReportController::class, 'collectionSheet'])->name('collection-sheet');
            Route::get('/collection-sheet/export', [ReportController::class, 'exportCollectionSheet'])->name('collection-sheet.export');

            Route::post('/export',              [ReportController::class, 'export'])->name('export');
            Route::get('/scheduled',            [ReportController::class, 'scheduledIndex'])->name('scheduled.index');
            Route::post('/scheduled',           [ReportController::class, 'scheduledStore'])->name('scheduled.store');
            Route::delete('/scheduled/{id}',    [ReportController::class, 'scheduledDestroy'])->name('scheduled.destroy');

            // ── 3-Tier Financial Reporting Dashboard & APIs ───────────
            Route::get('/financial-dashboard', [\App\Http\Controllers\Admin\FinancialReportController::class, 'dashboard'])->name('financial-dashboard');
            Route::prefix('financial-cycle')->name('financial-cycle.')->group(function () {
                // Monthly
                Route::get('/monthly/summary', [\App\Http\Controllers\Admin\FinancialReportController::class, 'monthlySummary'])->name('monthly.summary');
                Route::get('/monthly/arrears-provision', [\App\Http\Controllers\Admin\FinancialReportController::class, 'monthlyArrearsProvision'])->name('monthly.arrears-provision');
                
                // Quarterly
                Route::get('/quarterly/income-statement', [\App\Http\Controllers\Admin\FinancialReportController::class, 'quarterlyIncomeStatement'])->name('quarterly.income-statement');
                Route::get('/quarterly/portfolio', [\App\Http\Controllers\Admin\FinancialReportController::class, 'quarterlyPortfolio'])->name('quarterly.portfolio');
                Route::get('/quarterly/kpis', [\App\Http\Controllers\Admin\FinancialReportController::class, 'quarterlyKpis'])->name('quarterly.kpis');
                
                // Annual
                Route::get('/annual/{year}/balance-sheet', [\App\Http\Controllers\Admin\FinancialReportController::class, 'annualBalanceSheet'])->name('annual.balance-sheet');
                Route::get('/annual/{year}/cash-flow', [\App\Http\Controllers\Admin\FinancialReportController::class, 'annualCashFlow'])->name('annual.cash-flow');
                Route::get('/annual/trend', [\App\Http\Controllers\Admin\FinancialReportController::class, 'annualTrend'])->name('annual.trend');
                
                // Action locks & manual consolidation trigger
                Route::post('/annual/{year}/consolidate', [\App\Http\Controllers\Admin\FinancialReportController::class, 'annualConsolidate'])->name('annual.consolidate');
                Route::post('/{period}/lock', [\App\Http\Controllers\Admin\FinancialReportController::class, 'lockPeriod'])->name('period.lock');
            });
        });

        /*
        | ── REFERRALS ──────────────────────────────────────────────
        */
        Route::prefix('referrals')->name('referrals.')->middleware('admin.permission:referrals.view')->group(function () {
            Route::get('/',                          [\App\Http\Controllers\Admin\ReferralController::class, 'index'])->name('index');
            Route::middleware('admin.permission:referrals.manage')->group(function() {
                Route::post('/{referral}/pay',           [\App\Http\Controllers\Admin\ReferralController::class, 'markAsPaid'])->name('mark-paid');
                Route::post('/{referral}/credit-loan',   [\App\Http\Controllers\Admin\ReferralController::class, 'creditToLoan'])->name('credit-loan');
                Route::post('/{referral}/pay-mpesa',     [\App\Http\Controllers\Admin\ReferralController::class, 'payViaMpesa'])->name('pay-mpesa');
            });
        });

        /*
        | ── ADMIN ROLES & PERMISSIONS ──────────────────────────────
        */
        Route::prefix('roles')->name('roles.')->middleware('admin.permission:roles.manage')->group(function () {
            Route::get('/',              [\App\Http\Controllers\Admin\AdminRoleController::class, 'index'])->name('index');
            Route::get('/create',        [\App\Http\Controllers\Admin\AdminRoleController::class, 'create'])->name('create');
            Route::post('/',             [\App\Http\Controllers\Admin\AdminRoleController::class, 'store'])->name('store');
            Route::get('/{role}/edit',   [\App\Http\Controllers\Admin\AdminRoleController::class, 'edit'])->name('edit');
            Route::put('/{role}',        [\App\Http\Controllers\Admin\AdminRoleController::class, 'update'])->name('update');
            Route::delete('/{role}',     [\App\Http\Controllers\Admin\AdminRoleController::class, 'destroy'])->name('destroy');
        });

        /*
        | ── USER MANAGEMENT ────────────────────────────────────────
        */
        Route::prefix('users')->name('users.')->middleware('admin.permission:users.view')->group(function () {

            Route::get('/',                         [UserController::class, 'index'])->name('index');
            
            Route::middleware('admin.permission:users.manage')->group(function() {
                Route::get('/create',                   [UserController::class, 'create'])->name('create');
                Route::post('/',                        [UserController::class, 'store'])->name('store');
                Route::get('/export',                   [UserController::class, 'export'])->name('export');
                Route::post('/import',                  [UserController::class, 'import'])->name('import');
                Route::get('/import-template',          [UserController::class, 'importTemplate'])->name('import-template');

                // Profile Change Requests (Moved above {user} to prevent misrouting)
                Route::get('/profile-requests',         [UserController::class, 'profileRequests'])->name('profile-requests');
                Route::post('/profile-requests/{request}', [UserController::class, 'handleProfileRequest'])->name('profile-requests.action');
            });

            Route::get('/{user}',                   [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit',              [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',                   [UserController::class, 'update'])->name('update');
            Route::delete('/{user}',                [UserController::class, 'destroy'])->name('destroy');
            
            Route::middleware('admin.permission:users.manage')->group(function() {
            Route::post('/{user}/toggle-status',    [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{user}/toggle-float',     [UserController::class, 'toggleFloat'])->name('toggle-float');
                Route::post('/{user}/reset-password',   [UserController::class, 'resetPassword'])->name('reset-password');
                Route::post('/{user}/impersonate',      [UserController::class, 'impersonate'])->name('impersonate');  // admin only
            });

            // Activity
            Route::get('/{user}/activity',          [UserController::class, 'activity'])->name('activity');

            // Settlement
            Route::get('/{user}/consolidated-settlement', [LoanController::class, 'consolidatedSettlementQuotation'])->name('consolidated-settlement');
            Route::get('/{user}/consolidated-settlement-letter', [LoanController::class, 'consolidatedSettlementLetter'])->name('consolidated-settlement-letter');
            Route::get('/{user}/loans',             [UserController::class, 'loans'])->name('loans');
            Route::get('/{user}/applications',      [UserController::class, 'applications'])->name('applications');

        });

        /*
        | ── LOAN PRODUCTS ──────────────────────────────────────────
        */
        Route::prefix('products')->name('products.')->middleware('admin.permission:products.manage')->group(function () {
            Route::get('/',                 [ProductController::class, 'index'])->name('index');
            Route::get('/create',           [ProductController::class, 'create'])->name('create');
            Route::post('/',                [ProductController::class, 'store'])->name('store');
            Route::get('/{product}',        [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit',   [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}',        [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}',     [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/toggle', [ProductController::class, 'toggle'])->name('toggle');
            Route::get('/{product}/stats',   [ProductController::class, 'stats'])->name('stats');
        });

        /*
        | ── CREDIT BUREAU ──────────────────────────────────────────
        */
        Route::prefix('credit-bureau')->name('credit.')->middleware('admin.permission:credit_bureau')->group(function () {
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
        Route::prefix('compuscan')->name('compuscan.')->middleware('admin.permission:compuscan')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CompuscanController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Admin\CompuscanController::class, 'generate'])->name('generate');
        });

        /*
        | ── SYSTEM SETTINGS ────────────────────────────────────────
        */
        Route::prefix('settings')->name('settings.')->middleware('admin.permission:settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/general',          [SettingsController::class, 'updateGeneral'])->name('general');
            Route::post('/company',          [SettingsController::class, 'updateCompany'])->name('company');
            Route::post('/payment-gateway',  [SettingsController::class, 'updatePaymentGateway'])->name('payment-gateway');
            Route::post('/credit-bureau',    [SettingsController::class, 'updateCreditBureau'])->name('credit-bureau');
            Route::post('/notifications',    [SettingsController::class, 'updateNotifications'])->name('notifications');
            Route::post('/security',         [SettingsController::class, 'updateSecurity'])->name('security');
            Route::post('/email-templates',  [SettingsController::class, 'updateEmailTemplates'])->name('email-templates');
            Route::post('/test-email',       [SettingsController::class, 'testEmail'])->name('test-email');
            Route::post('/test-sms',         [SettingsController::class, 'testSms'])->name('test-sms');
            Route::post('/test-gateway',     [SettingsController::class, 'testGateway'])->name('test-gateway');
        });

        /*
        | ── BANK MANAGEMENT ────────────────────────────────────────
        */
        Route::prefix('banks')->name('banks.')->middleware('admin.permission:banks.manage')->group(function () {
            Route::get('/',                 [BankController::class, 'index'])->name('index');
            Route::post('/',                [BankController::class, 'store'])->name('store');
            Route::put('/{bank}',           [BankController::class, 'update'])->name('update');
            Route::delete('/{bank}',        [BankController::class, 'destroy'])->name('destroy');
            Route::get('/{bank}/branches',               [BankController::class, 'branches'])->name('branches');
            Route::post('/{bank}/branches',              [BankController::class, 'storeBranch'])->name('branches.store');
            Route::put('/{bank}/branches/{branch}',      [BankController::class, 'updateBranch'])->name('branches.update');
            Route::delete('/{bank}/branches/{branch}',   [BankController::class, 'destroyBranch'])->name('branches.destroy');
            Route::delete('/{bank}',        [BankController::class, 'destroy'])->name('destroy');

            Route::get('/{bank}/branches',               [BankController::class, 'branches'])->name('branches');
            Route::post('/{bank}/branches',              [BankController::class, 'storeBranch'])->name('branches.store');
            Route::put('/{bank}/branches/{branch}',      [BankController::class, 'updateBranch'])->name('branches.update');
            Route::delete('/{bank}/branches/{branch}',   [BankController::class, 'destroyBranch'])->name('branches.destroy');
        });

        /*
        | ── BULK SMS ──────────────────────────────────────────────
        */
        Route::prefix('bulk-sms')->name('bulk-sms.')->middleware('admin.permission:bulk_sms')->group(function () {
            Route::get('/',                     [\App\Http\Controllers\Admin\BulkSmsController::class, 'index'])->name('index');
            Route::get('/create',               [\App\Http\Controllers\Admin\BulkSmsController::class, 'create'])->name('create');
            Route::post('/',                    [\App\Http\Controllers\Admin\BulkSmsController::class, 'store'])->name('store');
            Route::get('/{campaign}',           [\App\Http\Controllers\Admin\BulkSmsController::class, 'show'])->name('show');
            Route::get('/{campaign}/refresh',   [\App\Http\Controllers\Admin\BulkSmsController::class, 'refresh'])->name('refresh');
        });

        /*
        | ── NOTIFICATIONS ──────────────────────────────────────────
        */
        Route::prefix('notifications')->name('notifications.')->middleware('admin.permission:notifications')->group(function () {
            Route::get('/',                         [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read',               [NotificationController::class, 'markRead'])->name('read');
            Route::post('/mark-all-read',           [NotificationController::class, 'markAllRead'])->name('read-all');
            Route::delete('/{id}',                  [NotificationController::class, 'destroy'])->name('destroy');
        });

        /*
        | ── AUDIT LOG ──────────────────────────────────────────────
        */
        Route::prefix('audit-log')->name('audit.')->middleware('admin.permission:audit_log')->group(function () {
            Route::get('/',             [AuditLogController::class, 'index'])->name('index');
            Route::get('/export',       [AuditLogController::class, 'export'])->name('export');
            Route::get('/{log}',        [AuditLogController::class, 'show'])->name('show');
        });

        /*
        | ── MYBILL — Bill-payment product ─────────────────────────
        */
        Route::middleware('admin.permission:mybill')->prefix('mybill')->name('mybill.')->group(function () {
            Route::get('/',                  [\App\Http\Controllers\Admin\MyBillController::class, 'dashboard'])->name('dashboard');
            Route::get('/loans',             [\App\Http\Controllers\Admin\MyBillController::class, 'loans'])->name('loans');
            Route::get('/loans/{loan}',      [\App\Http\Controllers\Admin\MyBillController::class, 'showLoan'])->name('loans.show');
            Route::get('/limits',            [\App\Http\Controllers\Admin\MyBillController::class, 'limits'])->name('limits');
            Route::patch('/limits/{limit}',  [\App\Http\Controllers\Admin\MyBillController::class, 'updateLimit'])->name('limits.update');
            Route::post('/payday',           [\App\Http\Controllers\Admin\MyBillController::class, 'triggerPayday'])->name('payday');
            Route::post('/settings',         [\App\Http\Controllers\Admin\MyBillController::class, 'updateSettings'])->name('settings.update');
            Route::get('/export',            [\App\Http\Controllers\Admin\MyBillController::class, 'export'])->name('export');
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

        /*
        | ── INVESTOR CAPITAL MANAGEMENT ──────────────────────────
        */
        Route::prefix('investments')->name('investments.')->group(function () {
            Route::get('/investors', [\App\Http\Controllers\Admin\InvestorController::class, 'listInvestors'])->name('investors.index');
            Route::post('/investors', [\App\Http\Controllers\Admin\InvestorController::class, 'storeInvestor'])->name('investors.store');
            Route::get('/investors/{id}', [\App\Http\Controllers\Admin\InvestorController::class, 'showInvestor'])->name('investors.show');

            Route::get('/', [\App\Http\Controllers\Admin\InvestorController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\InvestorController::class, 'storeInvestment'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\InvestorController::class, 'show'])->name('show');
            Route::post('/{id}/repay', [\App\Http\Controllers\Admin\InvestorController::class, 'repay'])->name('repay');
            Route::post('/{id}/terminate', [\App\Http\Controllers\Admin\InvestorController::class, 'requestTermination'])->name('terminate');
            Route::get('/{id}/termination-preview', [\App\Http\Controllers\Admin\InvestorController::class, 'previewTermination'])->name('terminate.preview');
            Route::post('/{id}/terminate/approve', [\App\Http\Controllers\Admin\InvestorController::class, 'approveTermination'])->name('terminate.approve');
            Route::post('/{id}/terminate/decline', [\App\Http\Controllers\Admin\InvestorController::class, 'declineTermination'])->name('terminate.decline');
            Route::get('/{id}/contract', [\App\Http\Controllers\Admin\InvestorController::class, 'downloadContract'])->name('contract.download');
        });

    }); // end auth:admin

}); // end prefix admin
