<?php

namespace App\Services;

use App\Models\Viaje;
use App\Models\Pasajero;
use App\Models\Comision;
use App\Models\Conductor;
use App\Models\Calificacion;
use App\Models\ConfiguracionTarifa;
use App\Events\ViajeCreado;
use Illuminate\Support\Facades\DB;

class ViajeService
{
    // Constantes de negocio

    private const PORCENTAJE_COMISION = 0.08; // 8 %
    private const TARIFA_BASE_DEFAULT = 3.00;

    private const PRECIO_KM_DEFAULT = 1.50;

    // Cálculo de tarifa 
    public function calcularTarifa(string $tipoServicio, float $distanciaKm): float
    {
        $config = ConfiguracionTarifa::where('tipo_servicio', $tipoServicio)
            ->where('activo', 1)
            ->first();

        $tarifaBase  = $config ? (float) $config->tarifa_base   : self::TARIFA_BASE_DEFAULT;
        $precioPorKm = $config ? (float) $config->precio_por_km : self::PRECIO_KM_DEFAULT;

        return round($tarifaBase + ($distanciaKm * $precioPorKm), 2);
    }

    // Creación de viaje
    public function crearViaje(int $pasajeroId, array $datos): Viaje
    {
        return DB::transaction(function () use ($pasajeroId, $datos) {

            // 1. Cancelar viajes previos en estado "buscando"
            $this->cancelarViajesBuscando($pasajeroId);

            // 2. Asegurar registro en tabla pasajeros
            Pasajero::firstOrCreate(
                ['id_pasajero' => $pasajeroId],
                ['metodo_pago_preferido' => 'efectivo']
            );

            // 3. Calcular tarifa
            $distanciaKm = (float) ($datos['distancia_km'] ?? 0);
            $tarifa      = $this->calcularTarifa($datos['tipo_servicio'], $distanciaKm);

            // 4. Crear el viaje
            $viaje = Viaje::create([
                'id_pasajero'         => $pasajeroId,
                'origen_texto'        => trim((string) $datos['origen']),
                'destino_texto'       => trim((string) $datos['destino']),
                'lat_origen'          => (float) $datos['origen_lat'],
                'lng_origen'          => (float) $datos['origen_lng'],
                'lat_destino'         => (float) $datos['destino_lat'],
                'lng_destino'         => (float) $datos['destino_lng'],
                'tarifa_estimada'     => $tarifa,
                'distancia_km'        => isset($datos['distancia_km']) ? (float) $datos['distancia_km'] : null,
                'tiempo_estimado_min' => isset($datos['tiempo_min']) ? (int) $datos['tiempo_min'] : null,
                'tipo_servicio'       => $datos['tipo_servicio'],
                'metodo_pago'         => $datos['metodo_pago'],
                'estado_viaje'        => 'buscando',
                'fecha_solicitud'     => now(),
            ]);

            // 5. Disparar evento (notifica a conductores disponibles via broadcast)
            event(new ViajeCreado($viaje));

            return $viaje;
        });
    }

    //  Cancelación 
    public function cancelarViajesBuscando(int $pasajeroId): void
    {
        Viaje::where('id_pasajero', $pasajeroId)
            ->where('estado_viaje', 'buscando')
            ->update(['estado_viaje' => 'cancelado']);
    }

    public function cancelarViaje(int $viajeId, int $pasajeroId): bool
    {
        $viaje = Viaje::where('id_viaje', $viajeId)
            ->where('id_pasajero', $pasajeroId)
            ->first();

        if (!$viaje || !in_array($viaje->estado_viaje, ['buscando', 'aceptado'])) {
            return false;
        }

        // Eliminación lógica: cambio de estado
        $viaje->update(['estado_viaje' => 'cancelado']);
        return true;
    }

    public function expirarViaje(int $viajeId, int $pasajeroId): bool
    {
        $viaje = Viaje::where('id_viaje', $viajeId)
            ->where('id_pasajero', $pasajeroId)
            ->where('estado_viaje', 'buscando')
            ->first();

        if (!$viaje) {
            return false;
        }

        $viaje->update(['estado_viaje' => 'expirado']);
        return true;
    }

    // Completar viaje y comisión 
    public function completarViaje(Viaje $viaje): array
    {
        return DB::transaction(function () use ($viaje) {

            $tarifaFinal   = (float) $viaje->tarifa_estimada;
            $montoComision = round($tarifaFinal * self::PORCENTAJE_COMISION, 2);
            $gananciaNeta  = round($tarifaFinal - $montoComision, 2);

            // 1. Cerrar viaje
            $viaje->update([
                'estado_viaje' => 'completado',
                'tarifa_final' => $tarifaFinal,
                'fecha_fin'    => now(),
            ]);

            // 2. Registrar comisión (si no existe ya)
            Comision::firstOrCreate(
                ['id_viaje' => $viaje->id_viaje],
                [
                    'id_conductor'   => $viaje->id_conductor,
                    'monto_comision' => $montoComision,
                    'fecha_descuento' => now(),
                ]
            );

            // 3. Acreditar ganancia neta al conductor
            Conductor::where('id_conductor', $viaje->id_conductor)
                ->increment('saldo_disponible', $gananciaNeta);

            // 4. Incrementar contador de viajes
            Conductor::where('id_conductor', $viaje->id_conductor)
                ->increment('total_viajes');

            return [
                'tarifa_final'  => $tarifaFinal,
                'comision'      => $montoComision,
                'ganancia_neta' => $gananciaNeta,
            ];
        });
    }

    // Calificación
    public function calificarViaje(
        int $viajeId,
        int $conductorId,
        int $estrellas,
        string $comentario = ''
    ): float {

        Calificacion::updateOrCreate(
            ['id_viaje' => $viajeId],
            ['puntuacion' => $estrellas, 'comentario' => $comentario]
        );

        // Recalcular promedio real desde BD
        $promedio = (float) Viaje::where('id_conductor', $conductorId)
            ->join('calificaciones', 'calificaciones.id_viaje', '=', 'viajes.id_viaje')
            ->avg('calificaciones.puntuacion');

        Conductor::where('id_conductor', $conductorId)
            ->update(['calificacion_promedio' => round($promedio, 2)]);

        return $promedio;
    }

    // Historial 
    public function historialPasajero(int $pasajeroId, string $filtro = 'todos', int $porPagina = 10)
    {
        $query = Viaje::where('id_pasajero', $pasajeroId)
            ->with('conductor.user', 'calificacion');

        $query = match($filtro) {
            'hoy'    => $query->whereDate('fecha_solicitud', today()),
            'semana' => $query->whereBetween('fecha_solicitud', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes'    => $query->whereMonth('fecha_solicitud', now()->month),
            default  => $query,
        };

        return $query
            ->orderByDesc('fecha_solicitud')
            ->simplePaginate($porPagina)
            ->withQueryString();
    }
}
