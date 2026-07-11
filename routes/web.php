<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MapaRutaController;
use App\Http\Controllers\PasajeroController;
use App\Http\Controllers\PerfilArchivoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

// Publico
Route::get('/', [InicioController::class, 'index'])->name('inicio');
Route::get('/inicio', [InicioController::class, 'index']);
Route::get('/inicio/como_funciona', [InicioController::class, 'como_funciona'])->name('como_funciona');
Route::get('/inicio/sobre_nosotros', [InicioController::class, 'sobre_nosotros'])->name('sobre_nosotros');
Route::get('/inicio/servicios', [InicioController::class, 'servicios'])->name('servicios');
Route::get('/inicio/contacto', [InicioController::class, 'contacto'])->name('contacto');
Route::get('/inicio/ayuda', [InicioController::class, 'ayuda'])->name('ayuda');
Route::post('/inicio/ayuda', [InicioController::class, 'enviarConsulta'])->name('ayuda.enviar');

// Autenticacion publica
Route::middleware('redirect.auth.role')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login_proceso'])->name('login.proceso');
    Route::get('/auth/eleccion_registro', [AuthController::class, 'eleccion_registro'])->name('eleccion_registro');
    Route::get('/auth/registro_pasajero', [AuthController::class, 'registro_pasajero'])->name('registro_pasajero');
    Route::post('/auth/registro_pasajero', [AuthController::class, 'proc_regist_pasajero'])->name('proc_regist_pasajero');
    Route::get('/auth/registro_conductor', [AuthController::class, 'registro_conductor'])->name('registro_conductor');
    Route::post('/auth/registro_conductor', [AuthController::class, 'proc_regist_conductor'])->name('proc_regist_conductor');
    Route::get('/auth/recuperar_contrasena', [AuthController::class, 'recuperar_contrasena'])->name('recuperar_contrasena');
    Route::post('/auth/recuperar_contrasena', [AuthController::class, 'recuperar_contrasena_proceso'])->name('recuperar_contrasena.proceso');
});

Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/mapa/ruta-estimada', [MapaRutaController::class, 'rutaEstimada'])->middleware(['auth', 'throttle:30,1,ruta-estimada'])->name('mapa.rutaEstimada');
Route::post('/perfil/foto', [PerfilArchivoController::class, 'actualizarFoto'])->middleware(['auth', 'throttle:10,1,perfil-foto'])->name('perfil.foto');
Route::get('/reportes/viajes/{viajeId}/comprobante', [ReporteController::class, 'comprobanteViajePdf'])->whereNumber('viajeId')->middleware('auth')->name('reportes.viajes.comprobante');

// Pasajero
// Seguridad de Endpoints
Route::prefix('pasajero')->name('pasajero.')->middleware(['auth', 'role:pasajero', 'share.pasajero.viaje'])->group(function () {
    Route::get('/', [PasajeroController::class, 'index']);
    Route::get('/solicitarViaje', [PasajeroController::class, 'solicitarViaje'])->name('solicitarViaje');
    Route::post('/crearViaje', [PasajeroController::class, 'crearViaje'])->name('crearViaje');
    Route::get('/buscando/{viajeId}', [PasajeroController::class, 'buscando'])->whereNumber('viajeId')->name('buscando');
    Route::post('/cancelarViaje', [PasajeroController::class, 'cancelarViaje'])->middleware('throttle:10,1,pasajero-cancelar')->name('cancelarViaje');
    Route::post('/expirarViaje', [PasajeroController::class, 'expirarViaje'])->middleware('throttle:10,1,pasajero-expirar')->name('expirarViaje');
    Route::get('/estadoViaje/{viajeId}', [PasajeroController::class, 'estadoViajeJson'])->whereNumber('viajeId')->middleware('throttle:30,1,pasajero-estado')->name('estadoViaje.json');
    Route::get('/enCurso/{viajeId}', [PasajeroController::class, 'enCurso'])->whereNumber('viajeId')->name('enCurso');
    Route::get('/calificar/{viajeId}', [PasajeroController::class, 'calificar'])->whereNumber('viajeId')->name('calificar');
    Route::post('/enviarCalificacion', [PasajeroController::class, 'enviarCalificacion'])->name('enviarCalificacion');
    Route::get('/historial', [PasajeroController::class, 'historial'])->name('historial');
    Route::get('/historial/csv', [ReporteController::class, 'pasajeroHistorialCsv'])->name('historial.csv');
    Route::get('/perfil', [PasajeroController::class, 'perfil'])->name('perfil');
    Route::get('/editarPerfil', [PasajeroController::class, 'editarPerfil'])->name('editarPerfil');
    Route::post('/guardarPerfil', [PasajeroController::class, 'guardarPerfil'])->name('guardarPerfil');
    Route::post('/actualizarUbicacion', [PasajeroController::class, 'actualizarUbicacion'])->name('actualizarUbicacion');
});

// Fallbacks pasajero
Route::redirect('/pasajero/buscando', '/pasajero/solicitarViaje');
Route::redirect('/pasajero/enCurso', '/pasajero/solicitarViaje');
Route::redirect('/pasajero/calificar', '/pasajero/solicitarViaje');

// Conductor
// Seguridad de Endpoints
Route::prefix('conductor')->name('conductor.')->middleware(['auth', 'role:conductor'])->group(function () {
    Route::get('/', [ConductorController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [ConductorController::class, 'perfil'])->name('perfil');
    Route::put('/perfil', [ConductorController::class, 'actualizarPerfil'])->name('actualizarPerfil');
    Route::get('/solicitudes', [ConductorController::class, 'solicitudes'])->name('solicitudes');
    Route::get('/solicitudes/json', [ConductorController::class, 'solicitudesJson'])->middleware('throttle:20,1,conductor-solicitudes')->name('solicitudes.json');
    Route::post('/aceptarViaje', [ConductorController::class, 'aceptarViaje'])->middleware('throttle:10,1,conductor-aceptar')->name('aceptarViaje');
    Route::post('/recogerPasajero', [ConductorController::class, 'recogerPasajero'])->middleware('throttle:10,1,conductor-recoger')->name('recogerPasajero');
    Route::post('/iniciarTrayecto', [ConductorController::class, 'iniciarTrayecto'])->middleware('throttle:10,1,conductor-iniciar')->name('iniciarTrayecto');
    Route::post('/completarViaje', [ConductorController::class, 'completarViaje'])->middleware('throttle:10,1,conductor-completar')->name('completarViaje');
    Route::post('/cancelarViaje', [ConductorController::class, 'cancelarViaje'])->middleware('throttle:10,1,conductor-cancelar')->name('cancelarViaje');
    Route::get('/viajeActivo', [ConductorController::class, 'viajeActivo'])->middleware('throttle:30,1,conductor-viaje-activo')->name('viaje_activo');
    Route::get('/historial', [ConductorController::class, 'historial'])->name('historial');
    Route::get('/historial/csv', [ReporteController::class, 'conductorHistorialCsv'])->name('historial.csv');
    Route::get('/billetera', [ConductorController::class, 'billetera'])->name('billetera');
    Route::post('/recargarSaldo', [ConductorController::class, 'recargarSaldo'])->name('recargarSaldo');
    Route::post('/actualizarUbicacion', [ConductorController::class, 'actualizarUbicacion'])->middleware('throttle:12,1,conductor-ubicacion')->name('ubicacion');
});
