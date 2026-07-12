@extends('layouts.main')
@section('content')

<div class="pagina-pasajero-perfil">
    <div class="perfil-layout">
 
        {{-- SIDEBAR --}}
        <aside class="perfil-sidebar">
            <div class="sidebar-cabecera">
                <div class="avatar-wrapper">
                    {{-- Dejamos fijas las iniciales para máxima eficiencia --}}
                    <div class="sidebar-avatar">{{ $user->iniciales() }}</div>
                </div>
                <div class="sidebar-nombre">{{ $user->nombre_completo ?? 'Usuario' }}</div>
                <div class="sidebar-rol">Pasajero</div>
            </div>
 
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('pasajero.historial') }}">
                        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        Mis viajes
                    </a>
                </li>
                <li>
                    <a href="{{ route('pasajero.perfil') }}" class="activo">
                        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                        Mi perfil
                    </a>
                </li>
                <li>
                    <a href="{{ route('pasajero.solicitarViaje') }}">
                        <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14m-7-7h14"/>
                        </svg>
                        Solicitar viaje
                    </a>
                </li>
            </ul>
        </aside>
 
        {{-- CONTENIDO FORMULARIO --}}
        <div class="perfil-contenido">
            <div class="tarjeta-perfil-bloque">
                <div class="perfil-encabezado">
                    <h2>Editar Detalles del Perfil</h2>
                </div>
 
                <form action="{{ route('pasajero.guardarPerfil') }}" method="POST">
                    @csrf
                    @method('PATCH')
 
                    <div class="perfil-grid">
                        <div>
                            <label class="campo-label" for="nombre_completo">Nombre</label>
                            <input type="text" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $user->nombre_completo) }}" class="campo-input" required>
                        </div>
 
                        <div>
                            <label class="campo-label" for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" class="campo-input">
                        </div>

                        <div>
                            <label class="campo-label" for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" value="{{ old('dni', $user->dni) }}" class="campo-input" placeholder="Número de documento" maxlength="8" inputmode="numeric">
                        </div>
 
                        <div>
                            <label class="campo-label" for="telefono">Teléfono / Celular</label>
                            <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" class="campo-input" placeholder="9XX XXX XXX" inputmode="numeric" autocomplete="tel" required>
                        </div>
 
                        <div>
                            <label class="campo-label" for="metodo_pago_preferido">Método de pago preferido</label>
                            <select name="metodo_pago_preferido" id="metodo_pago_preferido" class="campo-select">
                                @foreach(['efectivo' => '💵 Efectivo', 'yape' => '💜 Yape', 'plin' => '💙 Plin'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('metodo_pago_preferido', $pasajero->metodo_pago_preferido) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
 
                    @if ($errors->any())
                        <div class="alerta-errores-box">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
 
                    <div class="perfil-botones-acciones-footer">
                        <button type="submit" class="btn-guardar-cambios-perfil">
                            Guardar cambios
                        </button>
                        <a href="{{ route('pasajero.perfil') }}" class="btn-cancelar-perfil">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection