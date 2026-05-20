<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PasajeroController;
use App\Http\Controllers\ConductorController;

// ── Inicio público (sin middleware — visible para todos) ──
Route::get('/',        [InicioController::class, 'index'])->name('inicio');
Route::get('/inicio',  [InicioController::class, 'index']);
Route::get('/inicio/como_funciona',  [InicioController::class, 'como_funciona'])->name('como_funciona');
Route::get('/inicio/sobre_nosotros', [InicioController::class, 'sobre_nosotros'])->name('sobre_nosotros');

// ── Auth público (redirige si ya está autenticado) ────────
Route::middleware('redirect.auth.role')->group(function () {
    Route::get( '/auth/login',              [AuthController::class, 'login'])->name('login');
    Route::post('/auth/login',              [AuthController::class, 'login_proceso'])->name('login.proceso');
    Route::get( '/auth/eleccion_registro',  [AuthController::class, 'eleccion_registro'])->name('eleccion_registro');
    Route::get( '/auth/registro_pasajero',  [AuthController::class, 'registro_pasajero'])->name('registro_pasajero');
    Route::post('/auth/registro_pasajero',  [AuthController::class, 'proc_regist_pasajero'])->name('proc_regist_pasajero');
    Route::get( '/auth/registro_conductor', [AuthController::class, 'registro_conductor'])->name('registro_conductor');
    Route::post('/auth/registro_conductor', [AuthController::class, 'proc_regist_conductor'])->name('proc_regist_conductor');
});

Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

// ── Pasajero (protegido) ──────────────────────────────────
Route::prefix('pasajero')->name('pasajero.')->middleware(['auth', 'role:pasajero'])->group(function () {
    Route::get('/',                        [PasajeroController::class, 'index']);
    Route::get('/solicitarViaje',          [PasajeroController::class, 'solicitarViaje'])->name('solicitarViaje');
    Route::post('/crearViaje',             [PasajeroController::class, 'crearViaje'])->name('crearViaje');
    Route::get('/buscando/{viajeId}',      [PasajeroController::class, 'buscando'])->name('buscando');
    Route::post('/cancelarViaje',          [PasajeroController::class, 'cancelarViaje'])->name('cancelarViaje');
    Route::post('/expirarViaje',           [PasajeroController::class, 'expirarViaje'])->name('expirarViaje');
    Route::get('/enCurso/{viajeId}',       [PasajeroController::class, 'enCurso'])->name('enCurso');
    Route::get('/calificar/{viajeId}',     [PasajeroController::class, 'calificar'])->name('calificar');
    Route::post('/enviarCalificacion',     [PasajeroController::class, 'enviarCalificacion'])->name('enviarCalificacion');
    Route::get('/historial',               [PasajeroController::class, 'historial'])->name('historial');
    Route::get('/perfil',                  [PasajeroController::class, 'perfil'])->name('perfil');
    Route::get('/editarPerfil',            [PasajeroController::class, 'editarPerfil'])->name('editarPerfil');
    Route::post('/guardarPerfil',          [PasajeroController::class, 'guardarPerfil'])->name('guardarPerfil');
});

// ── Fallbacks pasajero (corregidos a camelCase para evitar 404) ─────
Route::redirect('/pasajero/buscando', '/pasajero/solicitarViaje');
Route::redirect('/pasajero/enCurso',   '/pasajero/solicitarViaje');
Route::redirect('/pasajero/calificar', '/pasajero/solicitarViaje');

// ── Conductor (protegido) ─────────────────────────────────
Route::prefix('conductor')->name('conductor.')->middleware(['auth', 'role:conductor'])->group(function () {
    Route::get('/',                  [ConductorController::class, 'index'])->name('dashboard');
    Route::get('/perfil',            [ConductorController::class, 'perfil'])->name('perfil');
    Route::put('/perfil',            [ConductorController::class, 'actualizarPerfil'])->name('actualizarPerfil');
    Route::get('/solicitudes',       [ConductorController::class, 'solicitudes'])->name('solicitudes');
    Route::post('/aceptarViaje',     [ConductorController::class, 'aceptarViaje'])->name('aceptarViaje');
    Route::post('/completarViaje',   [ConductorController::class, 'completarViaje'])->name('completarViaje');
    Route::post('/cancelarViaje',    [ConductorController::class, 'cancelarViaje'])->name('cancelarViaje');
    Route::get('/viajeActivo',       [ConductorController::class, 'viajeActivo'])->name('viaje_activo');
    Route::get('/historial',         [ConductorController::class, 'historial'])->name('historial');
    Route::get('/billetera',         [ConductorController::class, 'billetera'])->name('billetera');
});

// ── APIs tiempo real (corregido a camelCase) ──────────────────────────────────────
Route::middleware(['auth', 'role:conductor'])->post(
    '/conductor/actualizarUbicacion',
    [ConductorController::class, 'actualizarUbicacion']
)->name('conductor.ubicacion');

Route::middleware(['auth', 'role:pasajero'])->post(
    '/pasajero/actualizarUbicacion',
    [PasajeroController::class, 'actualizarUbicacion']
)->name('pasajero.actualizarUbicacion');