@extends('layouts.main')
@section('content')

<div class="auth-layout">

  {{-- ── Banner ── --}}
  <div class="auth-banner">
    <div class="auth-banner-logo">Alto<span>kke</span></div>
    <div class="auth-banner-cuerpo">
      <h2>Viaja más<br>cómodo.</h2>
      <p>Únete a cientos de pasajeros que ya disfrutan del servicio de mototaxi más rápido y seguro de Bagua.</p>
    </div>
    <div class="auth-banner-stats">
      <div class="auth-banner-stat">
        <div class="num">Gratis</div>
        <div class="lbl">Siempre gratuito</div>
      </div>
      <div class="auth-banner-stat">
        <div class="num">24/7</div>
        <div class="lbl">Siempre disponible</div>
      </div>
    </div>
  </div>

  {{-- ── Panel ── --}}
  <div class="auth-panel">

    <div class="auth-panel-logo">Alto<span>kke</span></div>

    <h1 class="auth-titulo">Cuenta de pasajero</h1>
    <p class="auth-sub">En menos de 1 minuto y totalmente gratis</p>

    @if ($errors->any())
      <div class="auth-errores">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('proc_regist_pasajero') }}" method="POST" autocomplete="on" novalidate>
      @csrf

      <div class="auth-grid-2">
        <div class="auth-campo">
          <label>Nombre</label>
          <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Tu nombre" required>
        </div>
        <div class="auth-campo">
          <label>Apellidos</label>
          <input type="text" name="apellidos" value="{{ old('apellidos') }}" placeholder="Tus apellidos" required>
        </div>
        <div class="auth-campo">
          <label>DNI</label>
          <input type="text" name="dni" value="{{ old('dni') }}" placeholder="12345678" minlength="8" required>
        </div>
        <div class="auth-campo">
          <label>Teléfono</label>
          <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="9XX XXX XXX" required>
        </div>
      </div>

      <div class="auth-campo">
        <label>Correo electrónico</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="tu@correo.com" required>
      </div>

      <div class="auth-grid-2">
        <div class="auth-campo">
          <label>Contraseña</label>
          <input type="password" name="password" placeholder="Mínimo 8 caracteres" required>
        </div>
        <div class="auth-campo">
          <label>Confirmar contraseña</label>
          <input type="password" name="password_confirmation" placeholder="Repite la contraseña" required>
        </div>
      </div>

      <button type="submit" class="btn-auth">
        Crear cuenta de pasajero
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </button>

    </form>

    <p class="auth-link-texto">
      ¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
    </p>

  </div>

</div>

@endsection