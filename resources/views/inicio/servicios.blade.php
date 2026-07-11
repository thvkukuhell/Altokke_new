@extends('layouts.main')

@section('content')
<section class="seccion servicios-seccion">
    <div class="seccion-inner">
        <div class="seccion-cabecera">
            <span class="seccion-chip">Servicios</span>
            <h1 class="seccion-titulo">Respuestas rápidas sobre Altokke</h1>
            <p class="seccion-sub">Aquí encontrarás información clara sobre cómo pedir un viaje, cuánto cuesta y qué hacer si tienes un problema.</p>
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
                <p>En tu cuenta de pasajero, utiliza la opción "Mis viajes" para ver los viajes que ya realizaste, el costo de cada uno y los comprobantes disponibles.</p>
            </article>

            <article class="faq-card">
                <h2>¿Puedo ser conductor en Altokke?</h2>
                <p>Sí. Si deseas ser conductor, regístrate como tal, envía tus datos y documentos, y espera la verificación para empezar a recibir solicitudes.</p>
            </article>

            <article class="faq-card">
                <h2>¿Cómo contacto al equipo de Altokke?</h2>
                <p>En la página de Contacto encontrarás el correo y el teléfono oficiales. Para enviar una consulta o reclamo, usa el formulario en Ayuda.</p>
            </article>
        </div>
    </div>
</section>
@endsection
