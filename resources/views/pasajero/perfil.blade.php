<div class="pagina-pasajero">
    <div class="perfil-layout">
 
        {{-- SIDEBAR --}}
        <aside class="perfil-sidebar">
            <div class="sidebar-cabecera">
                <div class="sidebar-avatar">{{ $iniciales ?? '—' }}</div>
                <div class="sidebar-nombre">
                    {{ $user->nombre_completo ?? 'Usuario' }}
                    {{ $user->apellidos ?? '' }}
                </div>
            </div>
 
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('pasajero.historial') }}"
                       class="{{ $seccionActiva === 'historial' ? 'activo' : '' }}">
                        Mis viajes
                    </a>
                </li>
                <li>
                    <a href="{{ route('pasajero.perfil') }}"
                       class="{{ $seccionActiva === 'perfil' ? 'activo' : '' }}">
                        Mi perfil
                    </a>
                </li>
                <li>
                    <a href="{{ route('pasajero.solicitarViaje') }}">
                        Solicitar viaje
                    </a>
                </li>
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
 
            {{-- Tarjeta: datos personales --}}
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Mi Perfil</h2>
                    <a href="{{ route('pasajero.editarPerfil') }}" class="btn btn-verde">
                        Editar
                    </a>
                </div>
 
                <div class="perfil-grid">
 
                    <div>
                        <p class="perfil-campo-label">Tu nombre</p>
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
                        <p class="perfil-campo-label">Email</p>
                        <p class="perfil-campo-valor">{{ $user->email ?? '—' }}</p>
                    </div>
 
                    <div>
                        <p class="perfil-campo-label">Número de teléfono</p>
                        <p class="perfil-campo-valor {{ empty($user->telefono) ? 'vacio' : '' }}">
                            {{ $user->telefono ?: '—' }}
                        </p>
                    </div>
 
                </div>
            </div>
 
            {{-- Tarjeta: método de pago --}}
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Método de pago preferido</h2>
                    <a href="{{ route('pasajero.editarPerfil') }}" class="btn btn-outline">
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
                    <span>{{ ucfirst($metodo) }}</span>
                </div>
            </div>
 
        </div>
    </div>
</div>