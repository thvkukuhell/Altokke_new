@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Foto de perfil</h2>
                </div>
                <div class="perfil-foto-bloque">
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
                            class="btn btn-verde"
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
                        JPG o PNG. Máximo 2 MB.
                    </p>

                    @error('foto_perfil')
                        <div class="form-errors">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
 
            {{-- Datos personales --}}
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Mi Perfil — Conductor</h2>
                </div>
 
                <form method="POST" action="{{ route('conductor.actualizarPerfil') }}">
                    @csrf
                    @method('PATCH')
 
                    <div class="perfil-grid">
                        <div>
                            <p class="perfil-campo-label">Nombre completo</p>
                            <input type="text"
                                   name="nombre_completo"
                                   class="campo-input"
                                   value="{{ old('nombre_completo', $conductor->user->nombre_completo) }}"
                                   required>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Apellidos</p>
                            <input type="text"
                                   name="apellidos"
                                   class="campo-input"
                                   value="{{ old('apellidos', $conductor->user->apellidos) }}">
                        </div>
                        <div>
                            <p class="perfil-campo-label">Teléfono</p>
                            <input type="tel"
                                   name="telefono"
                                   class="campo-input"
                                   value="{{ old('telefono', $conductor->user->telefono) }}"
                                   inputmode="numeric"
                                   autocomplete="tel"
                                   required>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Email</p>
                            <input type="email"
                                   name="email"
                                   class="campo-input"
                                   value="{{ old('email', $conductor->user->email) }}"
                                   autocomplete="email"
                                   required>
                        </div>
                    </div>
 
                    @if($errors->any())
                        <div class="form-errors" style="margin-top:12px;">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
 
                    <button type="submit" class="btn btn-verde" style="margin-top:16px;">
                        Guardar cambios
                    </button>
                </form>
            </div>
 
            {{-- Vehículo --}}
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Mi Vehículo</h2>
                </div>
 
                @if($vehiculo)
                    <div class="perfil-grid">
                        <div>
                            <p class="perfil-campo-label">Placa</p>
                            <p class="perfil-campo-valor">{{ $vehiculo->placa }}</p>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Marca / Modelo</p>
                            <p class="perfil-campo-valor">
                                {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
                            </p>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Color</p>
                            <p class="perfil-campo-valor">{{ $vehiculo->color }}</p>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Año</p>
                            <p class="perfil-campo-valor">{{ $vehiculo->anio ?? '—' }}</p>
                        </div>
                    </div>
                @else
                    <p style="color:var(--p-rojo);">Aún no tienes vehículo registrado.</p>
                @endif
            </div>
 
        </div>
    </div>
</div>

@endsection
