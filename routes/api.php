<?php

use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\ConductorController as ApiConductorController;
use App\Http\Controllers\Api\InternalViajeController;
use App\Http\Controllers\Api\PasajeroController as ApiPasajeroController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ViajeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::controller(UsuarioController::class)->prefix('usuarios')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show')->whereNumber('id');
        Route::put('/{id}', 'update')->whereNumber('id');
        Route::delete('/{id}', 'destroy')->whereNumber('id');
    });

    Route::controller(ViajeController::class)->prefix('viajes')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show')->whereNumber('id');
        Route::put('/{id}', 'update')->whereNumber('id');
        Route::delete('/{id}', 'destroy')->whereNumber('id');
    });

    Route::controller(ApiConductorController::class)->prefix('conductores')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show')->whereNumber('id');
        Route::put('/{id}', 'update')->whereNumber('id');
        Route::delete('/{id}', 'destroy')->whereNumber('id');
    });

    Route::controller(CalificacionController::class)->prefix('calificaciones')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show')->whereNumber('id');
        Route::delete('/{id}', 'destroy')->whereNumber('id');
    });

    Route::controller(ApiPasajeroController::class)->prefix('pasajeros')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show')->whereNumber('id');
        Route::put('/{id}', 'update')->whereNumber('id');
        Route::delete('/{id}', 'destroy')->whereNumber('id');
        Route::get('/{id}/viajes', 'viajes')->whereNumber('id');
    });

    Route::prefix('internal')->name('api.internal.')->group(function () {
        Route::get('/viajes/{id}', [InternalViajeController::class, 'show'])->whereNumber('id')->middleware('throttle:30,1')->name('viajes.show');
        Route::post('/viajes/{id}/aceptar', [InternalViajeController::class, 'aceptar'])->whereNumber('id')->middleware('throttle:10,1')->name('viajes.aceptar');
        Route::post('/viajes/{id}/ubicacion', [InternalViajeController::class, 'actualizarUbicacion'])->whereNumber('id')->middleware('throttle:12,1')->name('viajes.ubicacion');
        Route::post('/viajes/{id}/completar', [InternalViajeController::class, 'completar'])->whereNumber('id')->middleware('throttle:10,1')->name('viajes.completar');

        Route::get('/conductor/solicitudes', [InternalViajeController::class, 'solicitudesConductor'])->middleware('throttle:20,1')->name('conductor.solicitudes');
        Route::get('/conductor/historial', [InternalViajeController::class, 'historialConductor'])->name('conductor.historial');

        Route::get('/pasajero/viaje-activo', [InternalViajeController::class, 'viajeActivoPasajero'])->middleware('throttle:30,1')->name('pasajero.viajeActivo');
        Route::get('/pasajero/historial', [InternalViajeController::class, 'historialPasajero'])->name('pasajero.historial');
    });
});
