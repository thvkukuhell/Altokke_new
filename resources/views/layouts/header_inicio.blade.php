<header>
    <div class="barra-navegacion">

        {{-- Te redirige a la raíz y luego sube al inicio --}}
        <a href="{{ url('/#inicio') }}" class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Logo">
            <span class="logo-texto">Altokke</span>
        </a>

        <nav class="enlaces-nav">

            {{-- Fuerza a ir a la página principal antes de buscar el anclaje --}}
            <a href="{{ url('/#inicio') }}">Inicio</a>

            <a href="{{ url('/#como-funciona') }}">
                ¿Cómo funciona?
            </a>

            <a href="{{ url('/#sobre-nosotros') }}">
                Sobre nosotros
            </a>

            <a href="{{ route('login') }}" class="btn-iniciar-sesion">
                Iniciar sesión
            </a>

        </nav>

    </div>
</header>