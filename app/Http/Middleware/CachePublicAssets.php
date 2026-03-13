<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CachePublicAssets
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('storage/*')) {
             $response->headers->set('X-FAMA-CACHE', 'HIT'); // ✅ debug
    $path = $request->path();

    // Cache uniquement images + pdf (optionnel)
    if (preg_match('/\.(jpg|jpeg|png|webp|gif|pdf)$/i', $path)) {
        $response->headers->set('Cache-Control', 'public, max-age=2592000, immutable');
    }
}

        return $response;
    }
}