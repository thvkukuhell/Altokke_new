<?php
namespace App\Http\Controllers;

use App\Http\Requests\ActualizarPerfilConductorRequest;
use App\Http\Requests\ActualizarUbicacionRequest;
use App\Http\Requests\CancelarViajeRequest;
use App\Http\Requests\CompletarViajeRequest;
use App\Http\Requests\RecargarSaldoRequest;
use App\Models\Viaje;
use App\Models\Conductor;
use App\Models\Comision;
use App\Models\Notificacion;
use App\Models\RecargaSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ViajeNotificacionService;
use App\Services\ViajeService;

class ConductorController extends Controller
{
    private const COMISION_ALTOKKE = 0.08;

    // Helpers 
    private function calcularIniciales(string $nombre): string
    {
        $partes = explode(' ', trim($nombre));
        return strtoupper(
            substr($partes[0] ?? '', 0, 1) .
            substr($partes[1] ?? '', 0, 1)
        );
    }

    private function getConductorActual(): Conductor
    {
        return Conductor::with(['user', 'vehiculo'])
            ->findOrFail(Auth::id());
    }

    private function validarViajeConductor(int $viajeId, array $estadosPermitidos): Viaje
    {
        $viaje = Viaje::find($viajeId);

        if (! $viaje) {
            abort(404, 'Viaje no encontrado.');
        }

        if ((int) $viaje->id_conductor !== (int) Auth::id()) {
            abort(403, 'No tienes permiso para modificar este viaje.');
        }

        if (! in_array($viaje->estado_viaje, $estadosPermitidos, true)) {
            abort(409, 'El estado actual del viaje no permite esta accion.');
        }

        return $viaje;
    }

