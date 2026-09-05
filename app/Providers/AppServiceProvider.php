<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Di produksi, semua URL dipaksa HTTPS (aman dari downgrade/downgrade attack).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
