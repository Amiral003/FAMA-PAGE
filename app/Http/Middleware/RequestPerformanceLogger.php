<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestPerformanceLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        // log seulement les requêtes lentes
        if ($duration > 500) {

            Log::warning('Slow Request Detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration_ms' => $duration,
                'status_code' => $response->getStatusCode(),
                'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}