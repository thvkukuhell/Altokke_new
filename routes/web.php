<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PasajeroController;
use App\Http\Controllers\ConductorController;

/*
| RUTAS PÚBLICAS
*/

Route::middleware('redirect.auth.role')->group(function () {

    Route::get('/', [InicioController::class, 'index'])->name('inicio');
    Route::get('/inicio', [InicioController::class, 'index']);

    Route::get('/auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login_proceso'])->name('login.proceso');

    Route::get('/auth/eleccion_registro', [AuthController::class, 'eleccion_registro'])->name('eleccion_registro');

    Route::get('/auth/registro_pasajero', [AuthController::class, 'registro_pasajero'])->name('registro_pasajero');
    Route::post('/auth/registro_pasajero', [AuthController::class, 'proc_regist_pasajero'])->name('proc_regist_pasajero');

    Route::get('/auth/registro_conductor', [AuthController::class, 'registro_conductor'])->name('registro_conductor');
    Route::post('/auth/registro_conductor', [AuthController::class, 'proc_regist_conductor'])->name('proc_regist_conductor');
});

/*
| AUTH LOGOUT
*/

Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

/*
| PASAJERO (PROTEGIDO)
*/

Route::prefix('pasajero')
    ->name('pasajero.')
    ->middleware(['auth', 'role:pasajero'])
    ->group(function () {

        Route::get('/', [PasajeroController::class, 'index']);

        Route::get('/solicitar-viaje', [PasajeroController::class, 'solicitarViaje'])->name('solicitarViaje');

        Route::post('/crear-viaje', [PasajeroController::class, 'crearViaje'])->name('crearViaje');

        Route::get('/buscando/{viajeId}', [PasajeroController::class, 'buscando'])->name('buscando');

        Route::post('/cancelar-viaje', [PasajeroController::class, 'cancelarViaje'])->name('cancelarViaje');

        Route::get('/en-curso/{viajeId}', [PasajeroController::class, 'enCurso'])->name('enCurso');

        Route::get('/calificar/{viajeId}', [PasajeroController::class, 'calificar'])->name('calificar');

        Route::post('/enviar-calificacion', [PasajeroController::class, 'enviarCalificacion'])->name('enviarCalificacion');

        Route::get('/historial', [PasajeroController::class, 'historial'])->name('historial');

        Route::get('/perfil', [PasajeroController::class, 'perfil'])->name('perfil');

        Route::get('/editar-perfil', [PasajeroController::class, 'editarPerfil'])->name('editarPerfil');

        Route::post('/guardar-perfil', [PasajeroController::class, 'guardarPerfil'])->name('guardarPerfil');
    });

/*
| FALLBACK PASAJERO (SEGURIDAD UX)
*/

Route::redirect('/pasajero/buscando', '/pasajero/solicitar-viaje');
Route::redirect('/pasajero/enCurso', '/pasajero/solicitar-viaje');
Route::redirect('/pasajero/calificar', '/pasajero/solicitar-viaje');

/*
| CONDUCTOR (PROTEGIDO)
*/

Route::prefix('conductor')
    ->name('conductor.')
    ->middleware(['auth', 'role:conductor'])
    ->group(function () {

        Route::get('/', [ConductorController::class, 'index'])->name('dashboard');

        Route::get('/perfil', [ConductorController::class, 'perfil'])->name('perfil');
        Route::put('/perfil', [ConductorController::class, 'actualizarPerfil'])->name('actualizarPerfil');

        Route::get('/solicitudes', [ConductorController::class, 'solicitudes'])->name('solicitudes');

        Route::post('/aceptar-viaje', [ConductorController::class, 'aceptarViaje'])->name('aceptarViaje');
        Route::post('/completar-viaje', [ConductorController::class, 'completarViaje'])->name('completarViaje');
        Route::post('/cancelar-viaje', [ConductorController::class, 'cancelarViaje'])->name('cancelarViaje');

        Route::get('/viaje-activo', [ConductorController::class, 'viajeActivo'])->name('viaje_activo');

        Route::get('/historial', [ConductorController::class, 'historial'])->name('historial');

        Route::get('/billetera', [ConductorController::class, 'billetera'])->name('billetera');
    });

/*
 APIs / UBICACIÓN EN TIEMPO REAL
*/

Route::middleware(['auth', 'role:conductor'])->post(
    '/conductor/ubicacion',
    [ConductorController::class, 'actualizarUbicacion']
);

Route::middleware(['auth', 'role:pasajero'])->post(
    '/pasajero/actualizar-ubicacion',
    [PasajeroController::class, 'actualizarUbicacion']
)->name('pasajero.actualizarUbicacion');