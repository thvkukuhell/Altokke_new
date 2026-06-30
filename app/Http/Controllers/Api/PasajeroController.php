<?php

namespace App\Http\Controllers\Api;

use App\Models\Pasajero;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasajeroController extends BaseApiController
{
    public function index()
    {
        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero') {
            return $this->errorJson('Solo pasajeros pueden ver estos datos', 403);
        }

        $pasajeros = Pasajero::with('user')
            ->where('id_pasajero', Auth::id())
            ->get();

        return $this->respuestaJson($pasajeros);
    }

    public function show(int $id)
    {
        $pasajero = Pasajero::with(['user', 'viajes'])->find($id);

        if (! $pasajero) {
            return $this->errorJson('Pasajero no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || (int) $pasajero->id_pasajero !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para ver este pasajero', 403);
        }

        return $this->respuestaJson($pasajero);
    }

    public function store(Request $request)
    {
        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());

        $request->validate([
            'id_pasajero' => 'required|integer|exists:usuarios,id_usuario|unique:pasajeros,id_pasajero',
            'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
        ]);

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || (int) $request->id_pasajero !== (int) Auth::id()) {
            return $this->errorJson('Solo puedes crear tus propios datos de pasajero', 403);
        }

        $pasajero = Pasajero::create([
            'id_pasajero' => $request->id_pasajero,
            'metodo_pago_preferido' => $request->metodo_pago_preferido,
        ]);

        return $this->respuestaJson($pasajero->load('user'), 201);
    }

    public function update(Request $request, int $id)
    {
        $pasajero = Pasajero::find($id);

        if (! $pasajero) {
            return $this->errorJson('Pasajero no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || (int) $pasajero->id_pasajero !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para modificar este pasajero', 403);
        }

        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());
        $request->validate([
            'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
        ]);

        $pasajero->update([
            'metodo_pago_preferido' => $request->metodo_pago_preferido,
        ]);

        return $this->respuestaJson($pasajero->load('user'));
    }

    public function destroy(int $id)
    {
        $pasajero = Pasajero::find($id);

        if (! $pasajero) {
            return $this->errorJson('Pasajero no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || (int) $pasajero->id_pasajero !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para desactivar este pasajero', 403);
        }

        $pasajero->user?->update(['activo' => 0]);
        Viaje::where('id_pasajero', $id)
            ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
            ->update(['estado_viaje' => 'cancelado']);

        return $this->respuestaJson(['mensaje' => 'Pasajero desactivado correctamente']);
    }

    public function viajes(int $id)
    {
        $pasajero = Pasajero::find($id);

        if (! $pasajero) {
            return $this->errorJson('Pasajero no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'pasajero'
            || (int) $pasajero->id_pasajero !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para ver estos viajes', 403);
        }

        $viajes = Viaje::where('id_pasajero', $id)
            ->with(['conductor.user', 'calificacion'])
            ->orderByDesc('fecha_solicitud')
            ->get();

        return $this->respuestaJson($viajes);
    }
}
