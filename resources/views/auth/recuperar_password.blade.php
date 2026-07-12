@extends('layouts.main')
@section('content')

@php($esRestablecimiento = isset($token))

<div class="auth-layout">

  <div class="auth-banner">
    <div class="auth-banner-logo">Alto<span>kke</span></div>
    <div class="auth-banner-cuerpo">
      <h2>{{ $esRestablecimiento ? 'Crea una nueva' : 'Recupera tu' }}<br>contraseña.</h2>
      <p>
        {{ $esRestablecimiento
            ? 'Ingresa una nueva contraseña para volver a usar tu cuenta.'
            : 'Te enviaremos un enlace seguro para restablecer el acceso.' }}
      </p>
    </div>
    <div class="auth-banner-stats">
      <div class="auth-banner-stat"><div class="num">+200</div><div class="lbl">Conductores activos</div></div>
      <div class="auth-banner-stat"><div class="num">4.8</div><div class="lbl">Calificación promedio</div></div>
      <div class="auth-banner-stat"><div class="num">&lt;5 min</div><div class="lbl">Tiempo de espera</div></div>
    </div>
  </div>

  <div class="auth-panel">

    <div class="auth-panel-logo">Alto<span>kke</span></div>

    <h1 class="auth-titulo">{{ $esRestablecimiento ? 'Nueva contraseña' : 'Restablecer contraseña' }}</h1>
    <p class="auth-sub">
      ¿Recordaste tu contraseña?
      <a href="{{ route('login') }}" style="color:var(--verde-mid); font-weight:700;">Iniciar sesión</a>
    </p>

    @if (session('exito'))
      <div class="auth-errores" style="background:rgba(34,197,94,.15); border-color:#22c55e; color:#22c55e;">
        <ul><li>{{ session('exito') }}</li></ul>
      </div>
    @endif

    @if ($errors->any())
      <div class="auth-errores">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if ($esRestablecimiento)
      <form action="{{ route('password.update') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-campo">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email"
                 value="{{ old('email', $email ?? '') }}"
                 placeholder="tu@correo.com" required>
        </div>

        <div class="auth-campo">
          <label for="password">Nueva contraseña</label>
          <input type="password" id="password" name="password"
                 placeholder="********" required>
        </div>

        <div class="auth-campo">
          <label for="password_confirmation">Confirmar contraseña</label>
          <input type="password" id="password_confirmation" name="password_confirmation"
                 placeholder="********" required>
        </div>

        <button type="submit" class="btn-auth">
          Guardar contraseña
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>
      </form>
    @else
      <form action="{{ route('recuperar_password.proceso') }}" method="POST" novalidate>
        @csrf

        <div class="auth-campo">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email"
                 value="{{ old('email') }}"
                 placeholder="tu@correo.com" required>
        </div>

        <button type="submit" class="btn-auth">
          Enviar instrucciones
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>
      </form>
    @endif

    <p class="auth-link-texto">
      ¿No tienes cuenta? <a href="{{ route('eleccion_registro') }}">Regístrate gratis</a>
    </p>

  </div>

</div>

@endsection
