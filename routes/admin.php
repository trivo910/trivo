<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\AdminIpAllow;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\PendingAdminLogin;
use Illuminate\Support\Facades\Route;

$path = trim((string) config('trivo.admin_path'), '/');
if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{7,63}$/D', $path)
    || in_array(strtolower($path), ['wp-admin', 'wp-login', 'administrator'], true)) {
    return;
}

Route::middleware(['web', AdminIpAllow::class])->prefix($path)->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'authenticate'])->middleware('throttle:admin-login')->name('authenticate');
    Route::middleware(PendingAdminLogin::class)->group(function () {
        Route::get('two-factor/enroll', [AuthController::class, 'enrollment'])->name('enroll');
        Route::post('two-factor/enroll', [AuthController::class, 'enroll'])->middleware('throttle:admin-two-factor')->name('enroll.store');
        Route::get('two-factor/challenge', [AuthController::class, 'challenge'])->name('challenge');
        Route::post('two-factor/challenge', [AuthController::class, 'verify'])->middleware('throttle:admin-two-factor')->name('challenge.store');
    });
    Route::middleware(['auth', 'auth.session', EnsureAdminAuthenticated::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->middleware('can:dashboard.view')->name('dashboard');
        Route::get('_styleguide', [DashboardController::class, 'styleguide'])->middleware('can:view-styleguide')->name('styleguide');
        Route::get('activity', [DashboardController::class, 'activity'])->middleware('can:activity.view')->name('activity');
        Route::patch('preferences', [DashboardController::class, 'preferences'])->name('preferences');
        Route::get('recovery-codes', [AuthController::class, 'recoveryCodes'])->name('recovery-codes');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
