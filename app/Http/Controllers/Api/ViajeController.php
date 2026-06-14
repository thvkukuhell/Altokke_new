<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Viaje;
use Illuminate\Http\Request;

class ViajeController extends Controller
{
    public function index()
    {
        $viajes = Viaje::with(['pasajero.user', 'conductor.user'])->get();

        return response()->json($viajes, 200);
    }

    public function show(int $id)
    {
        $viaje = Viaje::with(['pasajero.user', 'conductor.user', 'calificacion'])->find($id);

        if (!$viaje) {
            return response()->json(['error' => 'Viaje no encontrado'], 404);
        }

        return response()->json($viaje, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pasajero'   => 'required|integer|exists:pasajeros,id_pasajero',
            'origen_texto'  => 'required|string',
            'destino_texto' => 'required|string|different:origen_texto',
            'tipo_servicio' => 'required|in:normal,express',
            'metodo_pago'   => 'required|in:efectivo,yape,plin',
        ]);

        $viaje = Viaje::create([
            'id_pasajero'     => $request->id_pasajero,
            'origen_texto'    => $request->origen_texto,
            'destino_texto'   => $request->destino_texto,
            'tarifa_estimada' => 3.00,
            'tipo_servicio'   => $request->tipo_servicio,
            'metodo_pago'     => $request->metodo_pago,
            'estado_viaje'    => 'buscando',
        ]);

        return response()->json($viaje, 201);
    }

    public function update(Request $request, int $id)
    {
        $viaje = Viaje::find($id);

        if (!$viaje) {
            return response()->json(['error' => 'Viaje no encontrado'], 404);
        }

        $request->validate([
            'estado_viaje' => 'sometimes|in:buscando,aceptado,recogiendo,en_curso,completado,cancelado',
            'id_conductor' => 'sometimes|integer|exists:conductores,id_conductor',
            'tarifa_final' => 'sometimes|numeric',
        ]);

        $viaje->update($request->only([
            'estado_viaje',
            'id_conductor',
            'tarifa_final',
        ]));

        return response()->json($viaje, 200);
    }

    public function destroy(int $id)
    {
        $viaje = Viaje::find($id);

        if (!$viaje) {
            return response()->json(['error' => 'Viaje no encontrado'], 404);
        }

        $viaje->update(['estado_viaje' => 'cancelado']);

        return response()->json(['mensaje' => 'Viaje cancelado correctamente'], 200);
    }
}
