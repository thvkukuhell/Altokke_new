<header class="encabezado">
    <div class="barra-navegacion">

        <div class="logo">
            <img src="{{ asset('assets/img/logoTemporal.png') }}" alt="Logo">
        </div>

        <nav class="enlaces-nav">

            <a href="{{ url('/pasajero/solicitarViaje') }}">
                Solicitar viaje
            </a>

            <a href="{{ url('/pasajero/buscando') }}">
                Buscando conductor
            </a>

            <a href="{{ url('/pasajero/enCurso') }}">
                Viaje en curso
            </a>

            <a href="{{ url('/pasajero/calificar') }}">
                Calificar viaje
            </a>

            <a href="{{ url('/pasajero/historial') }}">
                Historial
            </a>

            <a href="{{ url('/pasajero/perfil') }}">
                <img src="{{ asset('assets/img/user.png') }}" class="imagen-perfil" alt="Perfil">
            </a>

        </nav>

    </div>
</header>