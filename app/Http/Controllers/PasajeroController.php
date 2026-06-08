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

    public function solicitarViaje()
    {
        return view('pasajero.solicitar_viaje', [
            'header' => 'header_pasajero',
            'footer' => 'footer',
            'css'    => ['pasajero/pasajero.css', 'pasajero/solicitar_viaje.css'],
        ]);
    }

    public function crearViaje(Request $request)
    {
        // Validación de entrada (saneamiento contra datos maliciosos)
        $datos = $request->validate([
            'origen'        => 'required|string|max:300',
            'destino'       => 'required|string|different:origen|max:300',
            'tipo_servicio' => 'required|in:normal,express',
            'metodo_pago'   => 'required|in:efectivo,yape,plin',
            'origen_lat'    => 'nullable|numeric|between:-90,90',
            'origen_lng'    => 'nullable|numeric|between:-180,180',
            'destino_lat'   => 'nullable|numeric|between:-90,90',
            'destino_lng'   => 'nullable|numeric|between:-180,180',
            'distancia_km'  => 'nullable|numeric|min:0|max:500',
            'tiempo_min'    => 'nullable|integer|min:0',
        ], [
            'destino.different' => 'El origen y destino no pueden ser iguales.',
        ]);
 
        // Toda la lógica de negocio queda en el Service
        $viaje = $this->viajeService->crearViaje(Auth::id(), $datos);
 
        return redirect()->route('pasajero.buscando', $viaje->id_viaje);
    }

    public function buscando(int $viajeId)
    {
        $viajeRaw = Viaje::find($viajeId);

        // Si el viaje fue aceptado mientras llegaba aquí, redirigir directo
        if ($viajeRaw && in_array($viajeRaw->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'])) {
            return redirect()->route('pasajero.enCurso', $viajeId);
        }

        $viaje = $viajeRaw ? [
            'id'          => $viajeRaw->id_viaje,
            'origen'      => $viajeRaw->origen_texto,
            'destino'     => $viajeRaw->destino_texto,
            'tarifa'      => $viajeRaw->tarifa_estimada,
            'distancia'   => $viajeRaw->distancia_km,
            'tiempo'      => $viajeRaw->tiempo_estimado_min,
            'origen_lat'  => $viajeRaw->lat_origen  ?? -5.63889,
            'origen_lng'  => $viajeRaw->lng_origen  ?? -78.5311,
            'destino_lat' => $viajeRaw->lat_destino ?? -5.6800,
            'destino_lng' => $viajeRaw->lng_destino ?? -78.5400,
        ] : [
            'id' => 0, 'origen' => '—', 'destino' => '—', 'tarifa' => '0.00',
            'origen_lat' => -5.63889, 'origen_lng' => -78.5311,
            'destino_lat' => -5.6800, 'destino_lng' => -78.5400,
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

    public function enCurso(int $viajeId)
    {
        $viajeRaw = Viaje::with('conductor.user', 'conductor.vehiculo')->find($viajeId);

        if (!$viajeRaw || !$viajeRaw->conductor) {
            $viaje = [
                'id'          => $viajeId,
                'origen'      => '—',
                'destino'     => '—',
                'tarifa'      => '0.00',
                'metodo_pago' => 'efectivo',
                'origen_lat'  => -5.63889,
                'origen_lng'  => -78.5311,
                'destino_lat' => -5.6800,
                'destino_lng' => -78.5400,
                'estado'      => 'buscando',
            ];
            $conductor = [
                'nombre'      => '—',
                'calificacion' => 0,
                'modelo'      => '—',
                'placa'       => '—',
                'lat'         => -5.63889,
                'lng'         => -78.5311,
            ];
            $iniciales = '--';
            $eta       = '—';
            $pasos     = $this->construirPasos('aceptado');
        } else {
            $viaje = [
                'id'          => $viajeRaw->id_viaje,
                'origen'      => $viajeRaw->origen_texto,
                'destino'     => $viajeRaw->destino_texto,
                'tarifa'      => $viajeRaw->tarifa_estimada,
                'metodo_pago' => $viajeRaw->metodo_pago,
                'origen_lat'  => $viajeRaw->lat_origen  ?? -5.63889,
                'origen_lng'  => $viajeRaw->lng_origen  ?? -78.5311,
                'destino_lat' => $viajeRaw->lat_destino ?? -5.6800,
                'destino_lng' => $viajeRaw->lng_destino ?? -78.5400,
                'estado'      => $viajeRaw->estado_viaje,
            ];
            $conductor = [
                'nombre'       => $viajeRaw->conductor->user->nombre_completo ?? '—',
                'calificacion' => $viajeRaw->conductor->calificacion_promedio ?? 0,
                'modelo'       => trim(($viajeRaw->conductor->vehiculo->marca ?? '') . ' ' . ($viajeRaw->conductor->vehiculo->modelo ?? '')),
                'placa'        => $viajeRaw->conductor->vehiculo->placa ?? '—',
                'lat'          => -5.63889,
                'lng'          => -78.5311,
            ];
            $iniciales = $this->calcularIniciales($viajeRaw->conductor->user->nombre_completo ?? '');
            $eta       = $viajeRaw->tiempo_estimado_min ?? '—';
            $pasos     = $this->construirPasos($viajeRaw->estado_viaje);
        }

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
        $viajeRaw = Viaje::with('conductor.user', 'conductor.vehiculo')->find($viajeId);

        if (!$viajeRaw || !$viajeRaw->conductor) {
            $viaje     = ['id' => 0, 'origen' => '—', 'destino' => '—', 'tarifa' => '0.00'];
            $conductor = ['id' => 0, 'nombre' => '—', 'placa' => '—', 'calificacion' => 0];
            $iniciales = '--';
        } else {
            $viaje = [
                'id'      => $viajeRaw->id_viaje,
                'origen'  => $viajeRaw->origen_texto,
                'destino' => $viajeRaw->destino_texto,
                'tarifa'  => $viajeRaw->tarifa_final ?? $viajeRaw->tarifa_estimada,
            ];
            $conductor = [
                'id'           => $viajeRaw->conductor->id_conductor,
                'nombre'       => $viajeRaw->conductor->user->nombre_completo ?? '—',
                'placa'        => $viajeRaw->conductor->vehiculo->placa ?? '—',
                'calificacion' => $viajeRaw->conductor->calificacion_promedio ?? 0,
            ];
            $iniciales = $this->calcularIniciales($viajeRaw->conductor->user->nombre_completo ?? '');
        }

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
            ->historialPasajero(Auth::id(), $filtro)
            ->map(fn($v) => $this->formatearViaje($v));
 
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
        return [
            'id'           => $v->id_viaje,
            'origen'       => $v->origen_texto,
            'destino'      => $v->destino_texto,
            'precio'       => number_format($v->tarifa_final ?? $v->tarifa_estimada, 2),
            'fecha'        => $v->fecha_solicitud?->format('d/m/Y') ?? '—',
            'distancia'    => $v->distancia_km ? $v->distancia_km . ' km' : '—',
            'tiempo'       => $v->tiempo_estimado_min ? $v->tiempo_estimado_min . ' min' : '—',
            'conductor'    => $v->conductor->user->nombre_completo ?? '—',
            'calificacion' => $v->calificacion->puntuacion ?? 0,
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
        ];
    }
}

