@extends('layouts.main')
@section('content')

<div class="auth-layout">

  {{-- ── Banner ── --}}
  <div class="auth-banner">
    <div class="auth-banner-logo">Alto<span>kke</span></div>
    <div class="auth-banner-cuerpo">
      <h2>Gana dinero<br>conduciendo.</h2>
      <p>Regístrate como conductor en Altokke, establece tu horario y empieza a recibir solicitudes de viaje en Bagua.</p>
    </div>
    <div class="auth-banner-stats">
      <div class="auth-banner-stat">
        <div class="num">Flexible</div>
        <div class="lbl">Tú pones el horario</div>
      </div>
      <div class="auth-banner-stat">
        <div class="num">Verificado</div>
        <div class="lbl">Proceso seguro</div>
      </div>
    </div>
  </div>

  {{-- ── Panel ── --}}
  <div class="auth-panel">

    <div class="auth-panel-logo">Alto<span>kke</span></div>

    <h1 class="auth-titulo">Cuenta de conductor</h1>
    <p class="auth-sub">Completa tus datos en dos pasos rápidos</p>

    {{-- Stepper --}}
    <div class="auth-stepper">
      <div class="auth-step activo" id="step-indicator-1">
        <div class="auth-step-num">1</div>
        <span>Cuenta</span>
      </div>
      <div class="auth-step-linea"></div>
      <div class="auth-step" id="step-indicator-2">
        <div class="auth-step-num">2</div>
        <span>Vehículo</span>
      </div>
    </div>

    @if ($errors->any())
      <div class="auth-errores" id="form-errors">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @else
      <div class="auth-errores" id="form-errors" style="display:none;"></div>
    @endif

    <form action="{{ route('proc_regist_conductor') }}" method="POST" autocomplete="on" novalidate>
      @csrf

      {{-- Paso 1: Cuenta --}}
      <div class="paso-form active" id="paso-1">

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
            <input type="text" name="dni" value="{{ old('dni') }}" placeholder="12345678" required>
          </div>
          <div class="auth-campo">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="9XX XXX XXX" required>
          </div>
        </div>

        <div class="auth-campo">
          <label>Correo electrónico</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" required>
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

        <div class="auth-campo">
          <label>Número de licencia</label>
          <input type="text" name="numero_licencia" value="{{ old('numero_licencia') }}" placeholder="Ej.: LIC-123456" required>
        </div>

        <button type="button" class="btn-auth" id="siguienteBtn">
          Siguiente: datos del vehículo
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

      </div>

      {{-- Paso 2: Vehículo --}}
      <div class="paso-form" id="paso-2">

        <div class="auth-paso-titulo">Datos del vehículo</div>

        <div class="auth-grid-2">
          <div class="auth-campo">
            <label>Placa</label>
            <input type="text" name="placa" value="{{ old('placa') }}" placeholder="ABC-1234" required>
          </div>
          <div class="auth-campo">
            <label>Marca</label>
            <input type="text" name="marca" value="{{ old('marca') }}" placeholder="Ej.: Honda">
          </div>
          <div class="auth-campo">
            <label>Modelo</label>
            <input type="text" name="modelo" value="{{ old('modelo') }}" placeholder="Ej.: Wave 110">
          </div>
          <div class="auth-campo">
            <label>Año</label>
            <input type="text" name="year" value="{{ old('year') }}" placeholder="Ej.: 2022">
          </div>
          <div class="auth-campo">
            <label>Color</label>
            <input type="text" name="color" value="{{ old('color') }}" placeholder="Ej.: Rojo">
          </div>
          <div class="auth-campo">
            <label>Número SOAT</label>
            <input type="text" name="numero_soat" value="{{ old('numero_soat') }}" placeholder="SOAT-ABC-1234">
          </div>
        </div>

        <div class="auth-botones-fila">
          <button type="button" class="btn-auth-outline" id="atrasBtn">
            ← Atrás
          </button>
          <button type="submit" class="btn-auth">
            Confirmar registro
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <path d="M20 6 9 17l-5-5"/>
            </svg>
          </button>
        </div>

      </div>

    </form>

    <p class="auth-link-texto">
      ¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
    </p>

  </div>

</div>

@vite(['resources/js/auth/registro_conductor.js'])

@endsection
