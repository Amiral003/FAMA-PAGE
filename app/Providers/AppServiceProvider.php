<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\URL;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
         // test temporaire
        if (app()->environment('local')) {
    \Illuminate\Database\Eloquent\Model::preventLazyLoading();
}
        // 1) Force HTTPS uniquement en production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // 2) Configuration Filament
        FileUpload::configureUsing(function (FileUpload $component) {
            $component->maxSize(10240); // 10MB
        });

        // 3) Rate limiting API publique
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // 4) Rate limiting contact
        RateLimiter::for('contact', function (Request $request) {
            $email = strtolower((string) $request->input('email'));
            $key = $email !== '' ? $email : $request->ip();

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(20)->by($key),
            ];
        });
// throttle sur tout le panel admin
        RateLimiter::for('admin', function (Request $request) {
    return Limit::perMinute(30)->by($request->ip());
});
    }
}