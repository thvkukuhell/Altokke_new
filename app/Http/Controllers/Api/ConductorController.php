<?php

namespace App\Http\Controllers\Api;

use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConductorController extends BaseApiController
{
    public function index()
    {
        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'conductor') {
            return $this->errorJson('Solo conductores pueden ver estos datos', 403);
        }

        $conductores = Conductor::with(['user', 'vehiculo'])
            ->where('id_conductor', Auth::id())
            ->get();

        return $this->respuestaJson($conductores);
    }

    public function show(int $id)
    {
        $conductor = Conductor::with(['user', 'vehiculo', 'viajes'])->find($id);

        if (! $conductor) {
            return $this->errorJson('Conductor no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'conductor'
            || (int) $conductor->id_conductor !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para ver este conductor', 403);
        }

        return $this->respuestaJson($conductor);
    }

    public function update(Request $request, int $id)
    {
        $conductor = Conductor::find($id);

        if (! $conductor) {
            return $this->errorJson('Conductor no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'conductor'
            || (int) $conductor->id_conductor !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para modificar este conductor', 403);
        }

        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());
        $request->validate([
            'estado_conductor' => 'sometimes|in:activo,inactivo,en_verificacion',
            'saldo_disponible' => 'sometimes|numeric|min:0',
            'calificacion_promedio' => 'sometimes|numeric|min:0|max:5',
        ]);

        $conductor->update($request->only([
            'estado_conductor',
            'saldo_disponible',
            'calificacion_promedio',
        ]));

        return $this->respuestaJson($conductor);
    }

    public function destroy(int $id)
    {
        $conductor = Conductor::find($id);

        if (! $conductor) {
            return $this->errorJson('Conductor no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if (Auth::user()->tipo_usuario !== 'conductor'
            || (int) $conductor->id_conductor !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para desactivar este conductor', 403);
        }

        $conductor->update(['estado_conductor' => 'inactivo']);
        $conductor->user?->update(['activo' => 0]);

        return $this->respuestaJson(['mensaje' => 'Conductor desactivado correctamente']);
    }
}
