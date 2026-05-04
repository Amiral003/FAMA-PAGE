<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if (
            $request->routeIs('password.force.change')
            || $request->routeIs('password.force.update')
            || $request->routeIs('filament.admin.auth.logout')
            || $request->is('livewire/*')
        ) {
            return $next($request);
        }

        return redirect()->route('password.force.change');
    }
}