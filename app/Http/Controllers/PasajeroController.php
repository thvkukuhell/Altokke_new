<?php

namespace App\Http\Controllers;

use App\Models\Viaje;
use App\Models\Pasajero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Recibe peticiones HTTP, valida datos, llama al DAO y carga la vista correspondiente

class PasajeroController extends Controller {

    public function index()
    {
        return redirect()->route('pasajero.solicitarViaje');
    }

    public function solicitarViaje()
    {
        return view('pasajero.solicitar_viaje');
    }

    public function crearViaje(Request $request)
    {
        $request->validate([
            'origen'        => 'required|string',
            'destino'       => 'required|string|different:origen',
            'tipo_servicio' => 'required|in:normal,express',
            'metodo_pago'   => 'required|in:efectivo,yape,plin',
        ], [
            'destino.different' => 'El origen y destino no pueden ser iguales.',
        ]);

        // Verificar que exista el pasajero
        $pasajero = Pasajero::find(Auth::id());
        if (!$pasajero) {
            Pasajero::create([
                'id_pasajero' => Auth::id(),
                'metodo_pago_preferido' => 'efectivo',
            ]);
        }

        $viaje = Viaje::create([
            'id_pasajero'     => Auth::id(),
            'origen_texto'    => $request->origen,
            'destino_texto'   => $request->destino,
            'tarifa_estimada' => 3.00,
            'tipo_servicio'   => $request->tipo_servicio,
            'metodo_pago'     => $request->metodo_pago,
            'estado_viaje'    => 'buscando',
        ]);

        return redirect()->route('pasajero.buscando', $viaje->id_viaje);
    }

    public function buscando(int $viajeId)
    {
        $viajeRaw = Viaje::find($viajeId);

        $viaje = $viajeRaw ? [
            'id'      => $viajeRaw->id_viaje,
            'origen'  => $viajeRaw->origen_texto,
            'destino' => $viajeRaw->destino_texto,
            'tarifa'  => $viajeRaw->tarifa_estimada,
        ] : [
            'id' => 0, 'origen' => '—', 'destino' => '—', 'tarifa' => '0.00'
        ];

        return view('pasajero.buscando_conductor', compact('viaje'));
    }

    public function cancelarViaje(Request $request)
    {
        $viaje = Viaje::find($request->viaje_id);
        if ($viaje) {
            $viaje->update(['estado_viaje' => 'cancelado']);
        }
        return redirect()->route('pasajero.solicitarViaje');
    }

    public function enCurso(int $viajeId)
    {
        $viajeRaw = Viaje::with('conductor.user', 'conductor.vehiculo')->find($viajeId);

        if (!$viajeRaw || !$viajeRaw->conductor) {
            $viaje = [
                'id' => 0, 'origen' => '-', 'destino' => '-',
                'tarifa' => '0.00', 'metodo_pago' => 'efectivo'
            ];
            $conductor = ['nombre' => '-', 'calificacion' => 0, 'modelo' => '-', 'placa' => '-'];
            $iniciales = '--';
            $eta = '-';
            $pasos = [];
        } else {
            $viaje = [
                'id' => $viajeRaw->id_viaje,
                'origen' => $viajeRaw->origen_texto,
                'destino' => $viajeRaw->destino_texto,
                'tarifa' => $viajeRaw->tarifa_estimada,
                'metodo_pago' => $viajeRaw->metodo_pago,
            ];
            $conductor = [
                'nombre' => $viajeRaw->conductor->user->nombre_completo ?? '-',
                'calificacion' => $viajeRaw->conductor->califcacion_promedio ?? 0,
                'modelo' => ($viajeRaw->conductor->vehiculo->marca ?? '') . ' ' . ($viajeRaw->conductor->vehiculo->modelo ?? ''),
                'placa' => $viajeRaw->conductor->vehiculo->placa ?? '-',
            ];
            $iniciales = $this->calcularIniciales($viajeRaw->conductor->user->nombre_completo ?? '');
            $eta = $viajeRaw->tiempo_estimado_min ?? '-';
            $pasos = $this->construirPasos($viajeRaw->estado_viaje);
        }

        return view('pasajero.viaje_en_curso', compact('viaje', 'conductor', 'iniciales', 'eta', 'pasos'));
    }

