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

            if ($user->tipo_usuario === 'conductor') {
                return redirect()->route('conductor.dashboard');
            }

            if ($user->tipo_usuario === 'pasajero') {
                return redirect()->route('pasajero.solicitarViaje');
            }

            return redirect()->route('inicio');
        }

        return $next($request);
    }
}