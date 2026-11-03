<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MailSettingsController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\WebsiteSettingsController;
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
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::get('media/create', [MediaController::class, 'create'])->name('media.create');
        Route::post('media', [MediaController::class, 'store'])->middleware('throttle:admin-media-upload')->name('media.store');
        Route::get('media/{media}', [MediaController::class, 'show'])->withTrashed()->name('media.show');
        Route::get('media/{media}/preview', [MediaController::class, 'preview'])->withTrashed()->name('media.preview');
        Route::get('media/{media}/status', [MediaController::class, 'status'])->name('media.status');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'archive'])->name('media.archive');
        Route::post('media/{media}/restore', [MediaController::class, 'restore'])->withTrashed()->name('media.restore');
        Route::post('media/{media}/retry', [MediaController::class, 'retry'])->name('media.retry');
        Route::get('settings/mail', [MailSettingsController::class, 'index'])->name('mail.index');
        Route::get('settings/{section}', [WebsiteSettingsController::class, 'edit'])->whereIn('section', ['site', 'social', 'analytics'])->name('website.edit');
        Route::put('settings/{section}', [WebsiteSettingsController::class, 'update'])->whereIn('section', ['site', 'social', 'analytics'])->name('website.update');
        Route::put('settings/mail', [MailSettingsController::class, 'update'])->name('mail.update');
        Route::post('settings/mail/test', [MailSettingsController::class, 'test'])->middleware('throttle:admin-mail-test')->name('mail.test');
        Route::get('redirects', [RedirectController::class, 'index'])->name('redirects.index');
        Route::get('redirects/create', [RedirectController::class, 'create'])->name('redirects.create');
        Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
        Route::post('redirects/import', [RedirectController::class, 'import'])->name('redirects.import');
        Route::get('redirects/{redirect}/edit', [RedirectController::class, 'edit'])->name('redirects.edit');
        Route::put('redirects/{redirect}', [RedirectController::class, 'update'])->name('redirects.update');
        Route::delete('redirects/{redirect}', [RedirectController::class, 'destroy'])->name('redirects.destroy');
        Route::get('not-found', [RedirectController::class, 'notFound'])->name('not-found');
        Route::get('recovery-codes', [AuthController::class, 'recoveryCodes'])->name('recovery-codes');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
