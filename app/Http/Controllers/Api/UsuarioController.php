<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends BaseApiController
{
    public function index()
    {
        // esto es de Validacion BOLA IDOR
        return $this->respuestaJson(User::where('id_usuario', Auth::id())->get());
    }

    public function show(int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorJson('Usuario no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if ((int) $user->id_usuario !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para ver este usuario', 403);
        }

        return $this->respuestaJson($user);
    }

    public function store(Request $request)
    {
        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());

        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'email' => 'required|email|unique:usuarios,email',
            'dni' => 'required|string|min:6|unique:usuarios,dni',
            'password' => 'required|string|min:8',
            'tipo_usuario' => 'required|in:pasajero,conductor',
        ]);

        $user = User::create([
            'nombre_completo' => $this->limpiarTexto($request->nombre_completo),
            'apellidos' => $this->limpiarTexto($request->apellidos),
            'dni' => preg_replace('/\D/', '', $request->dni),
            'email' => $request->email,
            'telefono' => $this->limpiarTexto($request->telefono),
            'contrasena_hash' => Hash::make($request->password),
            'tipo_usuario' => $request->tipo_usuario,
            'activo' => 1,
        ]);

        return $this->respuestaJson($user, 201);
    }

    public function update(Request $request, int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorJson('Usuario no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if ((int) $user->id_usuario !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para modificar este usuario', 403);
        }

        // esto es de Refactorizar Api a ApiController
        $request->merge($this->leerJsonInput());
        $request->validate([
            'nombre_completo' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|nullable|string|max:150',
            'telefono' => 'sometimes|nullable|string|max:30',
        ]);

        $datos = [];
        foreach (['nombre_completo', 'apellidos', 'telefono'] as $campo) {
            if ($request->has($campo)) {
                $datos[$campo] = $this->limpiarTexto($request->input($campo));
            }
        }
        $user->update($datos);

        return $this->respuestaJson($user);
    }

    public function destroy(int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorJson('Usuario no encontrado', 404);
        }

        // esto es de Validacion BOLA IDOR
        if ((int) $user->id_usuario !== (int) Auth::id()) {
            return $this->errorJson('No tienes permiso para desactivar este usuario', 403);
        }

        $user->update(['activo' => 0]);

        return $this->respuestaJson(['mensaje' => 'Usuario desactivado correctamente']);
    }
}
