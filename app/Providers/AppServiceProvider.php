<?php

namespace App\Providers;

use App\Models\Viaje;
use App\Policies\ViajePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Viaje::class, ViajePolicy::class);

        View::composer('layouts.header_conductor', function ($view) {
            $tieneViajeActivo = Auth::check()
                && Viaje::where('id_conductor', Auth::id())
                    ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                    ->exists();

            $view->with('tieneViajeActivo', $tieneViajeActivo);
        });
        
        //
        if (config('app.env') !== 'local' || str_contains(config('app.url'), 'ngrok')) {
            URL::forceScheme('https');
        }
    }
}
