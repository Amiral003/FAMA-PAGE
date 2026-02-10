<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\URL;

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
        // 1. Force le HTTPS pour corriger le "Mixed Content"
        URL::forceScheme('https');

        // 2. Configuration Filament
        FileUpload::configureUsing(function (FileUpload $component) {
            $component->maxSize(10240); // 10MB
        });
    }
}
