@extends('layouts.main')

@section('content')
<section class="seccion servicios-seccion">
    <div class="seccion-inner">
        <div class="seccion-cabecera">
            <span class="seccion-chip">Servidores</span>
            <h1 class="seccion-titulo">Preguntas frecuentes sobre Altokke</h1>
            <p class="seccion-sub">Respuestas claras sobre cómo funciona el sistema, los viajes y la experiencia para pasajeros y conductores.</p>
        </div>

        <div class="faq-grid">
            <article class="faq-card">
                <h2>¿Qué necesito para pedir un viaje?</h2>
                <p>Solo necesitas una cuenta de pasajero. Completa tu registro, elige origen y destino, y confirma el viaje. El sistema mostrará la tarifa estimada antes de enviar la solicitud.</p>
            </article>

            <article class="faq-card">
                <h2>¿Cómo se calcula la tarifa?</h2>
                <p>La tarifa se estima con base en la distancia y el tiempo proyectado del trayecto. En la solicitud verás el precio antes de confirmar.</p>
            </article>

            <article class="faq-card">
                <h2>¿Qué pasa si el conductor no llega?</h2>
                <p>Si el conductor no llega o no acepta tu solicitud, puedes cancelar el viaje desde la misma pantalla. Nuestro soporte también está disponible si necesitas ayuda adicional.</p>
            </article>

            <article class="faq-card">
                <h2>¿Dónde veo el historial de viajes?</h2>
                <p>En el panel de pasajero, usa la opción "Mis viajes" para ver los viajes anteriores, detalles de tarifa y comprobantes disponibles.</p>
            </article>

            <article class="faq-card">
                <h2>¿Puedo ser conductor en Altokke?</h2>
                <p>Sí. Regístrate como conductor, sube tus datos y documentos. Una vez verificado, aparecerás en la plataforma para recibir solicitudes.</p>
            </article>

            <article class="faq-card">
                <h2>¿Cómo contacto soporte?</h2>
                <p>Visita la página de Contacto para encontrar el correo y teléfono oficiales. También puedes usar la sección de Ayuda si tienes dudas rápidas sobre el servicio.</p>
            </article>
        </div>
    </div>
</section>
@endsection
