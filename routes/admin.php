<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MailSettingsController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSeoController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PortfolioSeoController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceSeoController;
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
        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
        Route::get('portfolio/create', [PortfolioController::class, 'create'])->name('portfolio.create');
        Route::post('portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
        Route::get('portfolio/{portfolio}/edit', [PortfolioController::class, 'edit'])->withTrashed()->name('portfolio.edit');
        Route::put('portfolio/{portfolio}', [PortfolioController::class, 'update'])->name('portfolio.update');
        Route::delete('portfolio/{portfolio}', [PortfolioController::class, 'archive'])->name('portfolio.archive');
        Route::post('portfolio/{portfolio}/restore', [PortfolioController::class, 'restore'])->withTrashed()->name('portfolio.restore');
        Route::post('portfolio/{portfolio}/publication', [PortfolioController::class, 'publication'])->name('portfolio.publication');
        Route::post('portfolio/{portfolio}/revisions/{revision}', [PortfolioController::class, 'revision'])->name('portfolio.revision');
        Route::get('portfolio/{portfolio}/seo', [PortfolioSeoController::class, 'edit'])->name('portfolio.seo');
        Route::put('portfolio/{portfolio}/seo', [PortfolioSeoController::class, 'update'])->name('portfolio.seo.update');
        Route::post('portfolio/{portfolio}/seo/revisions/{revision}', [PortfolioSeoController::class, 'restore'])->name('portfolio.seo.restore');
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->withTrashed()->name('services.edit');
        Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'archive'])->name('services.archive');
        Route::post('services/{service}/restore', [ServiceController::class, 'restore'])->withTrashed()->name('services.restore');
        Route::post('services/{service}/publication', [ServiceController::class, 'publication'])->name('services.publication');
        Route::post('services/{service}/revisions/{revision}', [ServiceController::class, 'revision'])->name('services.revision');
        Route::get('services/{service}/seo', [ServiceSeoController::class, 'edit'])->name('services.seo');
        Route::put('services/{service}/seo', [ServiceSeoController::class, 'update'])->name('services.seo.update');
        Route::post('services/{service}/seo/revisions/{revision}', [ServiceSeoController::class, 'restore'])->name('services.seo.restore');
        Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->withTrashed()->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('pages/{page}', [PageController::class, 'archive'])->name('pages.archive');
        Route::post('pages/{page}/restore', [PageController::class, 'restore'])->withTrashed()->name('pages.restore');
        Route::post('pages/{page}/publication', [PageController::class, 'publication'])->name('pages.publication');
        Route::post('pages/{page}/revisions/{revision}', [PageController::class, 'revision'])->name('pages.revision');
        Route::get('pages/{page}/seo', [PageSeoController::class, 'edit'])->name('pages.seo');
        Route::put('pages/{page}/seo', [PageSeoController::class, 'update'])->name('pages.seo.update');
        Route::post('pages/{page}/seo/revisions/{revision}', [PageSeoController::class, 'restore'])->name('pages.seo.restore');
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
