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
    private function layoutPublico(array $extra = [], string $footer = 'footer'): array
    {
        $user = auth()->user();
        $header = match ($user?->tipo_usuario) {
            'pasajero' => 'header_pasajero',
            'conductor' => 'header_conductor',
            default => 'header_inicio',
        };

        return array_merge([
            'css' => ['inicio/inicio.css'],
            'header' => $header,
            'footer' => $user ? 'footer' : $footer,
        ], $extra);
    }

    public function index()
    {
        return view('inicio.inicio', $this->layoutPublico(footer: 'footer_inicio'));
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
        return view('inicio.servicios', $this->layoutPublico());
    }

    public function contacto()
    {
        return view('inicio.contacto', $this->layoutPublico());
    }

    public function ayuda()
    {
        return view('inicio.ayuda', $this->layoutPublico());
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
