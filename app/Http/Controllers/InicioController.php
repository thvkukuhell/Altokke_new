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
        return view('inicio.como_funciona', [
            'css' => ['inicio/como_funciona.css'],
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
        ]);
    }

    public function sobre_nosotros()
    {
        return view('inicio.sobre_nosotros', [
            'css' => ['inicio/sobre_nosotros.css'],
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
        ]);
    }
}