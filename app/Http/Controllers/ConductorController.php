<?php
namespace App\Http\Controllers;

use App\Models\Viaje;
use App\Models\Conductor;
use App\Models\Comision;
use App\Models\Notificacion;
use App\Models\RecargaSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SimularLlegadaConductor;
use App\Services\ViajeNotificacionService;

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

    public function actualizarPerfil(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'apellidos'       => 'nullable|string|max:150',
            'telefono'        => 'nullable|regex:/^\+?[0-9\s\-]{7,15}$/',
            'email'           => 'nullable|email|unique:usuarios,email,' . Auth::id() . ',id_usuario',
        ], [
            'nombre_completo.required' => 'El nombre es obligatorio.',
            'email.unique'             => 'El email ya está en uso.',
        ]);

        Auth::user()->update([
            'nombre_completo' => $request->nombre_completo,
            'apellidos'       => $request->apellidos,
            'telefono'        => $request->telefono,
            'email'           => $request->email,
        ]);

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

    public function aceptarViaje(Request $request) 
    {
        $request->validate([
            'id_viaje' => 'required|integer',
        ]);

        $viaje = Viaje::find((int) $request->id_viaje);

        if (! $viaje) {
            abort(404, 'Viaje no encontrado.');
        }

        $conductor = $this->getConductorActual();
        
        if ($conductor->estado_conductor !== 'activo') {
            return redirect() 
                ->route('conductor.solicitudes')
                ->with('mensaje', 'Tu cuenta de conductor aún está en verificación. No puedes aceptar viajes todavía.');
        }

        if ((float) $conductor->saldo_disponible <= 0) {
            return redirect()
                ->route('conductor.billetera')
                ->with('mensaje', 'Necesitas recargar saldo antes de aceptar viajes.');
        }

        // Evitar que dos conductores acepten el mismo viaje
        if (Viaje::where('id_conductor', Auth::id())
            ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
            ->exists()) {
            abort(409, 'Ya tienes un viaje activo.');
        }

        $actualizados = Viaje::where('id_viaje', $viaje->id_viaje)
            ->where('estado_viaje', 'buscando')
            ->whereNull('id_conductor')
            ->update([
                'id_conductor' => Auth::id(),
                'estado_viaje' => 'aceptado',
                'fecha_inicio' => now(),
            ]);

        if ($actualizados === 0) {
            abort(409, 'Este viaje ya fue tomado por otro conductor.');
        }

        $viaje->refresh();

        // 2. Disparar eventos
        event(new \App\Events\ViajeAceptado($viaje->load('conductor.user', 'conductor.vehiculo')));
        Notificacion::create([
            'id_usuario' => $viaje->id_pasajero,
            'titulo' => 'Viaje_aceptado',
            'mensaje' => 'Un conductor aceptó tu solicitud de viaje',
        ]);

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'aceptado',
            (int) $viaje->id_viaje
        ));

        // Inicia simulación de llegada del conductor
        dispatch(new SimularLlegadaConductor($viaje));

        return redirect()
            ->route('conductor.viaje_activo')
            ->with('mensaje', '¡Viaje aceptado! Dirígete a recoger al pasajero.');
    }

    public function recogerPasajero(Request $request)
    {
        $request->validate([
            'id_viaje' => 'required|integer',
        ]);

        $viaje = $this->validarViajeConductor(
            (int) $request->id_viaje,
            ['aceptado']
        );
        $viaje->update(['estado_viaje' => 'recogiendo']);

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'recogiendo',
            (int) $viaje->id_viaje
        ));

        return redirect()->route('conductor.viaje_activo');
    }

    public function iniciarTrayecto(Request $request)
    {
        $request->validate([
            'id_viaje' => 'required|integer',
        ]);

        $viaje = $this->validarViajeConductor(
            (int) $request->id_viaje,
            ['recogiendo']
        );
        $viaje->update(['estado_viaje' => 'en_curso']);

        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'en_curso',
            (int) $viaje->id_viaje
        ));

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

    public function completarViaje(Request $request)
    {
        $request->validate([
            'id_viaje' => 'required|integer',
        ]);

        $viaje = $this->validarViajeConductor(
            (int) $request->id_viaje,
            ['aceptado', 'recogiendo', 'en_curso']
        );
        $conductor = $this->getConductorActual();
        $tarifaFinal = (float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0);
        $montoComision = round($tarifaFinal * self::COMISION_ALTOKKE, 2);

        if ((float) $conductor->saldo_disponible < $montoComision) {
            return redirect()
                ->route('conductor.billetera')
                ->with('mensaje', 'Tu saldo no alcanza para cubrir la comisión de este viaje.');
        }

        $viaje->update([
            'estado_viaje' => 'completado',
            'tarifa_final' => $tarifaFinal,
            'fecha_fin'    => now()
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

        Notificacion::create([
            'id_usuario' => $viaje->id_pasajero,
            'titulo' => 'Viaje completado',
            'mensaje' => 'Tu viaje finalizó correctamente. Ya puedes calificar al conductor.',
        ]);

        // 3.  Pasar los 3 parámetros individuales al constructor del evento
        event(new \App\Events\ViajeActualizado(
            (int) $viaje->id_pasajero,
            'completado',
            (int) $viaje->id_viaje
        ));

        app(ViajeNotificacionService::class)->enviarResumenCompletado(
            $viaje->fresh(['pasajero.user', 'conductor.user'])
        );

        // 4. Redirigir al conductor a su historial, dashboard o solicitudes con un mensaje de éxito
        return redirect()
            ->route('conductor.solicitudes') 
            ->with('mensaje', '¡Viaje terminado con éxito! Buen trabajo.');
    }

    public function cancelarViaje(Request $request)
    {
        $request->validate([
            'id_viaje' => 'required|integer',
        ]);

        $viaje = $this->validarViajeConductor(
            (int) $request->id_viaje,
            ['aceptado', 'recogiendo', 'en_curso']
        );

        $viaje->update([
            'estado_viaje' => 'cancelado',
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

        return redirect()
            ->route('conductor.solicitudes');
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

    public function recargarSaldo(Request $request) 
    {
        $request->validate([
            'monto' => 'required|numeric|min:5|max:500',
            'metodo_recarga' => 'required|in:yape,plin,efectivo',
            'referencia' => 'nullable|string|max:150',
        ]);

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

    public function actualizarUbicacion(Request $request) 
    {
        $request->validate([
            'viaje_id' => 'required|integer',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $viaje = Viaje::find((int) $request->viaje_id);

        if (! $viaje) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Viaje no encontrado',
            ], 404);
        }

        if ((int) $viaje->id_conductor !== (int) Auth::id()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No tienes permiso para actualizar este viaje',
            ], 403);
        }

        if (! in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true)) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El estado del viaje no permite actualizar ubicacion',
            ], 409);
        }

        Conductor::where('id_conductor', Auth::id())->update([
            'lat_actual' => (float) $request->lat,
            'lng_actual' => (float) $request->lng,
            'ubicacion_actualizada_en' => now(),
        ]);
        
        // Dispara evento - Reverb lo manda al mapa del pasajero
        event(new \App\Events\ConductorMovido(
            $request->viaje_id,
            $request->lat,
            $request->lng
        ));

        return response()->json([
            'ok' => true,
            'mensaje' => 'Ubicacion actualizada',
            'lat' => (float) $request->lat,
            'lng' => (float) $request->lng,
        ]);
    }
}
