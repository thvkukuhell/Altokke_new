<header class="site-header">
    <div class="barra-navegacion">
        <a href="{{ url('/#inicio') }}" class="logo">
            <img src="{{ asset('img/logo_moto.png') }}" alt="Altokke">
            <span class="logo-texto">Altokke</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="enlaces-nav" id="menu-principal" aria-label="Navegacion principal">
            <a href="{{ url('/#inicio') }}">Inicio</a>
            <a href="{{ url('/#como-funciona') }}">Como funciona</a>
            <a href="{{ url('/#sobre-nosotros') }}">Sobre nosotros</a>
            <a href="{{ route('login') }}" class="btn-iniciar-sesion">Iniciar sesion</a>
        </nav>
    </div>
</header>
