@extends('layouts.main')

@section('content')
<section class="help-page">
    <div class="help-page__inner">
        <header class="help-hero">
            <span class="info-page__eyebrow">Centro de ayuda</span>
            <h1>Encuentra respuestas rápidas sobre tu cuenta y tus viajes</h1>
            <p>
                Revisa dudas frecuentes sobre acceso, solicitudes, conductores, saldo simulado y soporte. Si tu caso necesita más detalle, puedes enviarlo desde Contacto.
            </p>
            <a href="{{ route('contacto') }}" class="info-action">Ir a contacto</a>
        </header>

        <nav class="help-categories" aria-label="Categorías de ayuda">
            <a href="#cuenta">Cuenta</a>
            <a href="#viajes">Viajes</a>
            <a href="#conductores">Conductores</a>
            <a href="#saldo">Saldo</a>
            <a href="#soporte">Soporte</a>
        </nav>

        <section class="help-quick" aria-labelledby="ayuda-rapida">
            <div class="help-quick__header">
                <h2 id="ayuda-rapida">Ayuda rápida</h2>
                <p>Elige el tema que más se acerca a tu consulta.</p>
            </div>
            <div class="help-quick__grid">
                <a href="#cuenta">
                    <strong>Problemas con una cuenta</strong>
                    <span>Acceso, registro y recuperación de contraseña.</span>
                </a>
                <a href="#viajes">
                    <strong>Problemas con un viaje</strong>
                    <span>Estados, cancelación y seguimiento.</span>
                </a>
                <a href="{{ route('contacto') }}">
                    <strong>Enviar una consulta</strong>
                    <span>Registra un caso para que pueda revisarse.</span>
                </a>
            </div>
        </section>

        <div class="help-faq">
            <section class="help-section" id="cuenta" aria-labelledby="ayuda-cuenta">
                <div class="help-section__title">
                    <span>Cuenta</span>
                    <h2 id="ayuda-cuenta">Cuenta y acceso</h2>
                </div>
                <div class="help-question-list">
                    <details class="help-question">
                        <summary>¿Qué necesito para pedir un viaje?</summary>
                        <p>Necesitas una cuenta de pasajero. Después de iniciar sesión, puedes ingresar origen, destino y enviar la solicitud.</p>
                    </details>
                    <details class="help-question">
                        <summary>¿Puedo recuperar mi contraseña?</summary>
                        <p>Sí. Usa la opción de recuperar acceso en el login. El sistema envía un enlace de restablecimiento al correo registrado.</p>
                    </details>
                </div>
            </section>

            <section class="help-section" id="viajes" aria-labelledby="ayuda-viajes">
                <div class="help-section__title">
                    <span>Viajes</span>
                    <h2 id="ayuda-viajes">Solicitudes y seguimiento</h2>
                </div>
                <div class="help-question-list">
                    <details class="help-question" open>
                        <summary>¿Cómo se calcula la tarifa?</summary>
                        <p>La tarifa se estima con la distancia y el tiempo proyectado del trayecto. La verás antes de confirmar la solicitud.</p>
                    </details>
                    <details class="help-question">
                        <summary>¿Dónde veo el estado del viaje?</summary>
                        <p>La pantalla del viaje muestra si está buscando conductor, aceptado, recogiendo, en curso, completado o cancelado.</p>
                    </details>
                    <details class="help-question">
                        <summary>¿Qué pasa si el conductor no llega?</summary>
                        <p>Puedes cancelar el viaje desde la pantalla activa. Si necesitas dejar constancia del caso, envía una solicitud desde Contacto.</p>
                    </details>
                </div>
            </section>

            <section class="help-section" id="conductores" aria-labelledby="ayuda-conductores">
                <div class="help-section__title">
                    <span>Conductores</span>
                    <h2 id="ayuda-conductores">Registro y datos del conductor</h2>
                </div>
                <div class="help-question-list">
                    <details class="help-question">
                        <summary>¿Cómo funciona el registro de conductor?</summary>
                        <p>El conductor registra sus datos, vehículo y documentos. En esta versión académica, la validación se simula para demostrar el flujo.</p>
                    </details>
                    <details class="help-question">
                        <summary>¿Qué información ve el pasajero?</summary>
                        <p>El sistema muestra datos del conductor asignado y del vehículo para que el pasajero pueda reconocer el servicio.</p>
                    </details>
                </div>
            </section>

            <section class="help-section" id="saldo" aria-labelledby="ayuda-saldo">
                <div class="help-section__title">
                    <span>Saldo</span>
                    <h2 id="ayuda-saldo">Billetera del conductor</h2>
                </div>
                <div class="help-question-list">
                    <details class="help-question">
                        <summary>¿Para qué sirve la billetera del conductor?</summary>
                        <p>Permite revisar saldo disponible, recargas simuladas y comisiones descontadas por viajes completados.</p>
                    </details>
                    <details class="help-question">
                        <summary>¿Las recargas son pagos reales?</summary>
                        <p>No. Las recargas están simuladas para la exposición académica y para demostrar el flujo de saldo.</p>
                    </details>
                </div>
            </section>

            <section class="help-section" id="soporte" aria-labelledby="ayuda-soporte">
                <div class="help-section__title">
                    <span>Soporte</span>
                    <h2 id="ayuda-soporte">Consultas y reportes</h2>
                </div>
                <div class="help-question-list">
                    <details class="help-question">
                        <summary>¿Cómo contacto al equipo?</summary>
                        <p>Usa la página de Contacto para enviar una consulta, reclamo, sugerencia o reporte. También puedes escribir al correo oficial.</p>
                    </details>
                </div>
            </section>
        </div>

        <section class="help-contact" aria-label="Contacto de soporte">
            <div>
                <h2>¿Tu caso necesita revisión?</h2>
                <p>Envía una consulta con los datos del caso para que quede registrada y pueda revisarse con contexto.</p>
            </div>
            <a href="{{ route('contacto') }}" class="info-action info-action--light">Ir a contacto</a>
        </section>
    </div>
</section>
@endsection
