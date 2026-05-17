@extends('layouts.main')
@section('content')

<div class="auth-layout">

  {{-- ── Banner lateral ── --}}
  <div class="auth-banner">
    <div class="auth-banner-logo">Alto<span>kke</span></div>

    <div class="auth-banner-cuerpo">
      <h2>Bienvenido<br>de vuelta.</h2>
      <p>Accede a tu cuenta y pide tu mototaxi en segundos. Conductores verificados esperando en Bagua.</p>
    </div>

    <div class="auth-banner-stats">
      <div class="auth-banner-stat">
        <div class="num">+200</div>
        <div class="lbl">Conductores activos</div>
      </div>
      <div class="auth-banner-stat">
        <div class="num">4.8★</div>
        <div class="lbl">Calificación promedio</div>
      </div>
      <div class="auth-banner-stat">
        <div class="num">&lt;5 min</div>
        <div class="lbl">Tiempo de espera</div>
      </div>
    </div>
  </div>

  {{-- ── Panel formulario ── --}}
  <div class="auth-panel">

    <div class="auth-panel-logo">Alto<span>kke</span></div>

    <h1 class="auth-titulo">Iniciar sesión</h1>
    <p class="auth-sub">¿No tienes cuenta?
      <a href="{{ route('eleccion_registro') }}" style="color:var(--verde-mid); font-weight:700;">Regístrate gratis</a>
    </p>

    @if ($errors->any())
      <div class="auth-errores">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('login.proceso') }}" method="POST" autocomplete="on" novalidate>
      @csrf

      <div class="auth-campo">
        <label for="email">Correo electrónico</label>
        <input type="email"
               id="email"
               name="email"
               value="{{ old('email') }}"
               placeholder="tu@correo.com"
               required>
      </div>

      <div class="auth-campo">
        <label for="password">Contraseña</label>
        <input type="password"
               id="password"
               name="password"
               placeholder="••••••••"
               required>
      </div>

      <button type="submit" class="btn-auth">
        Entrar
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </button>

    </form>

    <p class="auth-link-texto">
      ¿Olvidaste tu contraseña? <a href="#">Recuperar acceso</a>
    </p>

  </div>

</div>

@endsection