    public function calificar (int $viajeId)
    {
        $viajeRaw = Viaje::with('conductor.user', 'conductor.vehiculo')->find($viajeId);

        if (!$viajeRaw || !$viajeRaw->conductor) {
            $viaje = ['id' => 0, 'origen' => '-', 'destino' => '-', 'tarifa' => '0.00'];
            $conductor = ['id' => 0, 'nombre' => '-', 'placa' => '-', 'calificacion' => 0];
            $inicales = '--';
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

        return view('pasajero.calificar_viaje', compact('viaje', 'conductor', 'iniciales'));
    }

    public function enviarCalificacion(Request $request) 
    {
        $request->validate([
            'viaje_id' => 'required|integer',
            'conductor_id' => 'required|integer',
            'estrellas' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500', 
        ]);

        \App\Models\Calificacion::updateOrCreate(
            ['id_viaje' => $request->viaje_id],
            [
                'puntuacion'  => $request->estrellas,
                'comentario'  => $request->comentario ?? '',
            ]
        );

        // Recalcular promedio del conductor
        $promedio = Viaje::where('id_conductor', $request->conductor_id)
                         ->join('calificaciones', 'calificaciones.id_viaje', '=', 'viajes.id_viaje')
                         ->avg('calificaciones.puntuacion');

        \App\Models\Conductor::where('id_conductor', $request->conductor_id)
                             ->update(['calificacion_promedio' => $promedio]);

        return redirect()->route('pasajero.historial');
    }

    public function historial(Request $request)
    {
        $filtros = [
            'todos'  => 'Todos',
            'hoy'    => 'Hoy',
            'semana' => 'Esta semana',
            'mes'    => 'Este mes',
        ];

        $filtro  = $request->get('filtro', 'todos');
        $query   = Viaje::where('id_pasajero', Auth::id())->with('conductor.user', 'calificacion');

        $query = match($filtro) {
            'hoy'    => $query->whereDate('fecha_solicitud', today()),
            'semana' => $query->whereBetween('fecha_solicitud', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes'    => $query->whereMonth('fecha_solicitud', now()->month),
            default  => $query,
        };

        $viajes = $query->orderByDesc('fecha_solicitud')->get()->map(function ($v) {
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
        });

        return view('pasajero.historial', compact('viajes', 'filtro', 'filtros'));
    }

    public function perfil()
    {
        $user     = Auth::user();
        $pasajero = $user->pasajero;
        $iniciales = $this->calcularIniciales($user->nombre_completo);
        $seccionActiva = 'perfil';

        return view('pasajero.perfil', compact('user', 'pasajero', 'iniciales', 'seccionActiva'));
    }

    public function editarPerfil() 
    {
        $user = Auth::user();
        $pasajero = $user->pasajero;
        $iniciales = $this->calcularIniciales($user->nombre_completo);
        $seccionActiva = 'perfil';

        return view('pasajero.editar_perfil', compact('user', 'pasajero', 'iniciales', 'seccionActiva'));
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
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
        ]);

        $user->pasajero->update([
            'metodo_pago_preferido' => $request->metodo_pago_preferido,
        ]);

        return redirect()->route('pasajero.perfil');
    }

    private function calcularIniciales(string $nombre): string
    {
        $partes = explode(' ', trim($nombre));
        return strtoupper(substr($partes[0] ?? '', 0, 1) . substr($partes[1] ?? '', 0, 1));
    }

    private function construirPasos(string $estadoActual): array
    {
        $orden = ['aceptado', 'recogiendo', 'en_curso', 'completado'];
        $posicion = array_search($estadoActual, $orden, true);

        $definiciones = [
            'aceptado'   => ['titulo' => 'Viaje aceptado',      'sub' => 'El conductor confirmó la solicitud'],
            'recogiendo' => ['titulo' => 'Recogiendo pasajero', 'sub' => 'El conductor va a tu origen'],
            'en_curso'   => ['titulo' => 'En curso',            'sub' => 'Llevándote al destino'],
            'completado' => ['titulo' => 'Completado',          'sub' => 'Has llegado a tu destino'],
        ];

        $pasos = [];
        foreach ($orden as $i => $estado) {
            $pasos[] = [
                'titulo' => $definiciones[$estado]['titulo'],
                'sub'    => $definiciones[$estado]['sub'],
                'estado' => $i < $posicion ? 'hecho' : ($i === $posicion ? 'activo' : 'pendiente'),
            ];
        }

        return $pasos;
    }
}