@extends('layouts.main')
@section('content')

@php
    $fotoPerfilUrl = $user->foto_perfil
        ? '/storage/' . ltrim($user->foto_perfil, '/')
        : null;
@endphp

<div class="pagina-pasajero-perfil">
    <div class="perfil-layout">
 
        {{-- SIDEBAR --}}
        <aside class="perfil-sidebar">
            <div class="sidebar-cabecera">
                <div class="avatar-wrapper">
                    <img
                        src="{{ $fotoPerfilUrl ?? '' }}"
                        alt="Foto de perfil"
                        class="sidebar-avatar-img"
                        data-profile-photo-image
                        @if(!$fotoPerfilUrl)
                            style="display: none;"
                        @endif
                    >

                    <div
                        class="sidebar-avatar"
                        data-profile-photo-placeholder
                        @if($fotoPerfilUrl)
                            style="display: none;"
                        @endif
                    >
                        {{ $user->iniciales() }}
                    </div>
                </div>
                <div class="sidebar-nombre">
                    {{ $user->nombre_completo ?? 'Usuario' }}
                    {{ $user->apellidos ?? '' }}
                </div>
                <div class="sidebar-rol">Pasajero</div>
            </div>
 
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('pasajero.historial') }}" class="{{ ($seccionActiva ?? '') === 'historial' ? 'activo' : '' }}">
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
                        <button type="submit" class="btn-cerrar-sesion-sidebar">
                            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-6 0v-1m0-8V7a3 3 0 0 1 6 0v1"/>
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </aside>
 
        {{-- CONTENIDO DE DATOS --}}
        <div class="perfil-contenido">

            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            <div class="tarjeta-perfil-bloque">
                <div class="perfil-encabezado">
                    <h2>Foto de perfil</h2>
                </div>

            <form
                method="POST"
                action="{{ route('perfil.foto') }}"
                enctype="multipart/form-data"
                class="perfil-upload-form"
                data-profile-photo-form
            >
                @csrf

                <input
                    type="file"
                    name="foto_perfil"
                    accept="image/png,image/jpeg"
                    required
                    data-profile-photo-input
                >

                <button
                    type="submit"
                    class="btn-editar-perfil-accion"
                    data-profile-photo-button
                >
                    Subir foto
                </button>
            </form>

            <p
                class="perfil-ayuda"
                data-profile-photo-status
                aria-live="polite"
            >
                Formatos permitidos: JPG o PNG. Máximo 2 MB.
            </p>

            @error('foto_perfil')
                <p class="form-errors">
                    {{ $message }}
                </p>
            @enderror

                <p class="perfil-ayuda" data-profile-photo-status aria-live="polite">
                    Formatos permitidos: JPG o PNG. Máximo 2 MB.
                </p>

                @error('foto_perfil')
                    <p class="form-errors">
                        {{ $message }}
                    </p>
                @enderror
            </div>
 
            <div class="tarjeta-perfil-bloque">
                <div class="perfil-encabezado">
                    <h2>Información Personal</h2>
                    <a href="{{ route('pasajero.editarPerfil') }}" class="btn-editar-perfil-accion">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>
                        </svg>
                        Editar perfil
                    </a>
                </div>
 
                <div class="perfil-grid">
                    <div>
                        <p class="perfil-campo-label">Nombre</p>
                        <p class="perfil-campo-valor">{{ $user->nombre_completo ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="perfil-campo-label">Apellidos</p>
                        <p class="perfil-campo-valor {{ empty($user->apellidos) ? 'vacio' : '' }}">
                            {{ $user->apellidos ?: '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="perfil-campo-label">DNI</p>
                        <p class="perfil-campo-valor {{ empty($user->dni) ? 'vacio' : '' }}">
                            {{ $user->dni ?: '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="perfil-campo-label">Miembro desde</p>
                        <p class="perfil-campo-valor">
                            {{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                </div>
 
                <p class="perfil-seccion-titulo">Detalles de Contacto</p>
 
                <div class="perfil-grid">
                    <div>
                        <p class="perfil-campo-label">Correo Electrónico</p>
                        <p class="perfil-campo-valor">{{ $user->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="perfil-campo-label">Teléfono / Celular</p>
                        <p class="perfil-campo-valor {{ empty($user->telefono) ? 'vacio' : '' }}">
                            {{ $user->telefono ?: '—' }}
                        </p>
                    </div>
                </div>
            </div>
 
            {{-- Método de pago --}}
            <div class="tarjeta-perfil-bloque">
                <div class="perfil-encabezado">
                    <h2>Método de pago preferido</h2>
                    <a href="{{ route('pasajero.editarPerfil') }}" class="btn-cambiar-pago-text">
                        Cambiar
                    </a>
                </div>
 
                @php
                    $iconosPago = ['efectivo' => '💵', 'yape' => '💜', 'plin' => '💙'];
                    $metodo = $pasajero->metodo_pago_preferido ?? 'efectivo';
                    $icono  = $iconosPago[$metodo] ?? '💵';
                @endphp
 
                <div class="pago-fila">
                    <span class="pago-icono">{{ $icono }}</span>
                    <span class="pago-texto-nombre">{{ ucfirst($metodo) }}</span>
                    <span class="badge-predeterminado">Predeterminado</span>
                </div>
            </div>
 
        </div>
    </div>
</div>
 
@endsection
