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
            'foto_perfil' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
        ], [
            'foto_perfil.required' => 'Debe seleccionar una fotografía.',
            'foto_perfil.image' => 'El archivo debe ser una imagen válida.',
            'foto_perfil.mimes' => 'Solo se permiten imágenes JPG o PNG.',
            'foto_perfil.max' => 'La imagen no debe superar los 2 MB.',
        ]);

        $archivo = $request->file('foto_perfil');

        if (!$this->tieneFirmaDeImagenValida($archivo->getRealPath())) {
            $errores = [
                'foto_perfil' => [
                    'La firma del archivo no corresponde a una imagen JPG o PNG.',
                ],
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El archivo seleccionado no es válido.',
                    'errors' => $errores,
                ], 422);
            }

            return back()->withErrors($errores);
        }

        $user = Auth::user();
        $rutaAnterior = $user->foto_perfil;

        $rutaNueva = $archivo->store('perfiles', 'public');

        $user->update([
            'foto_perfil' => $rutaNueva,
        ]);

        if (
            $rutaAnterior &&
            $rutaAnterior !== $rutaNueva &&
            Storage::disk('public')->exists($rutaAnterior)
        ) {
            Storage::disk('public')->delete($rutaAnterior);
        }

        $fotoUrl = '/storage/' . ltrim($rutaNueva, '/');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Foto de perfil actualizada correctamente.',
                'data' => [
                    'foto_url' => $fotoUrl,
                ],
            ], 200);
        }

        return back()->with(
            'mensaje',
            'Foto de perfil actualizada correctamente.'
        );
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
