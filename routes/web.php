<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Routes de fichiers
Route::get('/files/{post}', [FileController::class, 'show'])->name('files.show');

// 2. Gestion du conflit Jetstream / Filament
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/admin');
    })->name('dashboard');
});

// Force le login vers Filament
Route::redirect('/login', '/admin/login')->name('login');

// 3. Route de changement forcé du mot de passe
Route::middleware('auth')->group(function () {
    Route::get('/force-change-password', [ForcePasswordChangeController::class, 'edit'])
        ->name('password.force.change');

    Route::post('/force-change-password', [ForcePasswordChangeController::class, 'update'])
        ->name('password.force.update');
});

// 4. Route d'accueil
Route::get('/', function () {
    return view('front');
});

// 5. Capture du Front-end SPA
Route::view('/{any}', 'front')
    ->where('any', '^(?!admin|livewire|force-change-password).*$');