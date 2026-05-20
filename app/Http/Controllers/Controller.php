<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
abstract class Controller extends BaseController
{
    // En Laravel el redirect y las vistas se manejan diferente
    // Ya no se necesita helpers, Laravel los tiene nativos
    // return view('inicio.inicio')
    // return redirect()->route('nombre')
    // return back()->withErrors([...])
}