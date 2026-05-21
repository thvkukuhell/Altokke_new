<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedRole
{
    public function handle(Request $request, Closure $next)
{
    if (Auth::check()) {

        $user = Auth::user();

        return match ($user->tipo_usuario) {
            'conductor' => redirect()->route('conductor.dashboard'),
            'pasajero'  => redirect()->route('pasajero.solicitarViaje'),
            default     => redirect()->route('inicio'),
        };
    }

    return $next($request);
}
}