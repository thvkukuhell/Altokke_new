<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // registra cosas ANTES de que arranque la app
        // registrar servicios propios
    }

    public function boot(): void
    {
        // se ejecuta DESPUÉS de que todo está listo
        // forzar HTTPS, configurar Blade, etc.
    }
}
