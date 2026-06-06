<?php
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Viaje;
use App\Models\User;
use App\Models\Conductor;
use App\Models\Pasajero;
use App\Models\Calificacion;

// CRUD - USUARIOS
// Listar todos los usuarios
Route::get('/usuarios', function() {
    return response()->json(User::all(),200);
});

// Ver un usuario específico
Route::get('/usuarios/{id}', function ($id) {
    $user = User::find($id);
    if (!$user) {
        return response()->json(['error'=>'Usuario no encontrado'], 404);
    }
    return response()->json($user, 200);
});

// Crear un usuario
Route::post('/usuarios', function (Request $request) {
    $request->validate([
        'nombre_completo' => 'required|string|max:150',
        'email'           => 'required|email|unique:usuarios,email',
        'dni'             => 'required|string|min:6|unique:usuarios,dni',
        'password'        => 'required|string|min:8',
        'tipo_usuario'    => 'required|in:pasajero,conductor',
    ]);
 
    $user = User::create([
        'nombre_completo'  => $request->nombre_completo,
        'apellidos'        => $request->apellidos,
        'dni'              => preg_replace('/\D/', '', $request->dni),
        'email'            => $request->email,
        'telefono'         => $request->telefono,
        'contrasena_hash'  => \Illuminate\Support\Facades\Hash::make($request->password),
        'tipo_usuario'     => $request->tipo_usuario,
        'activo'           => 1,
    ]);
 
    return response()->json($user, 201);
});

// Actualizar un usuario
Route::put('/usuarios/{id}', function (Request $request, $id) {
    $user = User::find($id);
    if (!$user) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
 
    $user->update($request->only([
        'nombre_completo',
        'apellidos',
        'telefono',
    ]));
 
    return response()->json($user, 200);
});

// Eliminar un usuario
Route::delete('/usuarios/{id}', function ($id) {
    $user = User::find($id);
    if (!$user) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
    $user->update(['activo' => 0]);
    return response()->json(['mensaje' => 'Usuario desactivado correctamente'], 200);
});

// CRUD - VIAJES
// Listar todos los viajes
Route::get('/viajes', function () {
    $viajes = Viaje::with(['pasajero.user', 'conductor.user'])->get();
    return response()->json($viajes, 200);
});
 
// Ver un viaje específico
Route::get('/viajes/{id}', function ($id) {
    $viaje = Viaje::with(['pasajero.user', 'conductor.user', 'calificacion'])->find($id);
    if (!$viaje) {
        return response()->json(['error' => 'Viaje no encontrado'], 404);
    }
    return response()->json($viaje, 200);
});
 
// Crear un viaje
Route::post('/viajes', function (Request $request) {
    $request->validate([
        'id_pasajero'   => 'required|integer|exists:pasajeros,id_pasajero',
        'origen_texto'  => 'required|string',
        'destino_texto' => 'required|string|different:origen_texto',
        'tipo_servicio' => 'required|in:normal,express',
        'metodo_pago'   => 'required|in:efectivo,yape,plin',
    ]);
 
    $viaje = Viaje::create([
        'id_pasajero'     => $request->id_pasajero,
        'origen_texto'    => $request->origen_texto,
        'destino_texto'   => $request->destino_texto,
        'tarifa_estimada' => 3.00,
        'tipo_servicio'   => $request->tipo_servicio,
        'metodo_pago'     => $request->metodo_pago,
        'estado_viaje'    => 'buscando',
    ]);
 
    return response()->json($viaje, 201);
});
 
// Actualizar estado de un viaje
Route::put('/viajes/{id}', function (Request $request, $id) {
    $viaje = Viaje::find($id);
    if (!$viaje) {
        return response()->json(['error' => 'Viaje no encontrado'], 404);
    }
 
    $request->validate([
        'estado_viaje' => 'sometimes|in:buscando,aceptado,recogiendo,en_curso,completado,cancelado',
        'id_conductor' => 'sometimes|integer|exists:conductores,id_conductor',
        'tarifa_final'  => 'sometimes|numeric',
    ]);
 
    $viaje->update($request->only([
        'estado_viaje',
        'id_conductor',
        'tarifa_final',
    ]));
 
    return response()->json($viaje, 200);
});
 
// Eliminar un viaje
Route::delete('/viajes/{id}', function ($id) {
    $viaje = Viaje::find($id);
    if (!$viaje) {
        return response()->json(['error' => 'Viaje no encontrado'], 404);
    }
    $viaje->update(['estado_viaje' => 'cancelado']);
    return response()->json(['mensaje' => 'Viaje cancelado correctamente'], 200);
});

// CRUD - CONDUCTORES
// Listar todos los conductores
Route::get('/conductores', function () {
    $conductores = Conductor::with(['user', 'vehiculo'])->get();
    return response()->json($conductores, 200);
});
 
// Ver un conductor específico
Route::get('/conductores/{id}', function ($id) {
    $conductor = Conductor::with(['user', 'vehiculo', 'viajes'])->find($id);
    if (!$conductor) {
        return response()->json(['error' => 'Conductor no encontrado'], 404);
    }
    return response()->json($conductor, 200);
});
 
