<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;

class LogLockout
{
    public function handle(Lockout $event): void
    {
        $request = $event->request;
        $path = $request->path();

        Log::warning('Blocage temporaire après trop de tentatives', [
            'email' => mb_strtolower((string) $request->input('email')),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $path,
            'is_admin_login' => str_starts_with($path, 'admin'),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}