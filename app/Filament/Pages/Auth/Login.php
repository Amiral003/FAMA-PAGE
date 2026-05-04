<?php

namespace App\Filament\Pages\Auth;

use App\Models\SecurityLockout;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        $data = $this->form->getState();

        $email = Str::lower(trim($data['email'] ?? ''));
        $ip = request()->ip();

        $lockout = SecurityLockout::query()
            ->where('email', $email)
            ->where('ip_address', $ip)
            ->where('locked_until', '>', now())
            ->first();

        if ($lockout) {
            $seconds = now()->diffInSeconds($lockout->locked_until, false);
            $minutes = max(1, (int) ceil($seconds / 60));

            throw ValidationException::withMessages([
                'data.email' => "Connexion temporairement bloquée pour raisons de sécurité. Réessayez dans environ {$minutes} minute(s).",
            ]);
        }

        return parent::authenticate();
    }
}