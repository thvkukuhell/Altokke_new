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
            // 1. Asegurar registro en tabla pasajeros
            Pasajero::firstOrCreate(
                ['id_pasajero' => $pasajeroId],
                ['metodo_pago_preferido' => 'efectivo']
            );

            // 2. Calcular tarifa
            $distanciaKm = (float) ($datos['distancia_km'] ?? 0);
            $tarifa      = $this->calcularTarifa($datos['tipo_servicio'], $distanciaKm);

            // 3. Crear el viaje
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

            // 4. Disparar evento (notifica a conductores disponibles via broadcast)
            event(new ViajeCreado($viaje));

            return $viaje;
        });
    }

    //  Cancelación 
    public function cancelarViaje(
        int $viajeId,
        int $pasajeroId,
        string $motivoCancelacion,
        ?string $motivoCancelacionOtro = null
    ): bool {
        $viaje = Viaje::find($viajeId);

        if (! $viaje) {
            abort(404, 'Viaje no encontrado.');
        }

        if ((int) $viaje->id_pasajero !== $pasajeroId) {
            abort(403, 'No tienes permiso para cancelar este viaje.');
        }

        if (! in_array($viaje->estado_viaje, ['buscando', 'aceptado', 'recogiendo'], true)) {
            abort(409, 'El viaje solo puede cancelarse antes de que el conductor inicie el recorrido.');
        }

        $datosActualizacion = [
            'estado_viaje' => 'cancelado',
            'motivo_cancelacion' => $motivoCancelacion,
            'motivo_cancelacion_otro' => $motivoCancelacionOtro,
        ];

        $viaje->update($datosActualizacion);
        return true;
    }

    public function inicializarUbicacionConductor(Viaje $viaje, Conductor $conductor): void
    {
        $latOrigen = $viaje->lat_origen !== null ? (float) $viaje->lat_origen : null;
        $lngOrigen = $viaje->lng_origen !== null ? (float) $viaje->lng_origen : null;

        if (
            $latOrigen === null || $lngOrigen === null
            || $latOrigen < -90 || $latOrigen > 90
            || $lngOrigen < -180 || $lngOrigen > 180
            || ($latOrigen === 0.0 && $lngOrigen === 0.0)
        ) {
            $latOrigen = -5.63889;
            $lngOrigen = -78.5311;
        }

        $distanciaKm = 0.3;
        $angulo = fmod(((int) $viaje->id_viaje * 137.508), 360) * pi() / 180;
        $latRadianes = $latOrigen * pi() / 180;

        $conductor->update([
            'lat_actual' => $latOrigen + (($distanciaKm / 111.32) * cos($angulo)),
            'lng_actual' => $lngOrigen + (($distanciaKm / (111.32 * cos($latRadianes))) * sin($angulo)),
            'ubicacion_actualizada_en' => now(),
        ]);
    }

    public function aceptarViaje(int $viajeId, int $conductorId): Viaje
    {
        return DB::transaction(function () use ($viajeId, $conductorId) {
            $viaje = Viaje::where('id_viaje', $viajeId)->lockForUpdate()->first();

            if (! $viaje) {
                abort(404, 'Viaje no encontrado.');
            }

            if ($viaje->estado_viaje !== 'buscando' || $viaje->id_conductor !== null) {
                abort(409, 'Este viaje ya no está disponible.');
            }

            $conductor = Conductor::where('id_conductor', $conductorId)->lockForUpdate()->first();

            if (! $conductor || $conductor->estado_conductor !== 'activo') {
                abort(403, 'Conductor no disponible para aceptar viajes.');
            }

            if ((float) $conductor->saldo_disponible <= 0) {
                abort(403, 'Saldo insuficiente para aceptar viajes.');
            }

            if (Viaje::where('id_conductor', $conductorId)
                ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                ->exists()) {
                abort(409, 'Ya tienes un viaje activo.');
            }

            $viaje->update([
                'id_conductor' => $conductorId,
                'estado_viaje' => 'aceptado',
                'fecha_inicio' => now(),
            ]);

            $this->inicializarUbicacionConductor($viaje, $conductor);

            return $viaje->fresh(['conductor.user', 'conductor.vehiculo', 'pasajero.user']);
        });
    }

    public function cambiarEstadoConductor(int $viajeId, int $conductorId, string $estadoEsperado, string $estadoNuevo): Viaje
    {
        $viaje = Viaje::find($viajeId);

        if (! $viaje) {
            abort(404, 'Viaje no encontrado.');
        }

        if ((int) $viaje->id_conductor !== $conductorId) {
            abort(403, 'No tienes permiso para modificar este viaje.');
        }

        if ($viaje->estado_viaje !== $estadoEsperado) {
            abort(409, 'El estado actual del viaje no permite esta acción.');
        }

        $viaje->update(['estado_viaje' => $estadoNuevo]);

        return $viaje->fresh(['pasajero.user', 'conductor.user']);
    }

    public function actualizarUbicacionConductor(int $viajeId, int $conductorId, float $lat, float $lng): Viaje
    {
        $viaje = Viaje::find($viajeId);

        if (! $viaje) {
            abort(404, 'Viaje no encontrado.');
        }

        if ((int) $viaje->id_conductor !== $conductorId) {
            abort(403, 'No tienes permiso para actualizar este viaje.');
        }

        if (! in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)) {
            abort(409, 'El estado del viaje no permite actualizar ubicación.');
        }

        Conductor::where('id_conductor', $conductorId)->update([
            'lat_actual' => $lat,
            'lng_actual' => $lng,
            'ubicacion_actualizada_en' => now(),
        ]);

        return $viaje;
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
    public function completarViaje(int $viajeId, int $conductorId): Viaje
    {
        return DB::transaction(function () use ($viajeId, $conductorId) {
            $viaje = Viaje::where('id_viaje', $viajeId)
                ->lockForUpdate()
                ->first();

            if (! $viaje) {
                abort(404, 'Viaje no encontrado.');
            }

            if ((int) $viaje->id_conductor !== $conductorId) {
                abort(403, 'No tienes permiso para completar este viaje.');
            }

            if ($viaje->estado_viaje !== 'en_curso') {
                abort(409, 'El viaje no puede completarse desde su estado actual.');
            }

            $conductor = Conductor::where('id_conductor', $conductorId)
                ->lockForUpdate()
                ->first();

            if (! $conductor) {
                abort(404, 'Conductor no encontrado.');
            }

            $tarifaFinal = (float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0);
            $montoComision = round($tarifaFinal * self::PORCENTAJE_COMISION, 2);

            if ((float) $conductor->saldo_disponible < $montoComision) {
                abort(409, 'Tu saldo no alcanza para cubrir la comisión de este viaje.');
            }

            $viaje->update([
                'estado_viaje' => 'completado',
                'tarifa_final' => $tarifaFinal,
                'fecha_fin' => now(),
            ]);

            Comision::updateOrCreate(
                ['id_viaje' => $viaje->id_viaje],
                [
                    'id_conductor' => $conductorId,
                    'monto_comision' => $montoComision,
                    'fecha_descuento' => now()->toDateString(),
                ]
            );

            $conductor->decrement('saldo_disponible', $montoComision);
            $conductor->increment('total_viajes');

            return $viaje->fresh(['pasajero.user', 'conductor.user']);
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
