<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PublicStaffController;
use App\Http\Controllers\Api\PublicContactController;



/*
|--------------------------------------------------------------------------
| API Routes (Public)
|--------------------------------------------------------------------------
| - On expose UNIQUEMENT les posts publics (status = publie)
| - Pagination pour fil infini (page, per_page)
| - Recherche côté serveur via ?q=
|
| Exemples :
|   GET /api/posts?page=1&per_page=9
|   GET /api/posts?q=armée
|   GET /api/posts/latest
|   GET /api/posts/mon-slug
*/
Route::middleware('throttle:api-public')->group(function () {
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/flashes', [PostController::class, 'flashes']);
        Route::get('/latest', [PostController::class, 'latest']);
        Route::get('/photos', [PostController::class, 'photos']);
        Route::get('/videos', [PostController::class, 'videos']);
        Route::get('/{slug}', [PostController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+');
    });

    Route::get('/public/staffs', [PublicStaffController::class, 'index']);
    Route::get('/public/staffs/{slug}', [PublicStaffController::class, 'show']);
});

// Auth séparé
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'throttle:60,1']);

Route::post('/public/contact', PublicContactController::class)
    ->middleware('throttle:contact');