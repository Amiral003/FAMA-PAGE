<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Routes de fichiers
Route::get('/files/{post}', [FileController::class, 'show'])->name('files.show');

// 2. Gestion du conflit Jetstream / Filament
// On redirige les anciennes routes Jetstream vers l'interface Filament
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/admin');
    })->name('dashboard');
});

// Force le login vers Filament
Route::redirect('/login', '/admin/login')->name('login');

// 3. Route d'accueil (Test ou Front)
Route::get('/', function () {
    return view('front'); // Change en return 'LARAVEL OK'; si tu veux juste le texte
});

// 4. Capture du Front-end (SPA)
// Le "where" empêche cette route d'intercepter les requêtes admin ou livewire
Route::view('/{any}', 'front')
    ->where('any', '^(?!admin|livewire).*$');
