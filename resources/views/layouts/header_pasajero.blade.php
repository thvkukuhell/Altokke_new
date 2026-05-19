<header class="encabezado">
    <div class="barra-navegacion">

        <div class="logo">
            <img src="{{ asset('img/logoTemporal.png') }}" alt="Logo">
        </div>

        <nav class="enlaces-nav">
            {{-- Siempre accesibles --}}
            <a href="{{ route('pasajero.solicitarViaje') }}"
                class="{{ Request::is('pasajero/solicitarViaje') ? 'activo' : '' }}">
                Solicitar viaje
            </a>

            <a href="{{ route('pasajero.historial') }}"
                class="{{ Request::is('pasajero/historial*') ? 'activo' : '' }}">
                Mis viajes
            </a>

            {{-- Solo aparece si hay un viaje activo --}}
            @php
            $viajeActivo = null;
            if (auth()->check()) {
            $viajeActivo = \App\Models\Viaje::where('id_pasajero', auth()->id())
            ->whereIn('estado_viaje', ['buscando', 'aceptado', 'recogiendo', 'en_curso'])
            ->first();
            }
            @endphp

            @if($viajeActivo)
            @if($viajeActivo->estado_viaje === 'buscando')
            <a href="{{ route('pasajero.buscando', $viajeActivo->id_viaje) }}" class="nav-viaje-activo">
                <span class="nav-dot-pulse"></span>
                Buscando conductor
            </a>
            @else
            <a href="{{ route('pasajero.enCurso', $viajeActivo->id_viaje) }}" class="nav-viaje-activo">
                <span class="nav-dot-pulse"></span>
                Viaje en curso
            </a>
            @endif
            @endif

            {{-- Perfil --}}
            <a href="{{ route('pasajero.perfil') }}">
                <img src="{{ asset('img/user.png') }}" class="imagen-perfil" alt="Perfil">
            </a>

            <a href="{{ route('logout') }}" class="btn-cerrar"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesión
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </nav>

    </div>
</header>