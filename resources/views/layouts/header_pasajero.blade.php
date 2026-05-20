<header class="encabezado-app">
    <div class="barra-navegacion-app">

        <div class="logo-app">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Logo Altokke">
            <span class="logo-texto-app">Altokke</span>
        </div>

        <nav class="enlaces-nav-app">
            <a href="{{ route('pasajero.solicitarViaje') }}"
                class="enlace-menu-app {{ Request::is('pasajero/solicitarViaje') ? 'activo' : '' }}">
                Solicitar viaje
            </a>

            <a href="{{ route('pasajero.historial') }}"
                class="enlace-menu-app {{ Request::is('pasajero/historial*') ? 'activo' : '' }}">
                Mis viajes
            </a>

            {{-- Lógica de viaje activo --}}
            @php

            if (auth()->check()) {

                // Expirar viajes viejos
                \App\Models\Viaje::where('id_pasajero', auth()->id())
                    ->where('estado_viaje', 'buscando')
                    ->where('created_at', '<', now()->subMinutes(2))
                    ->update([
                        'estado_viaje' => 'expirado'
                    ]);

                // Buscar viaje activo reciente
                $viajeActivo = \App\Models\Viaje::where('id_pasajero', auth()->id())
                    ->whereIn('estado_viaje', [
                        'buscando',
                        'aceptado',
                        'recogiendo',
                        'en_curso'
                    ])
                    ->latest('id_viaje')
                    ->first();
            }

            @endphp

            {{-- Avatar de Perfil Circular Limpio --}}
            <a href="{{ route('pasajero.perfil') }}" class="perfil-contenedor-app">
                <div class="avatar-circular-app">
                    <img src="{{ asset('img/perfil.png') }}" alt="Perfil">
                </div>
            </a>

            <a href="{{ route('logout') }}" class="btn-cerrar-sesion-app"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesión
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </nav>

    </div>
</header>