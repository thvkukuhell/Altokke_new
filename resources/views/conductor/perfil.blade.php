@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif
 
            {{-- Datos personales --}}
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Mi Perfil — Conductor</h2>
                </div>
 
                <form method="POST" action="{{ route('conductor.actualizarPerfil') }}">
                    @csrf
                    @method('PUT')
 
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
                            <input type="text"
                                   name="telefono"
                                   class="campo-input"
                                   value="{{ old('telefono', $conductor->user->telefono) }}"
                                   required>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Email</p>
                            <input type="email"
                                   name="email"
                                   class="campo-input"
                                   value="{{ old('email', $conductor->user->email) }}"
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