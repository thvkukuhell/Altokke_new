<?php

namespace App\Http\Controllers\Api;

use App\Events\ConductorMovido;
use App\Events\ViajeAceptado;
use App\Events\ViajeActualizado;
use App\Jobs\SimularLlegadaConductor;
use App\Models\Comision;
use App\Models\Conductor;
use App\Models\Notificacion;
use App\Models\Viaje;
use App\Services\ViajeNotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternalViajeController extends BaseApiController
{
    private const COMISION_ALTOKKE = 0.08;

    public function show(int $id): JsonResponse
    {
        $viaje = Viaje::with(['pasajero.user', 'conductor.user', 'conductor.vehiculo', 'calificacion'])->find($id);

        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        if (! $this->puedeVerViaje($viaje)) {
            return $this->errorJson('No tienes permiso para ver este viaje', 403);
        }

        return $this->exitoJson('Viaje encontrado', $this->formatearViaje($viaje));
    }

    public function solicitudesConductor(): JsonResponse
    {
        if (! $this->esConductor()) {
            return $this->errorJson('Solo conductores pueden ver solicitudes', 403);
        }

        $solicitudes = Viaje::where('estado_viaje', 'buscando')
            ->whereNull('id_conductor')
            ->with('pasajero.user')
            ->orderByDesc('fecha_solicitud')
            ->get()
            ->map(fn (Viaje $viaje) => $this->formatearSolicitud($viaje));

        return $this->exitoJson('Solicitudes disponibles', [
            'total' => $solicitudes->count(),
            'solicitudes' => $solicitudes,
        ]);
    }

    public function viajeActivoPasajero(): JsonResponse
    {
        if (! $this->esPasajero()) {
            return $this->errorJson('Solo pasajeros pueden consultar su viaje activo', 403);
        }

        $viaje = Viaje::with(['conductor.user', 'conductor.vehiculo'])
            ->where('id_pasajero', Auth::id())
            ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
            ->orderByDesc('fecha_solicitud')
            ->first();

        if (! $viaje) {
            return $this->errorJson('No tienes viaje activo', 404);
        }

        return $this->exitoJson('Viaje activo encontrado', $this->formatearViaje($viaje));
    }

    public function aceptar(Request $request, int $id): JsonResponse
    {
        if (! $this->esConductor()) {
            return $this->errorJson('Solo conductores pueden aceptar viajes', 403);
        }

        $viaje = Viaje::where('id_viaje', $id)->first();
        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        $conductor = Conductor::with('user')->find(Auth::id());
        if (! $conductor || $conductor->estado_conductor !== 'activo') {
            return $this->errorJson('Conductor no disponible para aceptar viajes', 403);
        }

        if ((float) $conductor->saldo_disponible <= 0) {
            return $this->errorJson('Saldo insuficiente para aceptar viajes', 403);
        }

        if ($viaje->estado_viaje !== 'buscando' || $viaje->id_conductor !== null) {
            return $this->errorJson('Este viaje ya no esta disponible', 400);
        }

        $viaje->update([
            'id_conductor' => Auth::id(),
            'estado_viaje' => 'aceptado',
            'fecha_inicio' => now(),
        ]);

        $viaje->load('conductor.user', 'conductor.vehiculo', 'pasajero.user');
        event(new ViajeAceptado($viaje));
        event(new ViajeActualizado((int) $viaje->id_pasajero, 'aceptado', (int) $viaje->id_viaje));

        Notificacion::create([
            'id_usuario' => $viaje->id_pasajero,
            'titulo' => 'Viaje aceptado',
            'mensaje' => 'Un conductor acepto tu solicitud de viaje',
        ]);

        dispatch(new SimularLlegadaConductor($viaje));

        return $this->exitoJson('Viaje aceptado', $this->formatearViaje($viaje));
    }

    public function actualizarUbicacion(Request $request, int $id): JsonResponse
    {
        if (! $this->esConductor()) {
            return $this->errorJson('Solo conductores pueden actualizar ubicacion', 403);
        }

        $datos = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $viaje = Viaje::find($id);

        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if ((int) $viaje->id_conductor !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para actualizar este viaje', 403);
        }

        if (! in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)) {
            return $this->errorJson('El viaje no permite actualizar ubicacion', 400);
        }

        Conductor::where('id_conductor', Auth::id())->update([
            'lat_actual' => (float) $datos['lat'],
            'lng_actual' => (float) $datos['lng'],
            'ubicacion_actualizada_en' => now(),
        ]);

        event(new ConductorMovido($viaje->id_viaje, (float) $datos['lat'], (float) $datos['lng']));

        return $this->exitoJson('Ubicacion actualizada', [
            'viaje_id' => $viaje->id_viaje,
            'lat' => (float) $datos['lat'],
            'lng' => (float) $datos['lng'],
        ]);
    }

    public function completar(int $id): JsonResponse
    {
        if (! $this->esConductor()) {
            return $this->errorJson('Solo conductores pueden completar viajes', 403);
        }

        $viaje = Viaje::find($id);

        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if ((int) $viaje->id_conductor !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para completar este viaje', 403);
        }

        if (! in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)) {
            return $this->errorJson('El viaje no se puede completar en su estado actual', 400);
        }

        $conductor = Conductor::find(Auth::id());
        $tarifaFinal = (float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0);
        $montoComision = round($tarifaFinal * self::COMISION_ALTOKKE, 2);

        if (! $conductor || (float) $conductor->saldo_disponible < $montoComision) {
            return $this->errorJson('Saldo insuficiente para cubrir la comision', 403);
        }

        $viaje->update([
            'estado_viaje' => 'completado',
            'tarifa_final' => $tarifaFinal,
            'fecha_fin' => now(),
        ]);

        Comision::updateOrCreate(
            ['id_viaje' => $viaje->id_viaje],
            [
                'id_conductor' => $viaje->id_conductor,
                'monto_comision' => $montoComision,
                'fecha_descuento' => now()->toDateString(),
            ]
        );

        $conductor->decrement('saldo_disponible', $montoComision);
        $conductor->increment('total_viajes');

        event(new ViajeActualizado((int) $viaje->id_pasajero, 'completado', (int) $viaje->id_viaje));
        app(ViajeNotificacionService::class)->enviarResumenCompletado(
            $viaje->fresh(['pasajero.user', 'conductor.user'])
        );

        return $this->exitoJson('Viaje completado', $this->formatearViaje($viaje->fresh(['pasajero.user', 'conductor.user'])));
    }

    public function historialConductor(Request $request): JsonResponse
    {
        if (! $this->esConductor()) {
            return $this->errorJson('Solo conductores pueden ver este historial', 403);
        }

        // esto es de Debounce en busqueda
        $texto = $this->limpiarTexto($request->query('texto', ''));
        $consulta = Viaje::where('id_conductor', Auth::id())
            ->whereIn('estado_viaje', ['completado', 'cancelado']);

        if ($texto !== '') {
            $consulta->where(function ($query) use ($texto) {
                $query->where('origen_texto', 'like', '%'.$texto.'%')
                    ->orWhere('destino_texto', 'like', '%'.$texto.'%')
                    ->orWhere('estado_viaje', 'like', '%'.$texto.'%')
                    ->orWhereHas('pasajero.user', function ($usuarioQuery) use ($texto) {
                        $usuarioQuery->where('nombre_completo', 'like', '%'.$texto.'%');
                    });
            });
        }

        $viajes = $consulta
            ->with(['pasajero.user', 'calificacion'])
            ->orderByDesc('fecha_solicitud')
            ->limit(30)
            ->get()
            ->map(fn (Viaje $viaje) => $this->formatearViaje($viaje));

        return $this->exitoJson('Historial del conductor', $viajes);
    }

    public function historialPasajero(Request $request): JsonResponse
    {
        if (! $this->esPasajero()) {
            return $this->errorJson('Solo pasajeros pueden ver este historial', 403);
        }

        // esto es de Debounce en busqueda
        $texto = $this->limpiarTexto($request->query('texto', ''));
        $consulta = Viaje::where('id_pasajero', Auth::id());

        if ($texto !== '') {
            $consulta->where(function ($query) use ($texto) {
                $query->where('origen_texto', 'like', '%'.$texto.'%')
                    ->orWhere('destino_texto', 'like', '%'.$texto.'%')
                    ->orWhere('estado_viaje', 'like', '%'.$texto.'%')
                    ->orWhereHas('conductor.user', function ($usuarioQuery) use ($texto) {
                        $usuarioQuery->where('nombre_completo', 'like', '%'.$texto.'%');
                    });
            });
        }

        $viajes = $consulta
            ->with(['conductor.user', 'calificacion'])
            ->orderByDesc('fecha_solicitud')
            ->limit(30)
            ->get()
            ->map(fn (Viaje $viaje) => $this->formatearViaje($viaje));

        return $this->exitoJson('Historial del pasajero', $viajes);
    }

    private function puedeVerViaje(Viaje $viaje): bool
    {
        $user = Auth::user();

        return match ($user?->tipo_usuario) {
            'pasajero' => (int) $viaje->id_pasajero === (int) Auth::id(),
            'conductor' => (int) $viaje->id_conductor === (int) Auth::id()
                || ($viaje->estado_viaje === 'buscando' && $viaje->id_conductor === null),
            default => false,
        };
    }

    private function esConductor(): bool
    {
        return Auth::check() && Auth::user()->tipo_usuario === 'conductor';
    }

    private function esPasajero(): bool
    {
        return Auth::check() && Auth::user()->tipo_usuario === 'pasajero';
    }

    private function formatearViaje(Viaje $viaje): array
    {
        return [
            'id' => $viaje->id_viaje,
            'estado' => $viaje->estado_viaje,
            'estado_label' => ucfirst(str_replace('_', ' ', $viaje->estado_viaje)),
            'origen' => $viaje->origen_texto,
            'destino' => $viaje->destino_texto,
            'origen_lat' => $viaje->lat_origen !== null ? (float) $viaje->lat_origen : null,
            'origen_lng' => $viaje->lng_origen !== null ? (float) $viaje->lng_origen : null,
            'destino_lat' => $viaje->lat_destino !== null ? (float) $viaje->lat_destino : null,
            'destino_lng' => $viaje->lng_destino !== null ? (float) $viaje->lng_destino : null,
            'tarifa_estimada' => (float) $viaje->tarifa_estimada,
            'tarifa_final' => $viaje->tarifa_final ? (float) $viaje->tarifa_final : null,
            'distancia_km' => $viaje->distancia_km ? (float) $viaje->distancia_km : null,
            'tiempo_estimado_min' => $viaje->tiempo_estimado_min ? (int) $viaje->tiempo_estimado_min : null,
            'metodo_pago' => $viaje->metodo_pago,
            'tipo_servicio' => $viaje->tipo_servicio,
            'fecha' => $viaje->fecha_solicitud?->format('d/m/Y H:i'),
            'precio' => (float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0),
            'precio_label' => $viaje->tarifa_final !== null ? 'Tarifa final' : 'Tarifa estimada',
            'calificacion' => (int) ($viaje->calificacion?->puntuacion ?? 0),
            'pasajero' => $viaje->pasajero?->user ? [
                'id' => $viaje->id_pasajero,
                'nombre' => $viaje->pasajero->user->nombre_completo,
            ] : null,
            'conductor' => $viaje->conductor?->user ? [
                'id' => $viaje->id_conductor,
                'nombre' => $viaje->conductor->user->nombre_completo,
                'placa' => $viaje->conductor->vehiculo->placa ?? null,
                'lat' => $viaje->conductor->lat_actual ? (float) $viaje->conductor->lat_actual : null,
                'lng' => $viaje->conductor->lng_actual ? (float) $viaje->conductor->lng_actual : null,
            ] : null,
            'redirect_url' => in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)
                ? route('pasajero.enCurso', $viaje->id_viaje)
                : null,
            'comprobante_url' => $viaje->estado_viaje === 'completado'
                ? route('reportes.viajes.comprobante', $viaje->id_viaje)
                : null,
        ];
    }

    private function formatearSolicitud(Viaje $viaje): array
    {
        return [
            'id' => $viaje->id_viaje,
            'estado' => $viaje->estado_viaje,
            'origen' => $viaje->origen_texto,
            'destino' => $viaje->destino_texto,
            'origen_lat' => $viaje->lat_origen !== null ? (float) $viaje->lat_origen : null,
            'origen_lng' => $viaje->lng_origen !== null ? (float) $viaje->lng_origen : null,
            'destino_lat' => $viaje->lat_destino !== null ? (float) $viaje->lat_destino : null,
            'destino_lng' => $viaje->lng_destino !== null ? (float) $viaje->lng_destino : null,
            'pasajero' => $viaje->pasajero->user->nombre_completo ?? 'Pasajero',
            'tarifa' => number_format((float) ($viaje->tarifa_estimada ?? 0), 2),
            'metodo_pago' => $viaje->metodo_pago,
            'tipo_servicio' => $viaje->tipo_servicio,
            'distancia_km' => $viaje->distancia_km ? (float) $viaje->distancia_km : null,
            'tiempo_estimado_min' => $viaje->tiempo_estimado_min ? (int) $viaje->tiempo_estimado_min : null,
            'distancia' => $viaje->distancia_km ? number_format((float) $viaje->distancia_km, 1).' km' : 'Sin distancia',
            'tiempo' => $viaje->tiempo_estimado_min ? (int) $viaje->tiempo_estimado_min.' min' : 'Sin ETA',
            'fecha' => $viaje->fecha_solicitud?->diffForHumans() ?? 'Reciente',
        ];
    }
}