// Actualizar datos de un conductor
Route::put('/conductores/{id}', function (Request $request, $id) {
    $conductor = Conductor::find($id);
    if (!$conductor) {
        return response()->json(['error' => 'Conductor no encontrado'], 404);
    }
 
    $conductor->update($request->only([
        'estado_conductor',
        'saldo_disponible',
        'calificacion_promedio',
    ]));
 
    return response()->json($conductor, 200);
});
 
// Eliminar un conductor
Route::delete('/conductores/{id}', function ($id) {
    $conductor = Conductor::find($id);
    if (!$conductor) {
        return response()->json(['error' => 'Conductor no encontrado'], 404);
    }
    $conductor->update(['estado_conductor' => 'inactivo']);
    $conductor->user?->update(['activo' => 0]);
    return response()->json(['mensaje' => 'Conductor desactivado correctamente'], 200);
});

// CRUD - CALIFICACIONES
// Listar todas las calificaciones
Route::get('/calificaciones', function () {
    return response()->json(Calificacion::with('viaje')->get(), 200);
});
 
// Ver una calificación específica
Route::get('/calificaciones/{id}', function ($id) {
    $cal = Calificacion::with('viaje')->find($id);
    if (!$cal) {
        return response()->json(['error' => 'Calificación no encontrada'], 404);
    }
    return response()->json($cal, 200);
});
 
// Crear una calificación
Route::post('/calificaciones', function (Request $request) {
    $request->validate([
        'id_viaje'   => 'required|integer|exists:viajes,id_viaje',
        'puntuacion' => 'required|numeric|min:1|max:5',
        'comentario' => 'nullable|string|max:500',
    ]);
 
    $calificacion = Calificacion::updateOrCreate(
        ['id_viaje' => $request->id_viaje],
        [
            'puntuacion' => $request->puntuacion,
            'comentario' => $request->comentario ?? '',
        ]
    );
 
    return response()->json($calificacion, 201);
});
 
// Eliminar una calificación
Route::delete('/calificaciones/{id}', function ($id) {
    $cal = Calificacion::find($id);
    if (!$cal) {
        return response()->json(['error' => 'Calificación no encontrada'], 404);
    }
    $cal->delete();
    return response()->json(['mensaje' => 'Calificación eliminada correctamente'], 200);
});

// CRUD - PASAJEROS 
// Listar todos los pasajeros
Route::get('/pasajeros', function () {
    $pasajeros = Pasajero::with('user')->get();
    return response()->json($pasajeros, 200);
});
 
// Ver un pasajero específico
Route::get('/pasajeros/{id}', function ($id) {
    $pasajero = Pasajero::with(['user', 'viajes'])->find($id);
    if (!$pasajero) {
        return response()->json(['error' => 'Pasajero no encontrado'], 404);
    }
    return response()->json($pasajero, 200);
});
 
// Crear un pasajero (vinculado a un usuario existente)
Route::post('/pasajeros', function (Request $request) {
    $request->validate([
        'id_pasajero'          => 'required|integer|exists:usuarios,id_usuario|unique:pasajeros,id_pasajero',
        'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
    ]);
 
    $pasajero = Pasajero::create([
        'id_pasajero'          => $request->id_pasajero,
        'metodo_pago_preferido' => $request->metodo_pago_preferido,
    ]);
 
    return response()->json($pasajero->load('user'), 201);
});
 
// Actualizar método de pago preferido
Route::put('/pasajeros/{id}', function (Request $request, $id) {
    $pasajero = Pasajero::find($id);
    if (!$pasajero) {
        return response()->json(['error' => 'Pasajero no encontrado'], 404);
    }
 
    $request->validate([
        'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
    ]);
 
    $pasajero->update([
        'metodo_pago_preferido' => $request->metodo_pago_preferido,
    ]);
 
    return response()->json($pasajero->load('user'), 200);
});
 
// Eliminar un pasajero
Route::delete('/pasajeros/{id}', function ($id) {
    $pasajero = Pasajero::find($id);
    if (!$pasajero) {
        return response()->json(['error' => 'Pasajero no encontrado'], 404);
    }
    $pasajero->user?->update(['activo' => 0]);
    Viaje::where('id_pasajero', $id)
        ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
        ->update(['estado_viaje' => 'cancelado']);

    return response()->json(['mensaje' => 'Pasajero desactivado correctamente'], 200);
});
 
// Ver historial de viajes de un pasajero
Route::get('/pasajeros/{id}/viajes', function ($id) {
    $pasajero = Pasajero::find($id);
    if (!$pasajero) {
        return response()->json(['error' => 'Pasajero no encontrado'], 404);
    }
 
    $viajes = Viaje::where('id_pasajero', $id)
        ->with(['conductor.user', 'calificacion'])
        ->orderByDesc('fecha_solicitud')
        ->get();
 
    return response()->json($viajes, 200);
});
 
