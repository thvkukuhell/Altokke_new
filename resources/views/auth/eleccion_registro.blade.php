@extends('layouts.main')
@section('content')

<div class="auth-layout">

  {{-- ── Banner ── --}}
  <div class="auth-banner">
    <div class="auth-banner-logo">Alto<span>kke</span></div>
    <div class="auth-banner-cuerpo">
      <h2>Únete a<br>Altokke.</h2>
      <p>Crea tu cuenta gratis en menos de 1 minuto y empieza a disfrutar del mejor servicio de mototaxi en Bagua.</p>
    </div>
    <div class="auth-banner-stats">
      <div class="auth-banner-stat">
        <div class="num">Gratis</div>
        <div class="lbl">Sin costos ocultos</div>
      </div>
      <div class="auth-banner-stat">
        <div class="num">&lt;1 min</div>
        <div class="lbl">Para registrarte</div>
      </div>
    </div>
  </div>

  {{-- ── Panel ── --}}
  <div class="auth-panel">

    <div class="auth-panel-logo">Alto<span>kke</span></div>

    <h1 class="auth-titulo">Crea tu cuenta</h1>
    <p class="auth-sub">Elige cómo quieres usar la plataforma</p>

    <div class="eleccion-grid">
      <a href="{{ route('registro_pasajero') }}" class="tarjeta-rol">
        <div class="tarjeta-rol-icono">🧑‍💼</div>
        <h2>Soy pasajero</h2>
        <p>Quiero solicitar viajes rápidos y seguros</p>
        <span>Registrarme →</span>
      </a>
      <a href="{{ route('registro_conductor') }}" class="tarjeta-rol">
        <div class="tarjeta-rol-icono">🏍️</div>
        <h2>Soy conductor</h2>
        <p>Quiero ganar dinero con mi mototaxi</p>
        <span>Registrarme →</span>
      </a>
    </div>

    <p class="auth-link-texto" style="margin-top:28px;">
      ¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
    </p>

  </div>

</div>

@endsection