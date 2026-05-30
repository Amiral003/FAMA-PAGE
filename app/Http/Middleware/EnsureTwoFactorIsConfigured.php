<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if (! $request->is('admin') && ! $request->is('admin/*')) {
            return $next($request);
        }

        if ($user->two_factor_confirmed_at) {
            return $next($request);
        }

        return redirect()->route('two-factor.setup')
            ->with('warning', 'Vous devez configurer l’authentification à deux facteurs avant d’accéder à l’administration.');
    }
}