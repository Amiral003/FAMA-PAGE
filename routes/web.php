<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FileController;

Route::get('/', [PostController:: class, 'index']);
Route::get('/posts/{post}',[PostController::class, 'show']);
Route::get('/files/{post}',  [FileController:: class, 'show'])->name('files.show');



// Route::get('/', function () {
//     return view('home');
// });

// Route::get('/', function () {
//     return view('front.home');
// });

// Route::get('/', function () {
//     return view('front.about');
// });

//     Route::get('/', function () {
//         return view('front.contact');
//     });
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
