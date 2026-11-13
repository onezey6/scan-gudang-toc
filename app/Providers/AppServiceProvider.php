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
        // Paksa skema HTTPS jika diakses melalui reverse proxy / tunnel SSL (Pinggy, Cloudflare, dll)
        if (request()->header('x-forwarded-proto') === 'https' || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}
