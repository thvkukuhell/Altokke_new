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

        $archivo = $request->file('foto_perfil');

        if (!$this->tieneFirmaDeImagenValida($archivo->getRealPath())) {
            return back()->withErrors([
                'foto_perfil' => 'El archivo no es una imagen valida.',
            ]);
        }

        $user = Auth::user();
        $ruta = $archivo->store('perfiles', 'public');

        if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $user->update([
            'foto_perfil' => $ruta,
        ]);

        return back()->with('mensaje', 'Foto de perfil actualizada correctamente.');
    }

    private function tieneFirmaDeImagenValida(string $rutaTemporal): bool
    {
        $handle = fopen($rutaTemporal, 'rb');

        if (!$handle) {
            return false;
        }

        $cabecera = fread($handle, 8);
        fclose($handle);

        if ($cabecera === false) {
            return false;
        }

        $esJpg = str_starts_with($cabecera, "\xFF\xD8\xFF");
        $esPng = str_starts_with($cabecera, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A");

        return $esJpg || $esPng;
    }
}
