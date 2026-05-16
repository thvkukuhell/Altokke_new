@extends('layouts.main')

@section('content')

<main>
    <h1>Tres pasos y listo</h1>

    <section class="pasos">

        <div class="tarjeta-paso">
            <img src="{{ asset('img/user.png') }}" alt="icon user">

            <h3>1. Regístrate</h3>

            <p>
                Crea tu cuenta gratis con tu nombre y celular
                en menos de 1 minuto
            </p>
        </div>

        <div class="tarjeta-paso">
            <img src="{{ asset('img/location.png') }}" alt="icon location">

            <h3>2. Indica tu destino</h3>

            <p>
                Escribe a dónde quieres ir y ve la tarifa
                antes de confirmar
            </p>
        </div>

        <div class="tarjeta-paso">
            <img src="{{ asset('img/logoTemporal.png') }}" alt="icon temp">

            <h3>3. Tu moto llega</h3>

            <p>
                Un conductor verificado acepta tu solicitud
                y va a recogerte
            </p>
        </div>

        <div class="tarjeta-paso">
            <img src="{{ asset('img/estrella.png') }}" alt="icon estrella">

            <h3>4. Califica tu viaje</h3>

            <p>
                Al llegar, puntúa al conductor para mantener
                la calidad del servicio
            </p>
        </div>

    </section>
</main>

@endsection