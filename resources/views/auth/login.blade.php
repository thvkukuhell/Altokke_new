@extends('layouts.main')
@section('contenido')

<main>
    <div class="inicio">
        <h1>¿Ya tienes cuenta?</h1>
        <p>Inicia sesión o regístrate gratis</p>
    </div>
 
    <form class="formulario-login"
          action="{{ route('login.proceso') }}"
          method="POST"
          autocomplete="on"
          novalidate>
 
        @csrf
 
        <h2>Iniciar sesión</h2>
 
        <label for="email">Correo electrónico</label>
        <input required
               type="email"
               id="email"
               name="email"
               value="{{ old('email') }}">
 
        <label for="password">Contraseña</label>
        <input required
               type="password"
               id="password"
               name="password">
 
        <button type="submit">Entrar</button>
 
        <p class="texto-registro">
            ¿No tienes cuenta?
            <a href="{{ route('eleccion_registro') }}">Regístrate aquí</a>
        </p>
 
    </form>
 
    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="form-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
 
</main>

@endsection