<?php

namespace App\Http\Middleware;

use App\Models\Viaje;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SharePasajeroViajeActivo
{
    public function handle(Request $request, Closure $next)
    {
        // esto es de Persistencia del viaje activo en la navegacion
        $viajeActivo = Viaje::where('id_pasajero', Auth::id())
            ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
            ->orderByDesc('id_viaje')
            ->first();

        View::share('viajeActivoPasajero', $viajeActivo);
        $request->attributes->set('viajeActivoPasajero', $viajeActivo);

        return $next($request);
    }
}
