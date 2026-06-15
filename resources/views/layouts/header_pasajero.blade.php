<header class="encabezado-app app-shell-header" id="header-pasajero" data-app-header>
    <div class="barra-navegacion-app">
        <a href="{{ route('pasajero.solicitarViaje') }}" class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Altokke">
            <span class="logo-texto">Altokke</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-pasajero"
            aria-label="Abrir menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <button type="button" class="app-nav-toggle" aria-label="Abrir menu" aria-expanded="false"
            aria-controls="menu-pasajero">
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
                class="enlace-menu-app {{ Request::is('pasajero/historial') ? 'activo' : '' }}">
                Mis viajes
            </a>

            @if($viajeActivo ?? null)
            @if($viajeActivo->estado_viaje === 'buscando')
            <a href="{{ route('pasajero.buscando', $viajeActivo->id_viaje) }}" class="enlace-menu-app nav-viaje-activo">
                <span class="nav-dot-pulse"></span>
                Buscando conductor
            </a>
            @else
            <a href="{{ route('pasajero.enCurso', $viajeActivo->id_viaje) }}" class="enlace-menu-app nav-viaje-activo">
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
                onclick="event.preventDefault(); document.getElementById('logout-pasajero').submit();">
                Cerrar sesión
            </a>
            <form id="logout-pasajero" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('header-pasajero');
    const toggle = header?.querySelector('.nav-toggle');

    toggle?.addEventListener('click', () => {
        const isOpen = header.classList.toggle('nav-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', (e) => {
        if (header && !header.contains(e.target)) {
            header.classList.remove('nav-open');
            toggle?.setAttribute('aria-expanded', 'false');
        }
    });

    // Scroll
    window.addEventListener('scroll', () => {
        header?.classList.toggle('scrolled', window.scrollY > 20);
    }, {
        passive: true
    });
});
</script>