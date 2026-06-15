<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PerfilArchivoController extends Controller
{
    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto_perfil' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $archivo = $request->file('foto_perfil');
        $ruta = $archivo->store('perfiles', 'public');

        if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $user->update([
            'foto_perfil' => $ruta,
        ]);

        return back()->with('mensaje', 'Foto de perfil actualizada correctamente.');
    }
}
