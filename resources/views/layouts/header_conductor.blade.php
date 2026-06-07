<header class="encabezado">
    <div class="barra-navegacion">

        <div class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Logo">
        </div>

        <nav class="enlaces-nav">

            <a href="{{ url('/conductor') }}" class="{{ ($seccionActiva ?? '') === 'inicio' ? 'activo' : '' }}">
                Inicio
            </a>

            <a href="{{ route('conductor.solicitudes') }}"
                class="{{ ($seccionActiva ?? '') === 'solicitudes' ? 'activo' : '' }}">
                Solicitudes
            </a>

            @php
                use App\Models\Viaje;
            @endphp
            @if(Viaje::where('id_conductor', auth()->id())
                    ->whereIn('estado_viaje', ['aceptado', 'recogiendo', 'en_curso'])
                    ->exists())
                <a href="{{ route('conductor.viaje_activo') }}"
                    class="{{ ($seccionActiva ?? '') === 'viaje_activo' ? 'activo' : '' }}">
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

            <a href="{{ route('conductor.perfil') }}" class="{{ ($seccionActiva ?? '') === 'perfil' ? 'activo' : '' }}">
                <img src="{{ asset('img/perfil.png') }}" class="imagen-perfil" alt="Perfil">
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