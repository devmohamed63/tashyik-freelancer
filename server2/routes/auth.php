<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
// use App\Http\Controllers\Auth\NewPasswordController;
// use App\Http\Controllers\Auth\PasswordController;
// use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'showForgotForm'])
        ->name('password.request');

    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'sendOtp'])
        ->name('password.email');

    Route::get('/verify-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'showVerifyForm'])
        ->name('dashboard.password.otp.verify.form');

    Route::post('/verify-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'verifyOtp'])
        ->name('dashboard.password.otp.verify');

    Route::get('/reset-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'showResetForm'])
        ->name('dashboard.password.reset.form');

    Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'resetPassword'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
