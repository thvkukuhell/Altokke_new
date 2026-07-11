<?php
namespace App\Http\Controllers;

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
}
