<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function index()
    {
        return response()->json(Calificacion::with('viaje')->get(), 200);
    }

    public function show(int $id)
    {
        $calificacion = Calificacion::with('viaje')->find($id);

        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        return response()->json($calificacion, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_viaje'   => 'required|integer|exists:viajes,id_viaje',
            'puntuacion' => 'required|numeric|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $calificacion = Calificacion::updateOrCreate(
            ['id_viaje' => $request->id_viaje],
            [
                'puntuacion' => $request->puntuacion,
                'comentario' => $request->comentario ?? '',
            ]
        );

        return response()->json($calificacion, 201);
    }

    public function destroy(int $id)
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        $calificacion->delete();

        return response()->json(['mensaje' => 'Calificación eliminada correctamente'], 200);
    }
}
