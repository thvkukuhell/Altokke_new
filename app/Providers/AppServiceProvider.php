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
        View::composer('layouts.header_conductor', function ($view) {
            $tieneViajeActivo = Auth::check()
                && Viaje::where('id_conductor', Auth::id())
                    ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                    ->exists();

            $view->with('tieneViajeActivo', $tieneViajeActivo);
        });
    }
}
