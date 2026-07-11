<?php
namespace App\Http\Controllers;

use App\Mail\SolicitudContactoMail;
use App\Models\SolicitudContacto;
use Illuminate\Http\Request;
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

    public function enviarConsulta(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'correo' => 'required|email|max:150',
            'asunto' => 'required|string|max:150',
            'tipo_solicitud' => 'required|in:consulta,reclamo,sugerencia,reporte',
            'descripcion' => 'required|string|max:1200',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'asunto.required' => 'El asunto es obligatorio.',
            'tipo_solicitud.required' => 'Selecciona el tipo de solicitud.',
            'descripcion.required' => 'Ingresa una descripción del problema o reclamo.',
        ]);

        $data = $request->only(['nombre', 'correo', 'asunto', 'tipo_solicitud', 'descripcion']);

        $userId = auth()->check() ? auth()->id() : null;
        SolicitudContacto::create(array_merge($data, ['id_usuario' => $userId]));

        Mail::to(config('app.support_email'))
            ->send(new SolicitudContactoMail($data));

        return back()->with('success', 'Gracias. Tu solicitud ha sido enviada. Nos comunicaremos contigo pronto.');
    }
}
