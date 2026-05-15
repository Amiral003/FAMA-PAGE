<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginData;
use Filament\Forms\Components\FileUpload;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LogFailedLogin;
use Illuminate\Auth\Events\Lockout;
use App\Listeners\LogLockout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        DB::whenQueryingForLongerThan(300, function ($connection, $event) {
    Log::warning('Slow SQL Query Detected', [
        'connection' => $connection->getName(),
        'sql' => $event->sql,
        'bindings' => $event->bindings,
        'time_ms' => $event->time,
    ]);
});
        Event::listen(Login::class, UpdateLastLoginData::class);
        Event::listen(Failed::class, LogFailedLogin::class);
        Event::listen(Lockout::class, LogLockout::class);

        if (app()->environment('local')) {
            \Illuminate\Database\Eloquent\Model::preventLazyLoading();
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        FileUpload::configureUsing(function (FileUpload $component) {
            $component->maxSize(10240); // 10 MB
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by(mb_strtolower($email) . '|' . $request->ip()),
                Limit::perMinute(20)->by('login-ip|' . $request->ip()),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return [
                Limit::perMinute(5)->by((string) $request->session()->get('login.id') . '|' . $request->ip()),
                Limit::perMinute(20)->by('two-factor-ip|' . $request->ip()),
            ];
        });

        RateLimiter::for('api-public', function (Request $request) {
            return [
                Limit::perMinute(60)->by('api-public|' . $request->ip()),
            ];
        });

        RateLimiter::for('contact', function (Request $request) {
            return [
                Limit::perMinute(3)->by('contact-minute|' . $request->ip()),
                Limit::perHour(10)->by('contact-hour|' . $request->ip()),
            ];
        });

        RateLimiter::for('admin', function (Request $request) {
            return [
                Limit::perMinute(30)->by('admin|' . $request->ip()),
            ];
        });

        RateLimiter::for('files', function (Request $request) {
    return [
        Limit::perMinute(30)->by('files|' . $request->ip()),
    ];
});


    }
}