    private function construirPasos(string $estadoActual): array
    {
        $orden = ['aceptado', 'recogiendo', 'en_curso', 'completado'];
        $posicion = array_search($estadoActual, $orden, true);

        $definiciones = [
            'aceptado'   => ['titulo' => 'Viaje aceptado',      'sub' => 'Dirígete al punto de origen'],
            'recogiendo' => ['titulo' => 'Recogiendo pasajero', 'sub' => 'El pasajero te está esperando'],
            'en_curso'   => ['titulo' => 'En curso',            'sub' => 'Llevando al pasajero al destino'],
            'completado' => ['titulo' => 'Completado',          'sub' => 'Has llegado al destino'],
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

    // Dashboard / Inicio 
    public function index()
    {
        $conductor   = $this->getConductorActual();
        $vehiculo    = $conductor->vehiculo;
        $viajeActivo = Viaje::where('id_conductor', Auth::id())
                            ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                            ->with('pasajero.user')
                            ->first();

        $ganancias = Viaje::where('id_conductor', Auth::id())
                          ->where('estado_viaje', 'completado')
                          ->selectRaw('
                              COALESCE(SUM(tarifa_final), 0) as total,
                              COUNT(*)                  as total_viajes,
                              COALESCE(SUM(CASE WHEN DATE(fecha_fin) = CURDATE() THEN tarifa_final ELSE 0 END), 0) as hoy
                          ')
                          ->first();

        $iniciales     = $this->calcularIniciales($conductor->user->nombre_completo ?? '');
        $seccionActiva = 'inicio';

        return view('conductor.inicio', [
            'header'       => 'header_conductor',
            'footer'       => 'footer',
            'css'          => ['conductor/inicio.css'], 
            'conductor'    => $conductor,
            'vehiculo'     => $vehiculo,
            'viajeActivo'  => $viajeActivo,
            'ganancias'    => $ganancias,
            'iniciales'    => $iniciales,
            'seccionActiva'=> $seccionActiva,
        ]);
    }

    // Perfil
    public function perfil()
    {
        $conductor     = $this->getConductorActual();
        $vehiculo      = $conductor->vehiculo;
        $iniciales     = $this->calcularIniciales($conductor->user->nombre_completo ?? '');
        $seccionActiva = 'perfil';

        return view('conductor.perfil', [
            'header'       => 'header_conductor',
            'footer'       => 'footer',
            'css'          => ['conductor/perfil.css'],
            'conductor'    => $conductor,
            'vehiculo'     => $vehiculo,
            'iniciales'    => $iniciales,
            'seccionActiva'=> $seccionActiva,
        ]);
    }

    public function actualizarPerfil(ActualizarPerfilConductorRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data): void {
            $user->update([
                'nombre_completo' => $data['nombre_completo'],
                'apellidos' => $data['apellidos'] ?? null,
                'telefono' => $data['telefono'],
                'email' => $data['email'],
            ]);
        });

        return redirect()
            ->route('conductor.perfil')
            ->with('mensaje', 'Perfil actualizado correctamente.');
    }

    // Solicitudes
    public function solicitudes()
    {
        $conductor = $this->getConductorActual();
        $puedeTomarViajes = $conductor->estado_conductor === 'activo'
            && (float) $conductor->saldo_disponible > 0;

        // Solo muestra viajes en estado 'buscando' sin conductor asignado
        $solicitudes = Viaje::where('estado_viaje', 'buscando')
                            ->whereNull('id_conductor')
                            ->with('pasajero.user')
                            ->orderByDesc('fecha_solicitud')
                            ->limit(25)
                            ->get();

        $iniciales     = $this->calcularIniciales($conductor->user->nombre_completo ?? '');
        $seccionActiva = 'solicitudes';

        return view('conductor.solicitudes', [
            'header'       => 'header_conductor',
            'footer'       => 'footer',
            'css'          => ['conductor/solicitudes.css'],
            'conductor'    => $conductor,
            'iniciales'    => $iniciales,
            'seccionActiva'=> $seccionActiva,
            'solicitudes' => $solicitudes,
            'puedeTomarViajes' => $puedeTomarViajes, 
        ]);
    }

    public function solicitudesJson()
    {
        try {
            $conductor = $this->getConductorActual();
            $puedeTomarViajes = $conductor->estado_conductor === 'activo'
                && (float) $conductor->saldo_disponible > 0;

            $solicitudes = Viaje::where('estado_viaje', 'buscando')
                ->whereNull('id_conductor')
                ->with('pasajero.user')
                ->orderByDesc('fecha_solicitud')
                ->limit(25)
                ->get()
                ->map(fn (Viaje $viaje) => $this->formatearSolicitudJson($viaje));

            return response()->json([
                'ok' => true,
                'mensaje' => 'Solicitudes actualizadas',
                'puede_tomar_viajes' => $puedeTomarViajes,
                'total' => $solicitudes->count(),
                'solicitudes' => $solicitudes,
            ], 200);
        } catch (\Throwable) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se pudieron cargar las solicitudes.',
            ], 500);
        }
    }

    private function formatearSolicitudJson(Viaje $viaje): array
    {
        return [
            'id' => $viaje->id_viaje,
            'origen' => $viaje->origen_texto,
            'destino' => $viaje->destino_texto,
            'origen_lat' => $viaje->lat_origen !== null ? (float) $viaje->lat_origen : null,
            'origen_lng' => $viaje->lng_origen !== null ? (float) $viaje->lng_origen : null,
            'destino_lat' => $viaje->lat_destino !== null ? (float) $viaje->lat_destino : null,
            'destino_lng' => $viaje->lng_destino !== null ? (float) $viaje->lng_destino : null,
            'pasajero' => $viaje->pasajero->user->nombre_completo ?? 'Pasajero',
            'metodo_pago' => ucfirst($viaje->metodo_pago ?? 'efectivo'),
            'tipo_servicio' => $viaje->tipo_servicio ?? 'normal',
            'tarifa' => number_format((float) ($viaje->tarifa_estimada ?? 0), 2),
            'distancia' => $viaje->distancia_km ? number_format((float) $viaje->distancia_km, 1) . ' km' : 'Sin distancia',
            'tiempo' => $viaje->tiempo_estimado_min ? (int) $viaje->tiempo_estimado_min . ' min' : 'Sin ETA',
            'fecha' => $viaje->fecha_solicitud?->diffForHumans() ?? 'Reciente',
        ];
    }

    public function aceptarViaje(CompletarViajeRequest $request)
    {
        try {
            $viaje = app(ViajeService::class)->aceptarViaje((int) $request->id_viaje, (int) Auth::id());
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            if ($e->getStatusCode() === 403 && str_contains($e->getMessage(), 'Saldo')) {
                return redirect()->route('conductor.billetera')->with('mensaje', $e->getMessage());
            }

            if ($e->getStatusCode() === 403 && str_contains($e->getMessage(), 'disponible')) {
                return redirect()->route('conductor.solicitudes')->with('mensaje', $e->getMessage());
            }

            abort($e->getStatusCode(), $e->getMessage());
        }

        event(new \App\Events\ViajeAceptado($viaje));
        Notificacion::create([
            'id_usuario' => $viaje->id_pasajero,
            'titulo' => 'Viaje aceptado',
            'mensaje' => 'Un conductor aceptó tu solicitud de viaje',
        ]);

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'aceptado',
            (int) $viaje->id_viaje
        ));

        return redirect()
            ->route('conductor.viaje_activo')
            ->with('mensaje', '¡Viaje aceptado! Dirígete a recoger al pasajero.');
    }

    public function recogerPasajero(CompletarViajeRequest $request)
    {
        $viaje = app(ViajeService::class)->cambiarEstadoConductor(
            (int) $request->id_viaje,
            (int) Auth::id(),
            'aceptado',
            'recogiendo'
        );

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'recogiendo',
            (int) $viaje->id_viaje
        ));

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'estado' => 'recogiendo']);
        }

        return redirect()->route('conductor.viaje_activo');
    }

    public function iniciarTrayecto(CompletarViajeRequest $request)
    {
        $viaje = app(ViajeService::class)->cambiarEstadoConductor(
            (int) $request->id_viaje,
            (int) Auth::id(),
            'recogiendo',
            'en_curso'
        );

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'en_curso',
            (int) $viaje->id_viaje
        ));

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'estado' => 'en_curso']);
        }

        return redirect()->route('conductor.viaje_activo');
    }

    // Viaje activo 
    public function viajeActivo()
    {
        $conductor = $this->getConductorActual();

        $viaje = Viaje::where('id_conductor', Auth::id())
                      ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                      ->with('pasajero.user')
                      ->first();

        if (! $viaje) {
            return redirect()->route('conductor.dashboard')
                             ->with('mensaje', 'No tienes ningún viaje activo en este momento.');
        }

        $iniciales     = $this->calcularIniciales($conductor->user->nombre_completo ?? '');
        $pasos         = $viaje ? $this->construirPasos($viaje->estado_viaje) : [];

        $seccionActiva = 'viajeActivo';

        return view('conductor.viaje_activo', [
            'header'       => 'header_conductor',
            'footer'       => 'footer',
            'css'          => ['conductor/viaje_activo.css'],
            'conductor'    => $conductor,
            'iniciales'    => $iniciales,
            'seccionActiva'=> $seccionActiva,
            'viaje' => $viaje,
            'pasos' => $pasos,
        ]);
    }

    public function completarViaje(CompletarViajeRequest $request)
    {
        $request->validate([
            'id_viaje' => 'required|integer',
        ]);

        try {
            $viaje = app(ViajeService::class)->completarViaje((int) $request->id_viaje, (int) Auth::id());
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            if ($e->getStatusCode() === 409 && str_contains($e->getMessage(), 'saldo')) {
                return redirect()
                    ->route('conductor.billetera')
                    ->with('mensaje', $e->getMessage());
            }

            abort($e->getStatusCode(), $e->getMessage());
        }

        Notificacion::create([
            'id_usuario' => $viaje->id_pasajero,
            'titulo' => 'Viaje completado',
            'mensaje' => 'Tu viaje finalizo correctamente. Ya puedes calificar al conductor.',
        ]);

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'completado',
            (int) $viaje->id_viaje
        ));

        app(ViajeNotificacionService::class)->enviarResumenCompletado($viaje);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'estado' => 'completado',
                'redirect_url' => route('conductor.solicitudes'),
            ]);
        }

        return redirect()
            ->route('conductor.solicitudes')
            ->with('mensaje', 'Viaje terminado con exito. Buen trabajo.');
    }


    public function cancelarViaje(CancelarViajeRequest $request)
    {
        $viaje = Viaje::find((int) $request->viaje_id);
        if (! $viaje || ! Auth::user()?->can('cancel', $viaje)) {
            return redirect()
                ->route('conductor.dashboard')
                ->with('error', 'No se encontró el viaje o no tienes permiso para cancelarlo.');
        }

        $viaje->update([
            'estado_viaje' => 'cancelado',
            'motivo_cancelacion' => $request->motivo_cancelacion,
            'motivo_cancelacion_otro' => $request->input('motivo_cancelacion_otro') ? trim((string) $request->motivo_cancelacion_otro) : null,
        ]);

        Notificacion::create([
            'id_usuario' => $viaje->id_pasajero,
            'titulo' => 'Viaje cancelado',
            'mensaje' => 'El conductor canceló el viaje.',
        ]);

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'cancelado',
            (int) $viaje->id_viaje
        ));

        return redirect()->route('conductor.solicitudes');
    }

    // Historial 
    public function historial()
    {
        $conductor = $this->getConductorActual();

        $historial = Viaje::where('id_conductor', Auth::id())
                          ->whereIn('estado_viaje', ['completado', 'cancelado'])
                          ->with('pasajero.user', 'calificacion')
                          ->orderByDesc('fecha_solicitud')
                          ->simplePaginate(10)
                          ->withQueryString()
                          ->through(function ($v) {
                              return [
                                  ...$v->toArray(),
                                  'calificacion' => $v->calificacion?->puntuacion !== null
                                      ? (int) $v->calificacion->puntuacion
                                      : null,
                                  'fecha_fin' => $v->fecha_fin?->toDateTimeString(),
                                  'borde_clase'  => match($v->estado_viaje) {
                                      'completado' => 'borde-verde',
                                      'cancelado'  => 'borde-rojo',
                                      default      => 'borde-dorado',
                                  },
                                  'estado_texto' => match($v->estado_viaje) {
                                      'completado' => 'Completado',
                                      'cancelado'  => 'Cancelado',
                                      default      => ucfirst(str_replace('_', ' ', (string) $v->estado_viaje)),
                                  },
                                  'badge_clase' => match($v->estado_viaje) {
                                      'completado' => 'badge-verde',
                                      'cancelado'  => 'badge-rojo',
                                      default      => 'badge-gris',
                                  },
                              ];
                          });

        $ganancias = Viaje::where('id_conductor', Auth::id())
                          ->where('estado_viaje', 'completado')
                          ->selectRaw('
                              SUM(tarifa_final)  as total,
                              COUNT(*)            as total_viajes,
                              SUM(CASE WHEN DATE(fecha_fin) = CURDATE() THEN tarifa_final ELSE 0 END) as hoy
                          ')
                          ->first();

        $iniciales     = $this->calcularIniciales($conductor->user->nombre_completo ?? '');
        $seccionActiva = 'historial';

        return view('conductor.historial_viaje', [
            'header'       => 'header_conductor',
            'footer'       => 'footer',
            'css'          => ['conductor/historial.css'],
            'conductor'    => $conductor,
            'ganancias'    => $ganancias,
            'iniciales'    => $iniciales,
            'seccionActiva'=> $seccionActiva,
            'historial' => $historial,
        ]);
    }

    // Billetera
    public function billetera()
    {
        $conductor = $this->getConductorActual();

        $ganancias = Viaje::where('id_conductor', Auth::id())
                          ->where('estado_viaje', 'completado')
                          ->selectRaw('
                              SUM(tarifa_final)   as total,
                              COUNT(*)             as total_viajes,
                              SUM(CASE WHEN DATE(fecha_fin) = CURDATE()
                                  THEN tarifa_final ELSE 0 END) as hoy,
                              SUM(CASE WHEN WEEK(fecha_fin) = WEEK(CURDATE())
                                  THEN tarifa_final ELSE 0 END) as semana
                          ')
                          ->first();

        $ultimosViajes = Viaje::where('id_conductor', Auth::id())
                              ->where('estado_viaje', 'completado')
                              ->with('pasajero.user')
                              ->orderByDesc('fecha_fin')
                              ->limit(10)
                              ->get();
        $recargas = RecargaSaldo::where('id_conductor', Auth::id())
                                ->orderByDesc('fecha_solicitud')
                                ->limit(5)
                                ->get();
        $comisiones = Comision::where('id_conductor', Auth::id())
                              ->orderByDesc('fecha_descuento')
                              ->limit(5)
                              ->get();

        $iniciales     = $this->calcularIniciales($conductor->user->nombre_completo ?? '');
        $seccionActiva = 'billetera';

        return view('conductor.billetera', [
            'header'       => 'header_conductor',
            'footer'       => 'footer',
            'css'          => ['conductor/billetera.css'],
            'conductor'    => $conductor,
            'ganancias'    => $ganancias,
            'iniciales'    => $iniciales,
            'seccionActiva'=> $seccionActiva,
            'ultimosViajes' => $ultimosViajes,
            'recargas' => $recargas,
            'comisiones' => $comisiones,
            'porcentajeComision' => self::COMISION_ALTOKKE * 100,
        ]);
    }

    public function recargarSaldo(RecargarSaldoRequest $request)
    {
        $conductor = $this->getConductorActual();

        RecargaSaldo::create([
            'id_conductor' => $conductor->id_conductor,
            'monto' => $request->monto,
            'metodo_recarga' => $request->metodo_recarga,
            'referencia' => $request->referencia,
            'estado_recarga' => 'aprobada',
            'fecha_aprobacion' => now(),
        ]);

        $conductor->increment('saldo_disponible', $request->monto);

        Notificacion::create([
            'id_usuario' => $conductor->id_conductor,
            'titulo' => 'Recarga aprobada',
            'mensaje' => 'Se agregó saldo a tu billetera de conductor.',
        ]);

        return redirect()
            ->route('conductor.billetera')
            ->with('mensaje', 'Recarga simulada aprobada correctamente.');
    }

    public function actualizarUbicacion(ActualizarUbicacionRequest $request)
    {
        try {
            $viaje = app(ViajeService::class)->actualizarUbicacionConductor(
                (int) $request->viaje_id,
                (int) Auth::id(),
                (float) $request->lat,
                (float) $request->lng
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        event(new \App\Events\ConductorMovido(
            $viaje->id_viaje,
            (float) $request->lat,
            (float) $request->lng
        ));

        return response()->json([
            'ok' => true,
            'mensaje' => 'Ubicación actualizada',
            'lat' => (float) $request->lat,
            'lng' => (float) $request->lng,
        ]);
    }
}
