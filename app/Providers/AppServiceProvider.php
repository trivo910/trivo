<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Mail\SmtpConfigurator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('admin-media-upload', fn (Request $request) => Limit::perMinute(10)->by((string) $request->user()?->getAuthIdentifier()));
        Queue::before(function (): void {
            app(SmtpConfigurator::class)->apply();
        });
        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(5)->by('identity:'.hash('sha256', $request->ip().'|'.strtolower((string) $request->input('email')))),
            Limit::perMinute(30)->by('ip:'.hash('sha256', (string) $request->ip())),
        ]);
        RateLimiter::for('leads', fn (Request $request) => Limit::perMinute(3)
            ->by(hash('sha256', (string) $request->ip())));
        Gate::define('view-styleguide', fn (User $user): bool => ! app()->isProduction() && $user->hasRole('Super Admin'));
        RateLimiter::for('admin-mail-test', fn (Request $request) => Limit::perMinute(2)->by((string) $request->user()?->getAuthIdentifier()));
    }
}
