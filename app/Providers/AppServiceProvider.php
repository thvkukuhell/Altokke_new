<?php

namespace App\Providers;

use App\Models\Viaje;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.header_pasajero', function ($view) {
            $viajeActivo = null;

            if (Auth::check()) {
                Viaje::where('id_pasajero', Auth::id())
                    ->where('estado_viaje', 'buscando')
                    ->where('created_at', '<', now()->subMinutes(3))
                    ->update(['estado_viaje' => 'expirado']);

                $viajeActivo = Viaje::where('id_pasajero', Auth::id())
                    ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
                    ->latest('id_viaje')
                    ->first();
            }

            $view->with('viajeActivo', $viajeActivo);
        });

        View::composer('layouts.header_conductor', function ($view) {
            $tieneViajeActivo = Auth::check()
                && Viaje::where('id_conductor', Auth::id())
                    ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                    ->exists();

            $view->with('tieneViajeActivo', $tieneViajeActivo);
        });
    }
}
