<?php
use Illuminate\Support\Facades\Route;

// Redirect root directly to admin login
Route::get('/', fn() => redirect()->route('admin.login'));