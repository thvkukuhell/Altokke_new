<header class="encabezado app-shell-header" data-app-header data-header-role="conductor">
    <div class="barra-navegacion">

        <div class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Logo">
        </div>

        <button type="button" class="app-nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-conductor">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="app-nav-overlay" data-app-nav-overlay hidden></div>

        <nav class="enlaces-nav app-drawer-nav" id="menu-conductor" aria-label="Menu conductor">
            <div class="app-drawer-head">
                <div>
                    <strong>Altokke</strong>
                    <span>Conductor</span>
                </div>
                <button type="button" class="app-drawer-close" aria-label="Cerrar menu" data-app-nav-close>
                    &times;
                </button>
            </div>

            <a href="{{ route('conductor.dashboard') }}" class="{{ ($seccionActiva ?? '') === 'inicio' ? 'activo' : '' }}">
                Inicio
            </a>

            <a href="{{ route('conductor.solicitudes') }}"
                class="{{ ($seccionActiva ?? '') === 'solicitudes' ? 'activo' : '' }}">
                Solicitudes
            </a>

            @if($tieneViajeActivo ?? false)
                <a href="{{ route('conductor.viaje_activo') }}"
                    class="{{ ($seccionActiva ?? '') === 'viajeActivo' ? 'activo' : '' }}">
                    Viaje activo
                </a>
            @endif

            <a href="{{ route('conductor.historial') }}"
                class="{{ ($seccionActiva ?? '') === 'historial' ? 'activo' : '' }}">
                Historial
            </a>

            <a href="{{ route('conductor.billetera') }}"
                class="{{ ($seccionActiva ?? '') === 'billetera' ? 'activo' : '' }}">
                Billetera
            </a>

            <a href="{{ route('conductor.perfil') }}" class="perfil-link-header {{ ($seccionActiva ?? '') === 'perfil' ? 'activo' : '' }}">
                @include('conductor.partials.avatar', [
                    'user' => $conductor->user ?? auth()->user(),
                    'initials' => $iniciales ?? null,
                    'size' => 'header',
                ])
                <span class="perfil-link-header__texto">Perfil</span>
            </a>

            <a href="{{ route('logout') }}" class="btn-cerrar js-cerrar-sesion"
               data-form-id="logout-form">
                Cerrar sesi&oacute;n
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>

        </nav>
    </div>
</header>

@vite(['resources/js/layouts/header_conductor.js'])
