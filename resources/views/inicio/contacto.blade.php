@extends('layouts.main')

@section('content')
<section class="seccion contacto-seccion">
    <div class="seccion-inner">
        <div class="seccion-cabecera">
            <span class="seccion-chip">Contacto</span>
            <h1 class="seccion-titulo">Contáctanos</h1>
            <p class="seccion-sub">Si quieres enviar una consulta, reclamo o sugerencia, usa los datos oficiales de soporte.</p>
        </div>

        <div class="contacto-panel">
            <div class="contacto-card">
                <h2>Correo oficial</h2>
                <p><a href="mailto:{{ config('app.support_email') }}" target="_blank" rel="noopener">{{ config('app.support_email') }}</a></p>
            </div>
            <div class="contacto-card">
                <h2>Teléfono</h2>
                <p><a href="tel:{{ config('app.support_phone') }}">{{ config('app.support_phone') }}</a></p>
            </div>
            <div class="contacto-card">
                <h2>Horario</h2>
                <p>Lunes a domingo, de 08:00 a 21:00.</p>
            </div>
        </div>

        <p class="contacto-texto">Si necesitas enviar una consulta, reclamo o sugerencia, usa el formulario en la página de Ayuda.</p>
    </div>
</section>
@endsection
