<?php

namespace App\Listeners;

use App\Models\SecurityEvent;
use App\Models\SecurityLockout;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;

class LogSecurityEvent
{
    public function __construct(
        protected SecurityLogger $logger
    ) {}

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof Login => $this->loginSuccess($event),
            $event instanceof Failed => $this->loginFailed($event),
            $event instanceof Lockout => $this->lockout($event),
            $event instanceof PasswordReset => $this->passwordReset($event),
            default => null,
        };
    }

    protected function loginSuccess(Login $event): void
    {
        $this->logger->log(
            eventType: 'login_success',
            user: $event->user,
            severity: 'info',
            metadata: [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ],
        );
    }

    protected function loginFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        $user = $event->user instanceof User
            ? $event->user
            : User::where('email', $email)->first();

        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        $this->logger->log(
            eventType: 'login_failed',
            user: $user,
            email: $email,
            severity: $isSuperAdmin ? 'critical' : 'warning',
            metadata: [
                'guard' => $event->guard,
                'target_role' => $isSuperAdmin ? 'super-admin' : null,
            ],
        );

        $this->detectBruteForce($email, $user, $isSuperAdmin);
    }

    protected function lockout(Lockout $event): void
    {
        $this->logger->log(
            eventType: 'account_locked',
            email: $event->request->input('email'),
            severity: 'danger',
            metadata: [
                'reason' => 'too_many_login_attempts',
            ],
        );
    }

    protected function passwordReset(PasswordReset $event): void
    {
        $this->logger->log(
            eventType: 'password_reset_success',
            user: $event->user,
            severity: 'warning',
        );
    }

    protected function detectBruteForce(?string $email, ?User $user, bool $isSuperAdmin): void
{
    if (! $email) {
        return;
    }

    $email = strtolower(trim($email));
    $ip = request()->ip();

    $failedCount = SecurityEvent::query()
        ->where('event_type', 'login_failed')
        ->where('email', $email)
        ->where('ip_address', $ip)
        ->where('created_at', '>=', now()->subMinutes(10))
        ->count();

    if ($failedCount < 5) {
        return;
    }

    $lockMinutes = $isSuperAdmin ? 60 : 15;

    SecurityLockout::updateOrCreate(
        [
            'email' => $email,
            'ip_address' => $ip,
        ],
        [
            'reason' => 'brute_force',
            'severity' => $isSuperAdmin ? 'critical' : 'danger',
            'locked_until' => now()->addMinutes($lockMinutes),
            'metadata' => [
                'failed_attempts' => $failedCount,
                'window_minutes' => 10,
                'target_role' => $isSuperAdmin ? 'super-admin' : null,
                'lock_minutes' => $lockMinutes,
            ],
        ]
    );

    $alreadyLogged = SecurityEvent::query()
        ->where('event_type', 'account_locked')
        ->where('email', $email)
        ->where('ip_address', $ip)
        ->where('created_at', '>=', now()->subMinutes(10))
        ->exists();

    if (! $alreadyLogged) {
        $this->logger->log(
            eventType: 'account_locked',
            user: $user,
            email: $email,
            severity: $isSuperAdmin ? 'critical' : 'danger',
            metadata: [
                'reason' => 'brute_force_detected',
                'failed_attempts' => $failedCount,
                'window_minutes' => 10,
                'target_role' => $isSuperAdmin ? 'super-admin' : null,
            ],
        );
    }
}
}