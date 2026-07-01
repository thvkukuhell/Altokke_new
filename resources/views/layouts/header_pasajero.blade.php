@php
    $usuarioPasajero = auth()->user();
    $fotoPerfilPasajero = $usuarioPasajero?->foto_perfil_url;
    $inicialesPasajero = $usuarioPasajero?->iniciales() ?: '??';
@endphp

<header class="encabezado-app app-shell-header" id="header-pasajero" data-app-header>
    <div class="barra-navegacion-app">
        <a href="{{ route('pasajero.solicitarViaje') }}" class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Altokke">
            <span class="logo-texto">Altokke</span>
        </a>

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

            @if($viajeActivoPasajero ?? null)
                @if($viajeActivoPasajero->estado_viaje === 'buscando')
                    <a href="{{ route('pasajero.buscando', $viajeActivoPasajero->id_viaje) }}" class="enlace-menu-app nav-viaje-activo">
                        <span class="nav-dot-pulse"></span>
                        Buscando conductor
                    </a>
                @else
                    <a href="{{ route('pasajero.enCurso', $viajeActivoPasajero->id_viaje) }}" class="enlace-menu-app nav-viaje-activo">
                        <span class="nav-dot-pulse"></span>
                        Viaje en curso
                    </a>
                @endif
            @endif

            <a href="{{ route('pasajero.perfil') }}" class="perfil-contenedor-app">
                <div class="avatar-circular-app">
                    @if($fotoPerfilPasajero)
                        <img src="{{ $fotoPerfilPasajero }}" alt="Perfil">
                    @else
                        <span class="avatar-circular-app__fallback">{{ $inicialesPasajero }}</span>
                    @endif
                </div>
            </a>

            <a href="{{ route('logout') }}" class="btn-cerrar-sesion-app js-cerrar-sesion"
               data-form-id="logout-pasajero">
                Cerrar sesion
            </a>
            <form id="logout-pasajero" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </nav>
    </div>
</header>

@vite(['resources/js/layouts/header_pasajero.js'])
