<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Models\Post;

Route::get('/posts', function () {
    return Post::with('media','user')->get();
});
Route::get('/posts/{slug}', function ($slug) {
    return Post::where('slug', $slug)
        ->where('status', Post::STATUS_PUBLIE) // Sécurité : n'affiche que le public
        ->with(['media', 'user']) // Charge les images et l'auteur
        ->firstOrFail();
});

Route::get('/posts/latest', [PostController::class, 'latest']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
