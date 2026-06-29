<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConductorHistorialApiController extends Controller {
    public function buscar(Request $request) {
        $q = trim((string) $request->query('q', ''));
        $filtro = $request->query('filtro', 'todos');

        $query = Viaje::where('id_conductor', Auth::id())
            ->whereIn('estado_viaje', ['completado', 'cancelado'])
            ->with(['pasajero.user', 'calificacion']);

        $query = match ($filtro) {
            'hoy' => $query->whereDate('fecha_solicitud', today()),
            'semana' => $query->whereBetween('fecha_solicitud', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes' => $query->whereMonth('fecha_solicitud', now()->month)
                ->whereYear('fecha_solicitud', now()->year),
            default => $query,
        };

        if ($q !== '') {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('origen_texto', 'like', '%' . $q . '%')
                    ->orWhere('destino_texto', 'like', '%' . $q . '%')
                    ->orWhere('estado_viaje', 'like', '%' . $q . '%')
                    ->orWhere('metodo_pago', 'like', '%' . $q . '%')
                    ->orWhere('tipo_servicio', 'like', '%' . $q . '%')
                    ->orWhereHas('pasajero.user', function ($userQuery) use ($q) {
                        $userQuery->where('nombre_completo', 'like', '%' . $q . '%')
                            ->orWhere('apellidos', 'like', '%' . $q . '%');
                    });
            });
        }

        $viajes = $query->orderByDesc('fecha_solicitud')
            ->limit(30)
            ->get()
            ->map(fn ($viaje) => $this->formatearViaje($viaje));

        return response()->json([
            'ok' => true,
            'total' => $viajes->count(),
            'data' => $viajes,
        ]);
    }

    public function formatearViaje(Viaje $viaje): array {
        $estado = (string) $viaje->estado_viaje;
        $pasajero = trim(($viaje->pasajero->user->nombre_completo ?? '') . ' ' . ($viaje->pasajero->user->apellidos ?? ''));

        return [
            'id' => $viaje->id_viaje,
            'origen' => $viaje->origen_texto ?? '—',
            'destino' => $viaje->destino_texto ?? '—',
            'precio' => (float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0),
            'fecha' => $viaje->fecha_fin?->format('d/m/Y H:i')
                ?? $viaje->fecha_solicitud?->format('d/m/Y H:i')
                ?? '—',
            'distancia' => $viaje->distancia_km ? $viaje->distancia_km . ' km' : '—',
            'tiempo' => $viaje->tiempo_estimado_min ? $viaje->tiempo_estimado_min . ' min' : '—',
            'pasajero' => $pasajero !== '' ? $pasajero : 'Pasajero',
            'metodo_pago' => ucfirst((string) ($viaje->metodo_pago ?? '—')),
            'tipo_servicio' => ucfirst((string) ($viaje->tipo_servicio ?? '—')),
            'calificacion' => (int) ($viaje->calificacion->puntuacion ?? 0),
            'estado_texto' => ucfirst(str_replace('_', ' ', $estado)),
            'badge_clase' => match ($estado) {
                'completado' => 'badge-verde',
                'cancelado' => 'badge-rojo',
                default => 'badge-gris',
            },
            'borde_clase' => match ($estado) {
                'completado' => 'borde-verde',
                'cancelado' => 'borde-rojo',
                default => 'borde-dorado',
            },
        ];
    }

    
}