<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EsConductor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->tipo_usuario !== 'conductor') {
            return redirect()->route('inicio');
        }
        return $next($request);
    }
}