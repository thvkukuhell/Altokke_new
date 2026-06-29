<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasajeroHistorialApiController extends Controller {
    
    public function buscar(Request $request) {
        $filtro = $request->query('filtro', 'todos');
        $q = trim((string) $request->query('q', ''));

        $query = Viaje::where('id_pasajero', Auth::id())
            ->with(['conductor.user', 'calificacion']);

        $query = match($filtro) {
            'hoy' => $query->whereDate('fecha_solicitud', today()),
            'semana' => $query->whereBetween('fecha_solicitud', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes' => $query->whereMonth('fecha_solicitud', now()->month),
            default => $query,
        };

        if ($q !== '') {
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('origen_texto', 'like', '%' . $q . '%')
                    ->orWhere('destino_texto', 'like', '%' . $q . '%')
                    ->orWhere('estado_viaje', 'like', '%' . $q . '%')
                    ->orWhereHas('conductor.user', function($userQuery) use ($q) {
                        $userQuery->where('nombre_completo', 'like', '%' . $q . '%');
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

    private function formatearViaje(Viaje $viaje): array {
        $estado = (string) $viaje->estado_viaje;

        return [
            'id' => $viaje->id_viaje,
            'origen' => $viaje->origen_texto,
            'destino' => $viaje->destino_texto,
            'precio' => (float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0),
            'fecha' => $viaje->fecha_solicitud?->format('d/m/Y') ?? '-',
            'distancia' => $viaje->distancia_km ? $viaje->distancia_km . 'km' : '-',
            'tiempo' => $viaje->tiempo_estimado_min ? $viaje->tiempo_estimado_min . 'min' : '-',
            'conductor' => $viaje->conductor->user->nombre_completo ?? '-',
            'calificacion' => (int) ($viaje->calificacion->puntuacion ?? 0),
            'estado_texto' => ucfirst(str_replace('_', ' ', $estado)),
            'badge_clase' => match($estado) {
                'completado' => 'badge-verde',
                'cancelado' => 'badge-rojo',
                default => 'badge-gris',
            },
            'borde_clase' => match($estado) {
                'completado' => 'borde-verde',
                'cancelado' => 'borde-rojo',
                default => 'borde-dorado',
            },
        ];
    }
}