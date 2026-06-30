<?php

namespace App\Http\Controllers\Api;

use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViajeController extends BaseApiController
{
    public function index()
    {
        // esto es de Validacion BOLA IDOR
        $consulta = Viaje::with(['pasajero.user', 'conductor.user']);

        if (Auth::user()->tipo_usuario === 'pasajero') {
            $consulta->where('id_pasajero', Auth::id());
        } elseif (Auth::user()->tipo_usuario === 'conductor') {
            $consulta->where('id_conductor', Auth::id());
        } else {
            return $this->errorJson('Tipo de usuario no autorizado', 403);
        }

        return $this->respuestaJson($consulta->orderByDesc('fecha_solicitud')->get());
    }

    public function show(int $id)
    {
        $viaje = Viaje::with(['pasajero.user', 'conductor.user', 'calificacion'])->find($id);

        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (! $this->usuarioPuedeAcceder($viaje)) {
            return $this->errorJson('No tienes permiso para ver este viaje', 403);
        }

        return $this->respuestaJson($viaje);
    }

    public function store(Request $request)
    {
        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());
        $request->merge(['id_pasajero' => Auth::id()]);

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero') {
            return $this->errorJson('Solo pasajeros pueden crear viajes', 403);
        }

        $request->validate([
            'id_pasajero' => 'required|integer|exists:pasajeros,id_pasajero',
            'origen_texto' => 'required|string',
            'destino_texto' => 'required|string|different:origen_texto',
            'tipo_servicio' => 'required|in:normal,express',
            'metodo_pago' => 'required|in:efectivo,yape,plin',
        ]);

        $viaje = Viaje::create([
            'id_pasajero' => Auth::id(),
            'origen_texto' => $this->limpiarTexto($request->origen_texto),
            'destino_texto' => $this->limpiarTexto($request->destino_texto),
            'tarifa_estimada' => 3.00,
            'tipo_servicio' => $request->tipo_servicio,
            'metodo_pago' => $request->metodo_pago,
            'estado_viaje' => 'buscando',
        ]);

        return $this->respuestaJson($viaje, 201);
    }

    public function update(Request $request, int $id)
    {
        $viaje = Viaje::find($id);

        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (! $this->usuarioPuedeAcceder($viaje)) {
            return $this->errorJson('No tienes permiso para modificar este viaje', 403);
        }

        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());
        $request->validate([
            'estado_viaje' => 'sometimes|in:buscando,aceptado,recogiendo,en_curso,completado,cancelado',
            'tarifa_final' => 'sometimes|numeric|min:0',
        ]);

        if (Auth::user()->tipo_usuario === 'pasajero') {
            if ($request->input('estado_viaje') !== 'cancelado') {
                return $this->errorJson('El pasajero solo puede cancelar su viaje', 403);
            }

            $viaje->update(['estado_viaje' => 'cancelado']);
        } else {
            $datos = $request->only(['estado_viaje', 'tarifa_final']);
            $datos['id_conductor'] = Auth::id();
            $viaje->update($datos);
        }

        return $this->respuestaJson($viaje);
    }

    public function destroy(int $id)
    {
        $viaje = Viaje::find($id);

        if (! $viaje) {
            return $this->errorJson('Viaje no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (! $this->usuarioPuedeAcceder($viaje)) {
            return $this->errorJson('No tienes permiso para cancelar este viaje', 403);
        }

        $viaje->update(['estado_viaje' => 'cancelado']);

        return $this->respuestaJson(['mensaje' => 'Viaje cancelado correctamente']);
    }

    private function usuarioPuedeAcceder(Viaje $viaje): bool
    {
        if (Auth::user()->tipo_usuario === 'pasajero') {
            return (int) $viaje->id_pasajero === (int) Auth::id();
        }

        if (Auth::user()->tipo_usuario === 'conductor') {
            return (int) $viaje->id_conductor === (int) Auth::id();
        }

        return false;
    }
}
