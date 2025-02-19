<?php

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Controllers\LoginController as NovaLoginController;
use Laravel\Nova\Http\Controllers\LogoutController as NovaLogoutController;
use Laravel\Nova\Http\Controllers\ForgotPasswordController as NovaForgotPasswordController;

Route::middleware('guest')->group(function () {
// ✅ Redirect login to Nova’s login controller
Route::get('login', [NovaLoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [NovaLoginController::class, 'login']);

// ✅ Use Nova’s password reset functionality
Route::get('forgot-password', [NovaForgotPasswordController::class, 'showLinkRequestForm'])
->name('password.request');
Route::post('forgot-password', [NovaForgotPasswordController::class, 'sendResetLinkEmail'])
->name('password.email');

Route::get('reset-password/{token}', [NovaForgotPasswordController::class, 'showResetForm'])
->name('password.reset');
Route::post('reset-password', [NovaForgotPasswordController::class, 'reset'])
->name('password.store');
});

// ✅ Use Nova’s logout system
Route::middleware('auth')->group(function () {
Route::post('logout', [NovaLogoutController::class, 'logout'])->name('logout');
});
