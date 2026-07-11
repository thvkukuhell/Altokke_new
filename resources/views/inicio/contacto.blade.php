@extends('layouts.main')

@section('content')
<section class="seccion contacto-seccion">
    <div class="seccion-inner">
        <div class="seccion-cabecera">
            <span class="seccion-chip">Contacto</span>
            <h1 class="seccion-titulo">¿Necesitas asistencia?</h1>
            <p class="seccion-sub">Estamos aquí para ayudarte con tu cuenta, tu viaje o cualquier duda sobre el uso de Altokke.</p>
        </div>

        <div class="contacto-panel">
            <div class="contacto-card">
                <h2>Correo de soporte</h2>
                <p><a href="mailto:soporte@altokke.com">soporte@altokke.com</a></p>
            </div>
            <div class="contacto-card">
                <h2>Teléfono</h2>
                <p><a href="tel:+51999999999">+51 999 999 999</a></p>
            </div>
            <div class="contacto-card">
                <h2>Horario de atención</h2>
                <p>Lun - Dom: 08:00 - 21:00</p>
            </div>
        </div>

        <p class="contacto-texto">Si tienes una incidencia con un viaje, por favor indícanos tu nombre, número de teléfono y detalles del viaje.</p>
    </div>
</section>
@endsection
