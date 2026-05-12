<?php

namespace app\Http\Controllers;

use App\Models\Viaje;
use App\Models\Pasajero;
use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Recibe peticiones HTTP, valida datos, llama al DAO y carga la vista correspondiente

class PasajeroController extends Controller {
    // Middleware en el constructor 
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->tipo_usuario !== 'pasajero') {
                return redirect()->route('inicio');
            }
            return $next($request);
        });
    }

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
        $viajeRaw = Viaje::with('conductor.user', 'conductor.vehiculo')
                         ->find($viajeId);

        // igual que antes pero con Eloquent
        return view('pasajero.viaje_en_curso', compact('viaje', 'conductor', 'iniciales', 'eta', 'pasos'));
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
        $query   = Viaje::where('id_pasajero', Auth::id());

        $query = match($filtro) {
            'hoy'    => $query->whereDate('fecha_solicitud', today()),
            'semana' => $query->whereBetween('fecha_solicitud', [now()->startOfWeek(), now()->endOfWeek()]),
            'mes'    => $query->whereMonth('fecha_solicitud', now()->month),
            default  => $query,
        };

        $viajesRaw = $query->orderByDesc('fecha_solicitud')->get();

        $viajes = $viajesRaw->map(function ($v) {
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

        return view('pasajero.historial', compact('viajes', 'filtro', 'filtros'));
    }

    public function perfil()
    {
        $user     = Auth::user();
        $pasajero = $user->pasajero;
        $iniciales = $this->calcularIniciales($user->nombre_completo);

        return view('pasajero.perfil', compact('user', 'pasajero', 'iniciales'));
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
}