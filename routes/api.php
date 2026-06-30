<?php

use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\ConductorController as ApiConductorController;
use App\Http\Controllers\Api\InternalViajeController;
use App\Http\Controllers\Api\PasajeroController as ApiPasajeroController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ViajeController;
use Illuminate\Support\Facades\Route;

// esto es de Refactorizar Api a ApiController y Validacion BOLA IDOR
// Se usa la sesion web que el proyecto ya tiene, sin instalar Sanctum.
Route::middleware(['web', 'auth'])->group(function () {
    Route::controller(UsuarioController::class)->prefix('usuarios')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::controller(ViajeController::class)->prefix('viajes')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::controller(ApiConductorController::class)->prefix('conductores')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::controller(CalificacionController::class)->prefix('calificaciones')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::delete('/{id}', 'destroy');
    });

    Route::controller(ApiPasajeroController::class)->prefix('pasajeros')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::get('/{id}/viajes', 'viajes');
    });

    Route::prefix('internal')->name('api.internal.')->group(function () {
        Route::get('/viajes/{id}', [InternalViajeController::class, 'show'])->name('viajes.show');
        Route::post('/viajes/{id}/aceptar', [InternalViajeController::class, 'aceptar'])->name('viajes.aceptar');
        Route::post('/viajes/{id}/ubicacion', [InternalViajeController::class, 'actualizarUbicacion'])->name('viajes.ubicacion');
        Route::post('/viajes/{id}/completar', [InternalViajeController::class, 'completar'])->name('viajes.completar');

        Route::get('/conductor/solicitudes', [InternalViajeController::class, 'solicitudesConductor'])->name('conductor.solicitudes');
        Route::get('/conductor/historial', [InternalViajeController::class, 'historialConductor'])->name('conductor.historial');

        Route::get('/pasajero/viaje-activo', [InternalViajeController::class, 'viajeActivoPasajero'])->name('pasajero.viajeActivo');
        Route::get('/pasajero/historial', [InternalViajeController::class, 'historialPasajero'])->name('pasajero.historial');
    });
});