<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasajero;
use App\Models\Viaje;
use Illuminate\Http\Request;

class PasajeroController extends Controller
{
    public function index()
    {
        $pasajeros = Pasajero::with('user')->get();

        return response()->json($pasajeros, 200);
    }

    public function show(int $id)
    {
        $pasajero = Pasajero::with(['user', 'viajes'])->find($id);

        if (!$pasajero) {
            return response()->json(['error' => 'Pasajero no encontrado'], 404);
        }

        return response()->json($pasajero, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pasajero'           => 'required|integer|exists:usuarios,id_usuario|unique:pasajeros,id_pasajero',
            'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
        ]);

        $pasajero = Pasajero::create([
            'id_pasajero'           => $request->id_pasajero,
            'metodo_pago_preferido' => $request->metodo_pago_preferido,
        ]);

        return response()->json($pasajero->load('user'), 201);
    }

    public function update(Request $request, int $id)
    {
        $pasajero = Pasajero::find($id);

        if (!$pasajero) {
            return response()->json(['error' => 'Pasajero no encontrado'], 404);
        }

        $request->validate([
            'metodo_pago_preferido' => 'required|in:efectivo,yape,plin',
        ]);

        $pasajero->update([
            'metodo_pago_preferido' => $request->metodo_pago_preferido,
        ]);

        return response()->json($pasajero->load('user'), 200);
    }

    public function destroy(int $id)
    {
        $pasajero = Pasajero::find($id);

        if (!$pasajero) {
            return response()->json(['error' => 'Pasajero no encontrado'], 404);
        }

        $pasajero->user?->update(['activo' => 0]);
        Viaje::where('id_pasajero', $id)
            ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
            ->update(['estado_viaje' => 'cancelado']);

        return response()->json(['mensaje' => 'Pasajero desactivado correctamente'], 200);
    }

    public function viajes(int $id)
    {
        $pasajero = Pasajero::find($id);

        if (!$pasajero) {
            return response()->json(['error' => 'Pasajero no encontrado'], 404);
        }

        $viajes = Viaje::where('id_pasajero', $id)
            ->with(['conductor.user', 'calificacion'])
            ->orderByDesc('fecha_solicitud')
            ->get();

        return response()->json($viajes, 200);
    }
}
