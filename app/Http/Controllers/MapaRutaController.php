<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapaRutaController extends Controller
{
    public function rutaEstimada(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'origen.lat' => 'required|numeric|between:-90,90',
            'origen.lng' => 'required|numeric|between:-180,180',
            'destino.lat' => 'required|numeric|between:-90,90',
            'destino.lng' => 'required|numeric|between:-180,180',
        ]);

        $origen = [
            'lat' => (float) $datos['origen']['lat'],
            'lng' => (float) $datos['origen']['lng'],
        ];
        $destino = [
            'lat' => (float) $datos['destino']['lat'],
            'lng' => (float) $datos['destino']['lng'],
        ];

        $fallback = $this->crearFallback($origen, $destino);
        $apiKey = config('services.openrouteservice.key');

        if (! $apiKey) {
            return response()->json([
                ...$fallback,
                'ok' => false,
                'estado' => 'fallback',
                'mensaje' => 'OpenRouteService no configurado',
            ]);
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => $apiKey,
                    'Accept' => 'application/json, application/geo+json',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openrouteservice.org/v2/directions/driving-car/geojson', [
                    'coordinates' => [
                        [$origen['lng'], $origen['lat']],
                        [$destino['lng'], $destino['lat']],
                    ],
                ]);

            if (! $response->successful()) {
                return response()->json([
                    ...$fallback,
                    'ok' => false,
                    'estado' => 'fallback',
                    'mensaje' => 'No se pudo consultar OpenRouteService',
                ]);
            }

            $feature = $response->json('features.0');
            $coordinates = $feature['geometry']['coordinates'] ?? [];
            $summary = $feature['properties']['summary'] ?? [];

            if (! $coordinates || ! isset($summary['distance'], $summary['duration'])) {
                return response()->json([
                    ...$fallback,
                    'ok' => false,
                    'estado' => 'fallback',
                    'mensaje' => 'Ruta externa sin datos suficientes',
                ]);
            }

            return response()->json([
                'ok' => true,
                'estado' => 'ruta_real',
                'mensaje' => 'Ruta calculada',
                'coordenadas' => array_map(
                    fn (array $coord) => [(float) $coord[1], (float) $coord[0]],
                    $coordinates
                ),
                'distancia_km' => round(((float) $summary['distance']) / 1000, 2),
                'duracion_min' => (int) ceil(((float) $summary['duration']) / 60),
            ]);
        } catch (\Throwable) {
            return response()->json([
                ...$fallback,
                'ok' => false,
                'estado' => 'fallback',
                'mensaje' => 'Ruta externa no disponible',
            ]);
        }
    }

    private function crearFallback(array $origen, array $destino): array
    {
        $distanciaKm = $this->distanciaHaversine(
            $origen['lat'],
            $origen['lng'],
            $destino['lat'],
            $destino['lng']
        );

        return [
            'coordenadas' => [
                [$origen['lat'], $origen['lng']],
                [$destino['lat'], $destino['lng']],
            ],
            'distancia_km' => round($distanciaKm, 2),
            'duracion_min' => max(1, (int) ceil($distanciaKm * 3)),
        ];
    }

    private function distanciaHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radioTierraKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $radioTierraKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
