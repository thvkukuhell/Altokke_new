<header class="encabezado-app app-shell-header" data-app-header>
    <div class="barra-navegacion-app">

        <div class="logo-app">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Logo Altokke">
            <span class="logo-texto-app">Altokke</span>
        </div>

        <button type="button" class="app-nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-pasajero">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="app-nav-overlay" data-app-nav-overlay hidden></div>

        <nav class="enlaces-nav-app app-drawer-nav" id="menu-pasajero" aria-label="Menu pasajero">
            <div class="app-drawer-head">
                <div>
                    <strong>Altokke</strong>
                    <span>Pasajero</span>
                </div>
                <button type="button" class="app-drawer-close" aria-label="Cerrar menu" data-app-nav-close>
                    &times;
                </button>
            </div>

            <a href="{{ route('pasajero.solicitarViaje') }}"
                class="enlace-menu-app {{ Request::is('pasajero/solicitarViaje') ? 'activo' : '' }}">
                Solicitar viaje
            </a>

            <a href="{{ route('pasajero.historial') }}"
                class="enlace-menu-app {{ Request::is('pasajero/historial*') ? 'activo' : '' }}">
                Mis viajes
            </a>

            @if($viajeActivo ?? null)
                @if($viajeActivo->estado_viaje === 'buscando')
                    <a href="{{ route('pasajero.buscando', $viajeActivo->id_viaje) }}"
                        class="enlace-menu-app nav-viaje-activo">
                        <span class="nav-dot-pulse"></span>
                        Buscando conductor
                    </a>
                @else
                    <a href="{{ route('pasajero.enCurso', $viajeActivo->id_viaje) }}"
                        class="enlace-menu-app nav-viaje-activo">
                        <span class="nav-dot-pulse"></span>
                        Viaje en curso
                    </a>
                @endif
            @endif

            <a href="{{ route('pasajero.perfil') }}" class="perfil-contenedor-app">
                <div class="avatar-circular-app">
                    <img src="{{ asset('img/perfil.png') }}" alt="Perfil">
                </div>
            </a>

            <a href="{{ route('logout') }}" class="btn-cerrar-sesion-app"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesi&oacute;n
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </nav>

    </div>
</header>
