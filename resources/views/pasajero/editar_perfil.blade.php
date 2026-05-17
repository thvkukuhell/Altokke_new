@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <div class="perfil-layout">
 
        {{-- SIDEBAR --}}
        <aside class="perfil-sidebar">
            <div class="sidebar-cabecera">
                <div class="sidebar-avatar">{{ $iniciales ?? '—' }}</div>
                <div class="sidebar-nombre">{{ $user->nombre_completo ?? '' }}</div>
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
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-cerrar-sesion">
                            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-6 0v-1m0-8V7a3 3 0 0 1 6 0v1"/>
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </aside>
 
        {{-- CONTENIDO --}}
        <div class="perfil-contenido">
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Editar Perfil</h2>
                </div>
 
                <form action="{{ route('pasajero.guardarPerfil') }}" method="POST">
                    @csrf
 
                    <div class="perfil-grid">
 
                        <div>
                            <label class="campo-label" for="nombre_completo">Nombre</label>
                            <input type="text"
                                   id="nombre_completo"
                                   name="nombre_completo"
                                   value="{{ old('nombre_completo', $user->nombre_completo) }}"
                                   class="campo-input"
                                   placeholder="Tu nombre"
                                   required>
                        </div>
 
                        <div>
                            <label class="campo-label" for="apellidos">Apellidos</label>
                            <input type="text"
                                   id="apellidos"
                                   name="apellidos"
                                   value="{{ old('apellidos', $user->apellidos) }}"
                                   class="campo-input"
                                   placeholder="Tus apellidos">
                        </div>
 
                        <div>
                            <label class="campo-label" for="telefono">Teléfono</label>
                            <input type="text"
                                   id="telefono"
                                   name="telefono"
                                   value="{{ old('telefono', $user->telefono) }}"
                                   class="campo-input"
                                   placeholder="9XX XXX XXX">
                        </div>
 
                        <div>
                            <label class="campo-label" for="metodo_pago_preferido">Método de pago preferido</label>
                            <select name="metodo_pago_preferido" id="metodo_pago_preferido" class="campo-select">
                                @foreach(['efectivo' => '💵 Efectivo', 'yape' => '💜 Yape', 'plin' => '💙 Plin'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('metodo_pago_preferido', $pasajero->metodo_pago_preferido) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
 
                    </div>
 
                    @if ($errors->any())
                        <div class="alerta-errores" style="margin-top:16px;">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
 
                    <div style="margin-top:24px; display:flex; gap:10px;">
                        <button type="submit" class="btn btn-verde">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Guardar cambios
                        </button>
                        <a href="{{ route('pasajero.perfil') }}" class="btn btn-outline">Cancelar</a>
                    </div>
 
                </form>
            </div>
        </div>
    </div>
</div>
 
@endsection
 