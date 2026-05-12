<aside class="perfil-sidebar">
    <div class="sidebar-cabecera">
        <div class="sidebar-avatar">{{ $iniciales ?? '??' }}</div>
        <div class="sidebar-nombre">
            {{ $conductor->user->nombre_completo ?? '' }}
            {{ $conductor->user->apellidos ?? '' }}
        </div>
    </div>
 
    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('conductor.dashboard') }}"
               class="{{ ($seccionActiva ?? '') === 'inicio' ? 'activo' : '' }}">
                Inicio
            </a>
        </li>
        <li>
            <a href="{{ route('conductor.solicitudes') }}"
               class="{{ ($seccionActiva ?? '') === 'solicitudes' ? 'activo' : '' }}">
                Solicitudes
            </a>
        </li>
        <li>
            <a href="{{ route('conductor.viajeActivo') }}"
               class="{{ ($seccionActiva ?? '') === 'viaje-activo' ? 'activo' : '' }}">
                Viaje Activo
            </a>
        </li>
        <li>
            <a href="{{ route('conductor.historial') }}"
               class="{{ ($seccionActiva ?? '') === 'historial' ? 'activo' : '' }}">
                Historial
            </a>
        </li>
        <li>
            <a href="{{ route('conductor.billetera') }}"
               class="{{ ($seccionActiva ?? '') === 'billetera' ? 'activo' : '' }}">
                Billetera
            </a>
        </li>
        <li>
            <a href="{{ route('conductor.perfil') }}"
               class="{{ ($seccionActiva ?? '') === 'perfil' ? 'activo' : '' }}">
                Mi Perfil
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