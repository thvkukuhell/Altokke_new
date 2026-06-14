<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    public function show(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'email'           => 'required|email|unique:usuarios,email',
            'dni'             => 'required|string|min:6|unique:usuarios,dni',
            'password'        => 'required|string|min:8',
            'tipo_usuario'    => 'required|in:pasajero,conductor',
        ]);

        $user = User::create([
            'nombre_completo' => $request->nombre_completo,
            'apellidos'       => $request->apellidos,
            'dni'             => preg_replace('/\D/', '', $request->dni),
            'email'           => $request->email,
            'telefono'        => $request->telefono,
            'contrasena_hash' => Hash::make($request->password),
            'tipo_usuario'    => $request->tipo_usuario,
            'activo'          => 1,
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $user->update($request->only([
            'nombre_completo',
            'apellidos',
            'telefono',
        ]));

        return response()->json($user, 200);
    }

    public function destroy(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $user->update(['activo' => 0]);

        return response()->json(['mensaje' => 'Usuario desactivado correctamente'], 200);
    }
}
