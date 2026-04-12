<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        $request = request();
        $path = $request->path();

        Log::warning('Échec de connexion', [
            'email' => mb_strtolower((string) $request->input('email')),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $path,
            'is_admin_login' => str_starts_with($path, 'admin'),
            'guard' => $event->guard,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}