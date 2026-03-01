<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;

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

Route::prefix('posts')->group(function () {
    // ✅ Fil d’actualité paginé + recherche
    Route::get('/', [PostController::class, 'index']);

      // ✅ Flashs (bandeau)
    Route::get('/flashes', [PostController::class, 'flashes']);

    // ✅ 9 derniers posts publics (Home)
    Route::get('/latest', [PostController::class, 'latest']);

    // Route::get('/posts/videos', [\App\Http\Controllers\Api\PostController::class, 'videos']);

    Route::get('/photos', [PostController::class, 'photos']);
    Route::get('/videos', [PostController::class, 'videos']);
    // ✅ Détail d’un post public par slug (SPA)
    Route::get('/{slug}', [PostController::class, 'show'])
        // slug: lettres/chiffres/tirets, évite de matcher n’importe quoi
        ->where('slug', '[A-Za-z0-9\-]+');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');