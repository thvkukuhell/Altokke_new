<header>
    <div class="barra-navegacion">

        <div class="logo">
            <img src="{{ asset('assets/img/logoTemporal.png') }}" alt="Logo">
        </div>

        <nav class="enlaces-nav">

            <a href="{{ url('/inicio') }}">Inicio</a>

            <a href="{{ url('/inicio/como_funciona') }}">
                ¿Cómo funciona?
            </a>

            <a href="{{ url('/inicio/sobre_nosotros') }}">
                Sobre nosotros
            </a>

            <a href="{{ url('/auth/login') }}" class="btn-iniciar-sesion">
                Iniciar sesión
            </a>

        </nav>

    </div>
</header>