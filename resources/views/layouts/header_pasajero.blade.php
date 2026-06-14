<header class="site-header" id="header-pasajero">
    <div class="barra-navegacion">
        <a href="{{ url('/pasajero/solicitarViaje') }}" class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Altokke">
            <span class="logo-texto">Altokke</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false"
                aria-controls="menu-pasajero" aria-label="Abrir menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="enlaces-nav" id="menu-pasajero" aria-label="Navegacion pasajero">
            <a href="{{ route('pasajero.solicitarViaje') }}"
               class="{{ ($seccionActiva ?? '') === 'solicitar' ? 'activo' : '' }}">
                Solicitar viaje
            </a>
            <a href="{{ route('pasajero.historial') }}"
               class="{{ ($seccionActiva ?? '') === 'historial' ? 'activo' : '' }}">
                Mis viajes
            </a>
            <a href="{{ route('pasajero.perfil') }}"
               class="{{ ($seccionActiva ?? '') === 'perfil' ? 'activo' : '' }}">
                Perfil
            </a>
            <a href="{{ route('logout') }}" class="btn-iniciar-sesion"
               onclick="event.preventDefault(); document.getElementById('logout-pasajero').submit();">
                Cerrar sesión
            </a>
            <form id="logout-pasajero" action="{{ route('logout') }}"
                  method="POST" style="display:none;">
                @csrf
            </form>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const header  = document.getElementById('header-pasajero');
    const toggle  = header?.querySelector('.nav-toggle');

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
    }, { passive: true });
});
</script>