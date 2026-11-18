<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        // The collection form is public, so keep one visitor from flooding the inbox.
        RateLimiter::for('collect', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perHour(20)->by($request->ip()),
        ]);
    }
}
