<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Borrower\AuthController;
use App\Http\Controllers\Borrower\DashboardController;
use App\Http\Controllers\Borrower\ApplicationController;
use App\Http\Controllers\Borrower\LoanController;
use App\Http\Controllers\Borrower\PaymentController;
use App\Http\Controllers\Borrower\DocumentController;
use App\Http\Controllers\Borrower\StatementController;
use App\Http\Controllers\Borrower\ProfileController;
use App\Http\Controllers\Borrower\AffordabilityController;
use App\Http\Controllers\Borrower\NotificationController;

/*
|--------------------------------------------------------------------------
| BORROWER ROUTES  —  prefix: /portal   name: borrower.*
|--------------------------------------------------------------------------
|
| This is the self-service borrower portal. Borrowers can:
|   - Register & verify email
|   - Apply for a loan (9-step wizard)
|   - Track their application status
|   - View active/past loans and repayment schedules
|   - Make payments
|   - Upload & manage documents
|   - Download statements
|   - Update their profile
|
*/

Route::prefix('portal')->name('borrower.')->group(function () {

    /*
    |------------------------------------------------------------------
    | PUBLIC — Landing, Register, Login, Reset
    |------------------------------------------------------------------
    */
    Route::get('/', function() {
        if (Auth::guard('borrower')->check()) return redirect()->route('borrower.dashboard');
        return view('borrower.welcome');
    })->name('welcome');


    Route::middleware('guest:borrower')->group(function () {

        // Registration
        Route::get('/register',             [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register',            [AuthController::class, 'register'])->name('register.post');

        // Email verification landing
        Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify-email');

        // Login
        Route::get('/login',                [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login',               [AuthController::class, 'login'])->name('login.post');

        // Password reset
        Route::get('/forgot-password',          [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password',         [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}',   [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password',          [AuthController::class, 'resetPassword'])->name('password.update');
    });

    // ── Phone Verification (auth required, unverified) ──────────────────────
    Route::middleware('auth:borrower')->group(function () {
        Route::get('/verify-phone',  [\App\Http\Controllers\Borrower\PhoneVerificationController::class, 'show'])->name('phone.verify.show');
        Route::post('/verify-phone', [\App\Http\Controllers\Borrower\PhoneVerificationController::class, 'verify'])->name('phone.verify');
        Route::post('/verify-phone/resend', [\App\Http\Controllers\Borrower\PhoneVerificationController::class, 'resend'])->name('phone.resend');
    });

    // Email verification notice (after register, before confirming)
    Route::get('/email-verification-pending', fn() => view('borrower.auth.verify-pending'))->name('verify.pending');

    /*
    |------------------------------------------------------------------
    | AUTHENTICATED BORROWER
    |------------------------------------------------------------------
    */
    Route::middleware(['auth:borrower', 'borrower.active', 'borrower.verified'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        | ── DASHBOARD ──────────────────────────────────────────────
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /*
        | ── LOAN APPLICATION (9-step wizard) ───────────────────────
        |
        | Step 1  — Personal Information
        | Step 2  — Address Information
        | Step 3  — Employment Information
        | Step 4  — Bank Details
        | Step 5  — Next of Kin / References
        | Step 6  — Affordability Calculator
        | Step 7  — Loan Product Selection & Terms
        | Step 8  — Document Upload
        | Step 9  — Review & Submit
        */
        Route::prefix('apply')->name('apply.')->group(function () {

            // Entry point — create draft or redirect to existing draft
            Route::get('/',                         [ApplicationController::class, 'start'])->name('start');

            // Step display & save
            Route::get('/{application}/step/{step}',    [ApplicationController::class, 'showStep'])->name('step.show');
            Route::post('/{application}/step/{step}',   [ApplicationController::class, 'saveStep'])->name('step.save');

            // Save draft without advancing
            Route::post('/{application}/save-draft',    [ApplicationController::class, 'saveDraft'])->name('save-draft');

            // Final submit (from step 9)
            Route::post('/{application}/submit',        [ApplicationController::class, 'submit'])->name('submit');

            // Submitted confirmation screen
            Route::get('/{application}/submitted',      [ApplicationController::class, 'submitted'])->name('submitted');

            // Affordability calculator AJAX
            Route::post('/calculate-affordability',     [AffordabilityController::class, 'calculate'])->name('affordability.calculate');

            // Loan product terms AJAX (fetches rate, monthly payment preview)
            Route::get('/product-terms/{product}',      [ApplicationController::class, 'productTerms'])->name('product-terms');

            // Card Verification (Step 9 legacy - removed in favor of application fee)
            // Route::get('/{application}/verify-card',    [ApplicationController::class, 'initiateCardVerification'])->name('verify-card');
            // Route::match(['get', 'post'], '/{application}/card-success',   [ApplicationController::class, 'cardVerificationSuccess'])->name('card-success');

            // Application Fee (New flow)
            Route::get('/{application}/pay-fee',         [ApplicationController::class, 'showPayFee'])->name('pay-fee');
            Route::post('/{application}/pay-fee',        [ApplicationController::class, 'initiateFeePayment'])->name('pay-fee.initiate');
            Route::match(['get', 'post'], '/{application}/fee-success',     [ApplicationController::class, 'feePaymentSuccess'])->name('fee-success');
        });

        /*
        | ── MY APPLICATIONS ────────────────────────────────────────
        */
        Route::prefix('applications')->name('applications.')->group(function () {

            Route::get('/',                 [ApplicationController::class, 'index'])->name('index');
            Route::get('/{application}',    [ApplicationController::class, 'show'])->name('show');

            // Borrower can send a message
            Route::get('/{application}/messages',     [ApplicationController::class, 'getMessages'])->name('messages.get');
            Route::post('/{application}/messages',    [ApplicationController::class, 'sendMessage'])->name('messages.send');

            // Borrower can cancel a draft or submitted application
            Route::post('/{application}/cancel',            [ApplicationController::class, 'cancel'])->name('cancel');

            // Borrower accepts loan terms (pre-disburse agreement)
            Route::get('/{application}/accept-terms',       [ApplicationController::class, 'showTerms'])->name('accept-terms');
            Route::post('/{application}/accept-terms',      [ApplicationController::class, 'acceptTerms'])->name('accept-terms.post');

            // Download application summary PDF
            Route::get('/{application}/download',           [ApplicationController::class, 'download'])->name('download');

            // Save signature later
            Route::post('/{application}/signature',         [ApplicationController::class, 'saveSignature'])->name('signature');
        });

        /*
        | ── MY LOANS ───────────────────────────────────────────────
        */
        Route::prefix('loans')->name('loans.')->group(function () {

            Route::get('/',                 [LoanController::class, 'index'])->name('index');
            Route::get('/{loan}',           [LoanController::class, 'show'])->name('show');

            // Repayment schedule
            Route::get('/{loan}/schedule',  [LoanController::class, 'schedule'])->name('schedule');

            // Download
            Route::get('/{loan}/agreement', [LoanController::class, 'downloadAgreement'])->name('agreement');
            Route::get('/{loan}/statement', [LoanController::class, 'statement'])->name('statement');
            Route::get('/{loan}/settlement', [LoanController::class, 'settlement'])->name('settlement');
            Route::get('/{loan}/settlement-letter', [LoanController::class, 'settlementLetter'])->name('settlement-letter');
        });

        /*
        | ── PAYMENTS ───────────────────────────────────────────────
        */
        Route::prefix('payments')->name('payments.')->group(function () {

            // Payment history
            Route::get('/',                         [PaymentController::class, 'index'])->name('index');

            // Initiate payment
            Route::get('/make',                     [PaymentController::class, 'showMakePayment'])->name('make');
            Route::post('/initiate',                [PaymentController::class, 'initiate'])->name('initiate');

            // CPay OTP confirmation (Step 2)
            Route::post('/confirm-otp',             [PaymentController::class, 'confirmOtp'])->name('confirm-otp');

            // CPay OTP/USSD waiting page status poll (AJAX)
            Route::get('/status',                   [PaymentController::class, 'checkStatus'])->name('status');

            // Payment gateway callback handlers
            Route::match(['get', 'post'], '/callback/success',         [PaymentController::class, 'callbackSuccess'])->name('callback.success');
            Route::match(['get', 'post'], '/callback/cancel',          [PaymentController::class, 'callbackCancel'])->name('callback.cancel');
            Route::match(['get', 'post'], '/callback/failed',          [PaymentController::class, 'callbackFailed'])->name('callback.failed');

            // Receipt
            Route::get('/{payment}/receipt',        [PaymentController::class, 'receipt'])->name('receipt');
            Route::get('/{payment}',                [PaymentController::class, 'show'])->name('show');
        });

        /*
        | ── DOCUMENTS ──────────────────────────────────────────────
        */
        Route::prefix('documents')->name('documents.')->group(function () {

            Route::get('/',                         [DocumentController::class, 'index'])->name('index');

            // Upload a document
            Route::get('/upload',                   [DocumentController::class, 'showUpload'])->name('upload');
            Route::post('/upload',                  [DocumentController::class, 'upload'])->name('upload.post');
            Route::post('/upload/{application}',    [DocumentController::class, 'uploadForApplication'])->name('upload.application');
            // View / download
            Route::get('/{document}',               [DocumentController::class, 'show'])->name('show');
            Route::get('/{document}/download',      [DocumentController::class, 'download'])->name('download');

            // Delete (only pending docs that haven't been verified)
            Route::delete('/{document}',            [DocumentController::class, 'destroy'])->name('destroy');
        });

        /*
        | ── STATEMENTS ─────────────────────────────────────────────
        */
        Route::prefix('statements')->name('statements.')->group(function () {
            Route::get('/',                         [StatementController::class, 'index'])->name('index');
            Route::get('/loan/{loan}',              [StatementController::class, 'loanStatement'])->name('loan');
            Route::post('/generate',                [StatementController::class, 'generate'])->name('generate');
            Route::get('/download/{statement}',     [StatementController::class, 'download'])->name('download');
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
        | ── REFERRALS ──────────────────────────────────────────────
        */
        Route::prefix('referrals')->name('referrals.')->group(function () {
            Route::get('/',                     [\App\Http\Controllers\Borrower\ReferralController::class, 'index'])->name('index');
        });

        /*
        | ── MYBILL — Credit bill-payment ──────────────────────────
        */
        Route::prefix('mybill')->name('mybill.')->group(function () {
            Route::get('/',                    [\App\Http\Controllers\Borrower\MyBillController::class, 'index'])->name('index');
            Route::get('/purchase/{category}', [\App\Http\Controllers\Borrower\MyBillController::class, 'showPurchase'])->name('purchase');
            Route::post('/quote',              [\App\Http\Controllers\Borrower\MyBillController::class, 'quote'])->name('quote');
            Route::post('/lookup-meter',       [\App\Http\Controllers\Borrower\MyBillController::class, 'lookupMeter'])->name('lookup-meter');
            Route::post('/lookup-insurance',   [\App\Http\Controllers\Borrower\MyBillController::class, 'lookupInsurance'])->name('lookup-insurance');
            Route::post('/event-details',      [\App\Http\Controllers\Borrower\MyBillController::class, 'eventDetails'])->name('event-details');
            Route::post('/confirm',            [\App\Http\Controllers\Borrower\MyBillController::class, 'confirm'])->name('confirm');
            Route::post('/purchase',           [\App\Http\Controllers\Borrower\MyBillController::class, 'store'])->name('store');
            Route::get('/loans/{loan}',        [\App\Http\Controllers\Borrower\MyBillController::class, 'show'])->name('show');
            Route::get('/history',             [\App\Http\Controllers\Borrower\MyBillController::class, 'history'])->name('history');
        });

        /*
        | ── PROFILE ────────────────────────────────────────────────
        */
        Route::prefix('profile')->name('profile.')->group(function () {

            Route::get('/',                         [ProfileController::class, 'index'])->name('index');

            // Personal info
            Route::put('/personal',                 [ProfileController::class, 'updatePersonal'])->name('personal');

            // Address
            Route::put('/address',                  [ProfileController::class, 'updateAddress'])->name('address');

            // Employment
            Route::put('/employment',               [ProfileController::class, 'updateEmployment'])->name('employment');

            // Bank details
            Route::put('/bank',                     [ProfileController::class, 'updateBank'])->name('bank');

            // Next of kin
            Route::put('/next-of-kin',              [ProfileController::class, 'updateNextOfKin'])->name('next-of-kin');

            // Password
            Route::put('/password',                 [ProfileController::class, 'updatePassword'])->name('password');

            // Photo
            Route::post('/photo',                   [ProfileController::class, 'updatePhoto'])->name('photo');

            // Request Change
            Route::post('/request-change',          [ProfileController::class, 'requestChange'])->name('request-change');
        });

    }); // end auth:borrower

}); // end prefix portal

/*
|--------------------------------------------------------------------------
| PAYMENT GATEWAY WEBHOOKS — no CSRF, no auth
| These are called by the payment gateway server, not the browser.
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/payment',         [\App\Http\Controllers\Webhook\PaymentWebhookController::class, 'handle'])->name('payment');
    Route::post('/credit-bureau',   [\App\Http\Controllers\Webhook\CreditBureauWebhookController::class, 'handle'])->name('credit-bureau');

    // M-Pesa Webhooks
    Route::prefix('mpesa')->name('mpesa.')->group(function () {
        Route::post('/repayment',       [\App\Http\Controllers\Webhook\MpesaWebhookController::class, 'repaymentConfirmation'])->name('repayment');
        Route::post('/disburse-result', [\App\Http\Controllers\Webhook\MpesaWebhookController::class, 'disbursementResult'])->name('disburse.result');
        Route::post('/disburse-timeout', [\App\Http\Controllers\Webhook\MpesaWebhookController::class, 'disbursementTimeout'])->name('disburse.timeout');
    });
});

/*
|--------------------------------------------------------------------------
| PUBLIC PORTAL — Landing page, loan info, FAQ
|--------------------------------------------------------------------------
*/
Route::get('/',             [\App\Http\Controllers\Common\PublicPortalController::class, 'index'])->name('home');
Route::get('/about',        [\App\Http\Controllers\Common\PublicPortalController::class, 'about'])->name('about');
Route::get('/products',     [\App\Http\Controllers\Common\PublicPortalController::class, 'products'])->name('products');
Route::get('/contact',      [\App\Http\Controllers\Common\PublicPortalController::class, 'contact'])->name('contact');
Route::post('/contact',     [\App\Http\Controllers\Common\PublicPortalController::class, 'sendContact'])->name('contact.send');
Route::get('/faq',          [\App\Http\Controllers\Common\PublicPortalController::class, 'faq'])->name('faq');

Route::get('/privacy',      [\App\Http\Controllers\Common\PublicPortalController::class, 'privacy'])->name('privacy');
Route::get('/terms',        [\App\Http\Controllers\Common\PublicPortalController::class, 'terms'])->name('terms');
