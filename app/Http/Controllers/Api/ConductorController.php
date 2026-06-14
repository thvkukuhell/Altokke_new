<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use Illuminate\Http\Request;

class ConductorController extends Controller
{
    public function index()
    {
        $conductores = Conductor::with(['user', 'vehiculo'])->get();

        return response()->json($conductores, 200);
    }

    public function show(int $id)
    {
        $conductor = Conductor::with(['user', 'vehiculo', 'viajes'])->find($id);

        if (!$conductor) {
            return response()->json(['error' => 'Conductor no encontrado'], 404);
        }

        return response()->json($conductor, 200);
    }

    public function update(Request $request, int $id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['error' => 'Conductor no encontrado'], 404);
        }

        $request->validate([
            'estado_conductor'       => 'sometimes|in:activo,inactivo,en_verificacion',
            'saldo_disponible'       => 'sometimes|numeric|min:0',
            'calificacion_promedio'  => 'sometimes|numeric|min:0|max:5',
        ]);

        $conductor->update($request->only([
            'estado_conductor',
            'saldo_disponible',
            'calificacion_promedio',
        ]));

        return response()->json($conductor, 200);
    }

    public function destroy(int $id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['error' => 'Conductor no encontrado'], 404);
        }

        $conductor->update(['estado_conductor' => 'inactivo']);
        $conductor->user?->update(['activo' => 0]);

        return response()->json(['mensaje' => 'Conductor desactivado correctamente'], 200);
    }
}
