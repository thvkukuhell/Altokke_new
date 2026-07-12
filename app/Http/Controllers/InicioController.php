<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnviarConsultaRequest;
use App\Mail\SolicitudContactoMail;
use App\Models\SolicitudContacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InicioController extends Controller
{
    public function index()
    {
        return view('inicio.inicio', [
            'css' => ['inicio/inicio.css'],
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
        ]);
    }

    public function como_funciona()
    {
        return redirect()->to(route('inicio') . '#como-funciona');
    }

    public function sobre_nosotros()
    {
        return redirect()->to(route('inicio') . '#sobre-nosotros');
    }

    public function servicios()
    {
        return view('inicio.servicios', [
            'css' => ['inicio/inicio.css'],
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
        ]);
    }

    public function contacto()
    {
        return view('inicio.contacto', [
            'css' => ['inicio/inicio.css'],
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
        ]);
    }

    public function ayuda()
    {
        return view('inicio.ayuda', [
            'css' => ['inicio/inicio.css'],
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
        ]);
    }

    public function enviarConsulta(EnviarConsultaRequest $request)
    {
        $data = $request->validated();

        $userId = auth()->check() ? auth()->id() : null;
        SolicitudContacto::create(array_merge($data, ['id_usuario' => $userId]));

        try {
            Mail::to(config('app.support_email'))->send(new SolicitudContactoMail($data));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo de contacto.', [
                'correo' => $data['correo'],
                'tipo_solicitud' => $data['tipo_solicitud'],
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Gracias. Tu solicitud ha sido enviada. Nos comunicaremos contigo pronto.');
    }
}
