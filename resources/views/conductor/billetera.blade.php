@extends('layouts.main')
@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/conductor/perfil.css') }}">

<div class="pagina-conductor">
    <div class="perfil-layout">

        <!-- SIDEBAR -->
        @include('conductor.partials.sidebar', [
        'conductor' => $conductor,
        'iniciales' => $iniciales,
        'seccionActiva' => $seccionActiva
        ])

        <!-- CONTENIDO -->
        <div class="perfil-contenido">
            <h2>Mi Billetera</h2>

            <div class="tarjeta text-center">
                <h3>
                    S/ {{ number_format($ganancias->total ?? 0, 2) }}
                </h3>

                <p class="mt-2">Ganancias totales</p>

                <button type="button" class="btn btn-verde btn-retirar mt-4">
                    Retirar dinero
                </button>
            </div>

            <p class="text-muted text-center mt-4">
                Recargas y retiros próximamente...
            </p>
        </div>

    </div>
</div>

@endsection