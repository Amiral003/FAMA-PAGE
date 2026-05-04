<?php

namespace App\Services;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityLogger
{
    public function log(
        string $eventType,
        ?User $user = null,
        ?string $email = null,
        string $severity = 'info',
        array $metadata = []
    ): void {
        $request = request();

        SecurityEvent::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'email' => $email ?? $user?->email,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? $request->userAgent() : null,
            'severity' => $severity,
            'metadata' => $metadata ?: null,
        ]);
    }
}