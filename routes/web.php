<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', [\App\Http\Controllers\Common\PublicPortalController::class, 'index'])->name('home');

Route::get('/api/banks', [\App\Http\Controllers\Admin\BankController::class, 'getBanks']);
Route::get('/api/banks/{bank}/branches', [\App\Http\Controllers\Admin\BankController::class, 'getBranches']);

