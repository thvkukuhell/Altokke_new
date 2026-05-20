<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, $role)
{
    if (!Auth::check()) {
        return redirect()->route('inicio');
    }

    if (Auth::user()->tipo_usuario !== $role) {
        abort(403, 'No autorizado');
    }

    return $next($request);
}
}