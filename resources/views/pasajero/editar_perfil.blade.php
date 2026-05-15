@extends('layouts.main')
@section('contenido')

<div class="pagina-pasajero">
    <div class="perfil-layout">
 
        {{-- SIDEBAR --}}
        <aside class="perfil-sidebar">
            <div class="sidebar-cabecera">
                <div class="sidebar-avatar">{{ $iniciales ?? '—' }}</div>
                <div class="sidebar-nombre">{{ $user->nombre_completo ?? '' }}</div>
            </div>
 
            <ul class="sidebar-menu">
                <li><a href="{{ route('pasajero.historial') }}">Mis viajes</a></li>
                <li>
                    <a href="{{ route('pasajero.perfil') }}" class="activo">Mi perfil</a>
                </li>
                <li><a href="{{ route('pasajero.solicitarViaje') }}">Solicitar viaje</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-cerrar-sesion">Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </aside>
 
        {{-- CONTENIDO --}}
        <div class="perfil-contenido">
            <div class="tarjeta">
                <h2 style="margin-bottom:16px;">Editar Perfil</h2>
 
                <form action="{{ route('pasajero.guardarPerfil') }}" method="POST">
                    @csrf
 
                    <div class="perfil-grid">
 
                        <div>
                            <label class="campo-label">Nombre</label>
                            <input type="text"
                                   name="nombre_completo"
                                   value="{{ old('nombre_completo', $user->nombre_completo) }}"
                                   class="campo-input"
                                   required>
                        </div>
 
                        <div>
                            <label class="campo-label">Apellidos</label>
                            <input type="text"
                                   name="apellidos"
                                   value="{{ old('apellidos', $user->apellidos) }}"
                                   class="campo-input">
                        </div>
 
                        <div>
                            <label class="campo-label">Teléfono</label>
                            <input type="text"
                                   name="telefono"
                                   value="{{ old('telefono', $user->telefono) }}"
                                   class="campo-input">
                        </div>
 
                        <div>
                            <label class="campo-label">Método de pago</label>
                            <select name="metodo_pago_preferido" class="campo-select">
                                @foreach(['efectivo' => 'Efectivo', 'yape' => 'Yape', 'plin' => 'Plin'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('metodo_pago_preferido', $pasajero->metodo_pago_preferido) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
 
                    </div>
 
                    @if ($errors->any())
                        <div class="form-errors" style="margin-top:12px;">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
 
                    <div style="margin-top:20px; display:flex; gap:10px;">
                        <button type="submit" class="btn btn-verde">Guardar cambios</button>
                        <a href="{{ route('pasajero.perfil') }}" class="btn btn-outline">Cancelar</a>
                    </div>
 
                </form>
            </div>
        </div>
    </div>
</div>

@endsection