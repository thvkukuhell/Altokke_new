<?php
namespace app\Http\Controllers;

class InicioController extends Controller
{
    public function index()
    {
        return view('inicio.inicio');
    }

    public function como_funciona()
    {
        return view('inicio.como_funciona');
    }

    public function sobre_nosotros()
    {
        return view('inicio.sobre_nosotros');
    }
}