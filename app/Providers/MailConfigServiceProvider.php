<?php

namespace App\Providers;

use App\Services\Mail\SmtpConfigurator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // config:cache boots a fresh console application: never serialize decrypted DB secrets.
        // Queue jobs refresh configuration through the JobProcessing listener instead.
        if ($this->app->runningInConsole()) {
            return;
        }
        try {
            if (Schema::hasTable('settings')) {
                app(SmtpConfigurator::class)->apply();
            }
        } catch (QueryException) {
            // Installation/migration may run before a database or table exists.
        }
    }
}
