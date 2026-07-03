<?php

namespace App\Http\Controllers;

use App\Services\ViajeService;
use App\Models\Viaje;
use App\Models\Pasajero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasajeroController extends Controller
{
    public function __construct(private ViajeService $viajeService) {}

    public function index()
    {
        return redirect()->route('pasajero.solicitarViaje');
    }

    public function solicitarViaje(Request $request)
    {
        $redireccion = $this->redirigirSiTieneViajeActivo($request);
        if ($redireccion) {
            return $redireccion;
        }

        return view('pasajero.solicitar_viaje', [
            'header' => 'header_pasajero',
            'footer' => 'footer',
            'css'    => ['pasajero/pasajero.css', 'pasajero/solicitar_viaje.css'],
        ]);
    }

    public function crearViaje(Request $request)
    {
        // esto es de Evitar solicitudes duplicadas
        $redireccion = $this->redirigirSiTieneViajeActivo($request);
        if ($redireccion) {
            return $redireccion;
        }

        $request->merge([
            'origen' => trim((string) $request->input('origen', '')),
            'destino' => trim((string) $request->input('destino', '')),
        ]);

        // Validación de entrada (saneamiento contra datos maliciosos)
        $datos = $request->validate([
            'origen'        => ['required', 'string', 'min:2', 'max:300', 'not_regex:/^(nan|null|undefined)$/i'],
            'destino'       => ['required', 'string', 'different:origen', 'min:2', 'max:300', 'not_regex:/^(nan|null|undefined)$/i'],
            'tipo_servicio' => 'required|in:normal,express',
            'metodo_pago'   => 'required|in:efectivo,yape,plin',
            'origen_lat'    => 'required|numeric|between:-90,90',
            'origen_lng'    => 'required|numeric|between:-180,180',
            'destino_lat'   => 'required|numeric|between:-90,90',
            'destino_lng'   => 'required|numeric|between:-180,180',
            'distancia_km'  => 'nullable|numeric|min:0|max:500',
            'tiempo_min'    => 'nullable|integer|min:0',
        ], [
            'destino.different' => 'El origen y destino no pueden ser iguales.',
            'origen_lat.required' => 'Marca tu origen en el mapa.',
            'origen_lng.required' => 'Marca tu origen en el mapa.',
            'destino_lat.required' => 'Marca tu destino en el mapa.',
            'destino_lng.required' => 'Marca tu destino en el mapa.',
            'origen.not_regex' => 'El origen no es valido.',
            'destino.not_regex' => 'El destino no es valido.',
        ]);

        $datos['origen_lat'] = (float) $datos['origen_lat'];
        $datos['origen_lng'] = (float) $datos['origen_lng'];
        $datos['destino_lat'] = (float) $datos['destino_lat'];
        $datos['destino_lng'] = (float) $datos['destino_lng'];

        $distanciaSeleccionada = $this->distanciaKm(
            (float) $datos['origen_lat'],
            (float) $datos['origen_lng'],
            (float) $datos['destino_lat'],
            (float) $datos['destino_lng']
        );

        if ($distanciaSeleccionada < 0.025) {
            return back()
                ->withInput()
                ->withErrors(['destino' => 'El origen y destino deben estar en puntos diferentes del mapa.']);
        }
 
        // Toda la lógica de negocio queda en el Service
        $viaje = $this->viajeService->crearViaje(Auth::id(), $datos);
 
        return redirect()->route('pasajero.buscando', $viaje->id_viaje);
    }

    public function buscando(int $viajeId)
    {
        $viajeRaw = $this->validarViajePasajero($viajeId);

        if (in_array($viajeRaw->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)) {
            return redirect()->route('pasajero.enCurso', $viajeId);
        }

        if ($viajeRaw->estado_viaje !== 'buscando') {
            return redirect()
                ->route('pasajero.historial')
                ->with('error', 'Este viaje ya no se encuentra buscando conductor.');
        }

        $viaje = [
            'id'          => $viajeRaw->id_viaje,
            'origen'      => $viajeRaw->origen_texto,
            'destino'     => $viajeRaw->destino_texto,
            'tarifa'      => $viajeRaw->tarifa_estimada,
            'distancia'   => $viajeRaw->distancia_km,
            'tiempo'      => $viajeRaw->tiempo_estimado_min,
            'origen_lat'  => $viajeRaw->lat_origen,
            'origen_lng'  => $viajeRaw->lng_origen,
            'destino_lat' => $viajeRaw->lat_destino,
            'destino_lng' => $viajeRaw->lng_destino,
        ];

        return view('pasajero.buscando_conductor', [
            'header' => 'header_pasajero',
            'footer' => 'footer',
            'css'    => ['pasajero/pasajero.css', 'pasajero/solicitar_viaje.css'],
            'viaje'  => $viaje,
        ]);
    }

    public function cancelarViaje(Request $request)
    {
        $this->viajeService->cancelarViaje(
            (int) $request->viaje_id,
            Auth::id()
        );
 
        return redirect()->route('pasajero.solicitarViaje');
    }

    public function expirarViaje(Request $request)
    {
        $ok = $this->viajeService->expirarViaje(
            (int) $request->viaje_id,
            Auth::id()
        );
 
        return response()->json(['ok' => $ok]);
    }

    public function estadoViajeJson($viajeId)
    {
        if (! ctype_digit((string) $viajeId) || (int) $viajeId <= 0) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Datos invalidos.',
            ], 400);
        }

        $viajeId = (int) $viajeId;

        $viaje = Viaje::with('conductor.user', 'conductor.vehiculo')
            ->where('id_viaje', $viajeId)
            ->first();

        if (! $viaje) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El viaje no existe.',
            ], 404);
        }

        if ((int) $viaje->id_pasajero !== (int) Auth::id()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No tienes permiso para ver este viaje.',
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Estado actualizado',
            'viaje' => [
                'id' => $viaje->id_viaje,
                'estado' => $viaje->estado_viaje,
                'estado_label' => ucfirst(str_replace('_', ' ', $viaje->estado_viaje)),
                'origen' => $viaje->origen_texto,
                'destino' => $viaje->destino_texto,
                'origen_lat' => $viaje->lat_origen !== null ? (float) $viaje->lat_origen : null,
                'origen_lng' => $viaje->lng_origen !== null ? (float) $viaje->lng_origen : null,
                'destino_lat' => $viaje->lat_destino !== null ? (float) $viaje->lat_destino : null,
                'destino_lng' => $viaje->lng_destino !== null ? (float) $viaje->lng_destino : null,
                'tarifa' => number_format((float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0), 2),
                'distancia' => $viaje->distancia_km ? number_format((float) $viaje->distancia_km, 1) . ' km' : 'Sin distancia',
                'tiempo' => $viaje->tiempo_estimado_min ? (int) $viaje->tiempo_estimado_min . ' min' : 'Sin ETA',
                'redirect_url' => in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)
                    ? route('pasajero.enCurso', $viaje->id_viaje)
                    : null,
            ],
            'conductor' => $viaje->conductor ? [
                'nombre' => $viaje->conductor->user->nombre_completo ?? 'Conductor',
                'placa' => $viaje->conductor->vehiculo->placa ?? null,
                'modelo' => trim(($viaje->conductor->vehiculo->marca ?? '') . ' ' . ($viaje->conductor->vehiculo->modelo ?? '')),
                'lat' => $viaje->conductor->lat_actual !== null ? (float) $viaje->conductor->lat_actual : null,
                'lng' => $viaje->conductor->lng_actual !== null ? (float) $viaje->conductor->lng_actual : null,
            ] : null,
        ], 200);
    }

    public function enCurso(int $viajeId)
    {
        $viajeRaw = $this->validarViajePasajero($viajeId);
        $viajeRaw->load('conductor.user', 'conductor.vehiculo');

        if ($viajeRaw->estado_viaje === 'buscando') {
            return redirect()->route('pasajero.buscando', $viajeId);
        }

        if ($viajeRaw->estado_viaje === 'completado') {
            return redirect()->route('pasajero.calificar', $viajeId);
        }

        if (! in_array($viajeRaw->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)) {
            return redirect()
                ->route('pasajero.historial')
                ->with('error', 'Este viaje ya no se encuentra en curso.');
        }

        if (! $viajeRaw->conductor) {
            abort(404, 'El viaje no tiene conductor asignado.');
        }

        $viaje = [
            'id'          => $viajeRaw->id_viaje,
            'origen'      => $viajeRaw->origen_texto,
            'destino'     => $viajeRaw->destino_texto,
            'tarifa'      => $viajeRaw->tarifa_estimada,
            'metodo_pago' => $viajeRaw->metodo_pago,
            'origen_lat'  => $viajeRaw->lat_origen,
            'origen_lng'  => $viajeRaw->lng_origen,
            'destino_lat' => $viajeRaw->lat_destino,
            'destino_lng' => $viajeRaw->lng_destino,
            'estado'      => $viajeRaw->estado_viaje,
        ];
        $conductor = [
            'nombre'       => $viajeRaw->conductor->user->nombre_completo ?? 'Sin nombre',
            'calificacion' => $viajeRaw->conductor->calificacion_promedio ?? 0,
            'modelo'       => trim(($viajeRaw->conductor->vehiculo->marca ?? '') . ' ' . ($viajeRaw->conductor->vehiculo->modelo ?? '')),
            'placa'        => $viajeRaw->conductor->vehiculo->placa ?? 'Sin placa',
            'lat'          => $viajeRaw->conductor->lat_actual,
            'lng'          => $viajeRaw->conductor->lng_actual,
        ];
        $iniciales = $this->calcularIniciales($viajeRaw->conductor->user->nombre_completo ?? '');
        $eta       = $viajeRaw->tiempo_estimado_min ?? 'Sin tiempo estimado';
        $pasos     = $this->construirPasos($viajeRaw->estado_viaje);

        return view('pasajero.viaje_en_curso', [
            'header'    => 'header_pasajero',
            'footer'    => 'footer',
            'css'       => ['pasajero/pasajero.css', 'pasajero/solicitar_viaje.css'],
            'viaje'     => $viaje,
            'conductor' => $conductor,
            'iniciales' => $iniciales,
            'eta'       => $eta,
            'pasos'     => $pasos,
        ]);
    }

    public function calificar(int $viajeId)
    {
        $viajeRaw = $this->validarViajePasajero($viajeId);
        $viajeRaw->load('conductor.user', 'conductor.vehiculo');

        if ($viajeRaw->estado_viaje !== 'completado') {
            return redirect()
                ->route('pasajero.historial')
                ->with('error', 'Solo puedes calificar un viaje completado.');
        }

        if (! $viajeRaw->conductor) {
            abort(404, 'El viaje no tiene conductor asignado.');
        }

        $viaje = [
            'id'      => $viajeRaw->id_viaje,
            'origen'  => $viajeRaw->origen_texto,
            'destino' => $viajeRaw->destino_texto,
            'tarifa'  => $viajeRaw->tarifa_final ?? $viajeRaw->tarifa_estimada,
        ];
        $conductor = [
            'id'           => $viajeRaw->conductor->id_conductor,
            'nombre'       => $viajeRaw->conductor->user->nombre_completo ?? 'Sin nombre',
            'placa'        => $viajeRaw->conductor->vehiculo->placa ?? 'Sin placa',
            'calificacion' => $viajeRaw->conductor->calificacion_promedio ?? 0,
        ];
        $iniciales = $this->calcularIniciales($viajeRaw->conductor->user->nombre_completo ?? '');

        return view('pasajero.calificar_viaje', [
            'header'    => 'header_pasajero',
            'footer'    => 'footer',
            'css'       => ['pasajero/pasajero.css', 'pasajero/calificar_viaje.css'],
            'viaje'     => $viaje,
            'conductor' => $conductor,
            'iniciales' => $iniciales,
        ]);
    }

    public function enviarCalificacion(Request $request)
    {
        $request->validate([
            'viaje_id'     => 'required|integer',
            'conductor_id' => 'required|integer',
            'estrellas'    => 'required|integer|min:1|max:5',
            'comentario'   => 'nullable|string|max:500',
        ]);

        $viaje = Viaje::where('id_viaje', $request->viaje_id)
            ->where('id_pasajero', Auth::id())
            ->where('id_conductor', $request->conductor_id)
            ->where('estado_viaje', 'completado')
            ->first();

        if (!$viaje) {
            return redirect()
                ->route('pasajero.historial')
                ->withErrors(['viaje_id' => 'No puedes calificar este viaje.']);
        }
 
        $this->viajeService->calificarViaje(
            (int) $request->viaje_id,
            (int) $request->conductor_id,
            (int) $request->estrellas,
            (string) ($request->comentario ?? '')
        );
 
        return redirect()->route('pasajero.historial');
    }

    public function historial(Request $request)
    {
        $filtro  = $request->get('filtro', 'todos');
        $filtros = ['todos' => 'Todos', 'hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes'];
 
        $viajes = $this->viajeService
            ->historialPasajero(Auth::id(), $filtro, 10)
            ->through(fn($v) => $this->formatearViaje($v));
 
        return view('pasajero.historial', [
            'header'  => 'header_pasajero',
            'footer'  => 'footer',
            'css'     => ['pasajero/pasajero.css', 'pasajero/historial.css'],
            'viajes'  => $viajes,
            'filtro'  => $filtro,
            'filtros' => $filtros,
        ]);
    }

    public function perfil()
    {
        $user          = Auth::user();
        $pasajero      = $user->pasajero;
        
        // Si no existe registro en pasajeros, crearlo automáticamente
        if (!$pasajero) {
            $pasajero = \App\Models\Pasajero::create([
                'id_pasajero'           => $user->id_usuario,
                'metodo_pago_preferido' => 'efectivo',
            ]);
        }
        
        $iniciales     = $this->calcularIniciales($user->nombre_completo);
        $seccionActiva = 'perfil';

        return view('pasajero.perfil', [
            'header'        => 'header_pasajero',
            'footer'        => 'footer',
            'css'           => ['pasajero/pasajero.css', 'pasajero/perfil.css'],
            'user'          => $user,
            'pasajero'      => $pasajero,
            'iniciales'     => $iniciales,
            'seccionActiva' => $seccionActiva,
        ]);
    }

    public function editarPerfil()
    {
        $user          = Auth::user();
        $pasajero      = $user->pasajero;
        
        // Si no existe registro en pasajeros, crearlo automáticamente
        if (!$pasajero) {
            $pasajero = \App\Models\Pasajero::create([
                'id_pasajero'           => $user->id_usuario,
                'metodo_pago_preferido' => 'efectivo',
            ]);
        }
        
        $iniciales     = $this->calcularIniciales($user->nombre_completo);
        $seccionActiva = 'perfil';

        return view('pasajero.editar_perfil', [
            'header'        => 'header_pasajero',
            'footer'        => 'footer',
            'css'           => ['pasajero/pasajero.css', 'pasajero/editar_perfil.css'],
            'user'          => $user,
            'pasajero'      => $pasajero,
            'iniciales'     => $iniciales,
            'seccionActiva' => $seccionActiva,
        ]);
    }

    public function guardarPerfil(Request $request)
    {
        $request->validate([
            'nombre_completo'       => 'required|string|max:150',
            'apellidos'             => 'nullable|string|max:150',
            'telefono'              => 'nullable|regex:/^\+?[0-9\s\-]{7,15}$/',
            'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
        ]);

        $user = Auth::user();
        $user->update([
            'nombre_completo' => $request->nombre_completo,
            'apellidos'       => $request->apellidos,
            'telefono'        => $request->telefono,
        ]);

        $pasajero = Pasajero::firstOrCreate(
            ['id_pasajero' => $user->id_usuario],
            ['metodo_pago_preferido' => 'efectivo']
        );
        $pasajero->update([
            'metodo_pago_preferido' => $request->metodo_pago_preferido,
        ]);

        return redirect()->route('pasajero.perfil');
    }

    public function actualizarUbicacion(Request $request)
    {
        // Para uso futuro si el pasajero también comparte ubicación
        return response()->json(['ok' => true]);
    }

    private function validarViajePasajero(int $viajeId): Viaje
    {
        $viaje = Viaje::find($viajeId);

        if (! $viaje) {
            abort(404, 'Viaje no encontrado.');
        }

        if ((int) $viaje->id_pasajero !== (int) Auth::id()) {
            abort(403, 'No tienes permiso para ver este viaje.');
        }

        return $viaje;
    }

    private function redirigirSiTieneViajeActivo(Request $request)
    {
        $viajeActivo = $request->attributes->get('viajeActivoPasajero');

        if (! $viajeActivo) {
            return null;
        }

        $ruta = $viajeActivo->estado_viaje === 'buscando'
            ? 'pasajero.buscando'
            : 'pasajero.enCurso';

        return redirect()
            ->route($ruta, $viajeActivo->id_viaje)
            ->with('mensaje', 'Ya tienes un viaje activo.');
    }

    private function calcularIniciales(string $nombre): string
    {
        $partes = explode(' ', trim($nombre));
        return strtoupper(
            substr($partes[0] ?? '', 0, 1) .
            substr($partes[1] ?? '', 0, 1)
        );
    }

    private function construirPasos(string $estadoActual): array
    {
        $orden = ['aceptado', 'recogiendo', 'en_curso', 'completado'];
        $posicion = array_search($estadoActual, $orden, true);
        if ($posicion === false) $posicion = 0;

        $definiciones = [
            'aceptado'   => ['titulo' => 'Viaje aceptado',      'sub' => 'El conductor confirmó la solicitud'],
            'recogiendo' => ['titulo' => 'Recogiendo pasajero', 'sub' => 'El conductor va hacia ti'],
            'en_curso'   => ['titulo' => 'En camino',           'sub' => 'Llevándote al destino'],
            'completado' => ['titulo' => 'Llegaste',            'sub' => 'Has llegado a tu destino'],
        ];

        $pasos = [];
        foreach ($orden as $i => $estado) {
            $pasos[] = [
                'titulo' => $definiciones[$estado]['titulo'],
                'sub'    => $definiciones[$estado]['sub'],
                'estado' => $i < $posicion  ? 'hecho'
                          : ($i === $posicion ? 'activo' : 'pendiente'),
            ];
        }

        return $pasos;
    }

    private function formatearViaje($v): array
    {
        $precio = (float) ($v->tarifa_final ?? $v->tarifa_estimada ?? 0);
        $estado = (string) ($v->estado_viaje ?? 'pendiente');
        $metodoPago = match ((string) $v->metodo_pago) {
            'yape' => 'Yape',
            'plin' => 'Plin',
            'efectivo' => 'Efectivo',
            default => null,
        };

        return [
            'id'           => $v->id_viaje,
            'origen'       => $v->origen_texto,
            'destino'      => $v->destino_texto,
            'precio'       => $precio,
            'precio_label' => $v->tarifa_final !== null ? 'Tarifa final' : 'Tarifa estimada',
            'distancia'    => $v->distancia_km !== null ? number_format((float) $v->distancia_km, 1) . ' km' : 'â€”',
            'tiempo'       => $v->tiempo_estimado_min !== null ? (int) $v->tiempo_estimado_min . ' min' : 'â€”',
            'conductor'    => $v->conductor->user->nombre_completo ?? 'Sin conductor asignado',
            'metodo_pago'  => $metodoPago,
            'fecha'        => $v->fecha_solicitud?->format('d/m/Y') ?? '—',
            'distancia'    => $v->distancia_km ? $v->distancia_km . ' km' : '—',
            'tiempo'       => $v->tiempo_estimado_min ? $v->tiempo_estimado_min . ' min' : '—',
            'conductor'    => $v->conductor->user->nombre_completo ?? '—',
            'calificacion' => $v->calificacion->puntuacion ?? 0,
            'distancia'    => $v->distancia_km !== null ? number_format((float) $v->distancia_km, 1) . ' km' : 'â€”',
            'tiempo'       => $v->tiempo_estimado_min !== null ? (int) $v->tiempo_estimado_min . ' min' : 'â€”',
            'conductor'    => $v->conductor->user->nombre_completo ?? 'Sin conductor asignado',
            'metodo_pago'  => $metodoPago,
            'estado_viaje' => $v->estado_viaje,
            'borde_clase'  => match($v->estado_viaje) {
                'completado' => 'borde-verde',
                'cancelado'  => 'borde-rojo',
                default      => 'borde-dorado',
            },
            'badge_estado' => match($v->estado_viaje) {
                'completado' => '<span class="badge badge-verde">Completado</span>',
                'cancelado'  => '<span class="badge badge-rojo">Cancelado</span>',
                default      => '<span class="badge badge-gris">' . ucfirst($v->estado_viaje) . '</span>',
            },
            'estado_viaje' => $estado,
            'borde_clase'  => match($estado) {
                'completado' => 'borde-verde',
                'cancelado'  => 'borde-rojo',
                default      => 'borde-dorado',
            },
            'badge_estado' => match($estado) {
                'completado' => '<span class="badge badge-verde">Completado</span>',
                'cancelado'  => '<span class="badge badge-rojo">Cancelado</span>',
                default      => '<span class="badge badge-gris">' . ucfirst(str_replace('_', ' ', $estado)) . '</span>',
            },
        ];
    }

    private function distanciaKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radioTierraKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $radioTierraKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
