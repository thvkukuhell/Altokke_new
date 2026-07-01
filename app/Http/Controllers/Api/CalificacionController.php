<?php

namespace App\Http\Controllers\Api;

use App\Models\Calificacion;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalificacionController extends BaseApiController
{
    public function index()
    {
        // esto es de Validacion BOLA IDOR
        $calificaciones = Calificacion::with('viaje')
            ->whereHas('viaje', function ($query) {
                if (Auth::user()->tipo_usuario === 'pasajero') {
                    $query->where('id_pasajero', Auth::id());
                } else {
                    $query->where('id_conductor', Auth::id());
                }
            })
            ->get();

        return $this->respuestaJson($calificaciones);
    }

    public function show(int $id)
    {
        $calificacion = Calificacion::with('viaje')->find($id);

        if (! $calificacion) {
            return $this->errorJson('Calificacion no encontrada', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (! $this->usuarioPuedeAcceder($calificacion->viaje)) {
            return $this->errorJson('No tienes permiso para ver esta calificacion', 403);
        }

        return $this->respuestaJson($calificacion);
    }

    public function store(Request $request)
    {
        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());
        $request->validate([
            'id_viaje' => 'required|integer|exists:viajes,id_viaje',
            'puntuacion' => 'required|numeric|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $viaje = Viaje::find($request->id_viaje);

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || ! $viaje
            || (int) $viaje->id_pasajero !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para calificar este viaje', 403);
        }

        // esto es de Seguridad de Endpoints
        if ($viaje->estado_viaje !== 'completado' || ! $viaje->id_conductor) {
            return $this->errorJson('Solo puedes calificar un viaje completado', 422);
        }

        $calificacion = Calificacion::updateOrCreate(
            ['id_viaje' => $request->id_viaje],
            [
                'puntuacion' => $request->puntuacion,
                'comentario' => $this->limpiarTexto($request->comentario),
            ]
        );

        return $this->respuestaJson($calificacion, 201);
    }

    public function destroy(int $id)
    {
        $calificacion = Calificacion::with('viaje')->find($id);

        if (! $calificacion) {
            return $this->errorJson('Calificacion no encontrada', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || ! $this->usuarioPuedeAcceder($calificacion->viaje)) {
            return $this->errorJson('No tienes permiso para eliminar esta calificacion', 403);
        }

        $calificacion->delete();

        return $this->respuestaJson(['mensaje' => 'Calificacion eliminada correctamente']);
    }

    private function usuarioPuedeAcceder(?Viaje $viaje): bool
    {
        if (! $viaje) {
            return false;
        }

        if (Auth::user()->tipo_usuario === 'pasajero') {
            return (int) $viaje->id_pasajero === (int) Auth::id();
        }

        if (Auth::user()->tipo_usuario === 'conductor') {
            return (int) $viaje->id_conductor === (int) Auth::id();
        }

        return false;
    }
}
