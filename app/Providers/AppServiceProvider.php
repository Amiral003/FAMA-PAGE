<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\FileUpload;

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
{
    FileUpload::configureUsing(function (FileUpload $component) {
        $component->maxSize(10240); // Autorise jusqu'à 10MB
    });
}
    }
}
