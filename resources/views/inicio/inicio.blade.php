{{-- CSS específico de esta vista --}}
<link rel="stylesheet" href="{{ asset('assets/css/inicio/inicio.css') }}">

<main class="inicio">
    <div class="principal">
        <h1>
            Tu mototaxi en Bagua, <span>cuando lo necesites</span>
        </h1>

        <p>
            Ofrecemos el mejor servicio de mototaxi en Bagua con conductores verificados
            y tiempos de espera menores a 5 minutos.
        </p>

        <div class="botones-registro">
            <a href="{{ url('/auth/login') }}">Pedir mototaxi ahora</a>

            <a href="{{ url('/auth/eleccion_registro') }}">
                Registrarme como conductor
            </a>
        </div>
    </div>
</main>