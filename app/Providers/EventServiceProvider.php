<?php

namespace App\Providers;

use App\Listeners\LogSecurityEvent;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogSecurityEvent::class,
        ],

        Failed::class => [
            LogSecurityEvent::class,
        ],

        Lockout::class => [
            LogSecurityEvent::class,
        ],

        PasswordReset::class => [
            LogSecurityEvent::class,
        ],
    ];
}