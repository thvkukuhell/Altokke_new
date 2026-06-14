<?php

use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\ConductorController as ApiConductorController;
use App\Http\Controllers\Api\PasajeroController as ApiPasajeroController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ViajeController;
use Illuminate\Support\Facades\Route;

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
