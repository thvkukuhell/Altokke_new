@extends('layouts.main')

@section('content')
<section class="info-page info-page--help">
    <div class="info-page__inner">
        <header class="info-page__header info-page__header--split">
            <div>
                <span class="info-page__eyebrow">Ayuda</span>
                <h1>Resuelve dudas antes, durante y después del viaje</h1>
            </div>
            <p>
                Revisa respuestas rápidas sobre cuenta, viajes, saldo del conductor y soporte. Si necesitas enviar un caso, usa el formulario de contacto.
            </p>
        </header>

        <div class="help-layout">
            <aside class="help-sidebar" aria-label="Categorías de ayuda">
                <a href="#cuenta">Cuenta</a>
                <a href="#viajes">Viajes</a>
                <a href="#conductor">Conductor</a>
                <a href="#soporte">Soporte</a>
            </aside>

            <div class="help-content">
                <section class="help-group" id="cuenta" aria-labelledby="ayuda-cuenta">
                    <h2 id="ayuda-cuenta">Cuenta y acceso</h2>
                    <details>
                        <summary>¿Qué necesito para pedir un viaje?</summary>
                        <p>Necesitas una cuenta de pasajero. Después de iniciar sesión, puedes ingresar origen, destino y enviar la solicitud.</p>
                    </details>
                    <details>
                        <summary>¿Puedo recuperar mi contraseña?</summary>
                        <p>Sí. Usa la opción de recuperar acceso en el login. El sistema envía un enlace de restablecimiento al correo registrado.</p>
                    </details>
                </section>

                <section class="help-group" id="viajes" aria-labelledby="ayuda-viajes">
                    <h2 id="ayuda-viajes">Viajes y seguimiento</h2>
                    <details open>
                        <summary>¿Cómo se calcula la tarifa?</summary>
                        <p>La tarifa se estima con la distancia y el tiempo proyectado del trayecto. La verás antes de confirmar la solicitud.</p>
                    </details>
                    <details>
                        <summary>¿Dónde veo el estado del viaje?</summary>
                        <p>La pantalla del viaje muestra si está buscando conductor, aceptado, recogiendo, en curso, completado o cancelado.</p>
                    </details>
                    <details>
                        <summary>¿Qué pasa si el conductor no llega?</summary>
                        <p>Puedes cancelar el viaje desde la pantalla activa. Si necesitas dejar constancia del caso, envía una solicitud desde Contacto.</p>
                    </details>
                </section>

                <section class="help-group" id="conductor" aria-labelledby="ayuda-conductor">
                    <h2 id="ayuda-conductor">Conductores y saldo</h2>
                    <details>
                        <summary>¿Cómo funciona el registro de conductor?</summary>
                        <p>El conductor registra sus datos, vehículo y documentos. En esta versión académica, la validación se simula para demostrar el flujo.</p>
                    </details>
                    <details>
                        <summary>¿Para qué sirve la billetera del conductor?</summary>
                        <p>Permite revisar saldo disponible, recargas simuladas y comisiones descontadas por viajes completados.</p>
                    </details>
                </section>

                <section class="help-group" id="soporte" aria-labelledby="ayuda-soporte">
                    <h2 id="ayuda-soporte">Soporte</h2>
                    <details>
                        <summary>¿Cómo contacto al equipo?</summary>
                        <p>Usa la página de Contacto para enviar una consulta, reclamo, sugerencia o reporte. También puedes escribir al correo oficial.</p>
                    </details>
                </section>
            </div>
        </div>

        <div class="help-cta">
            <div>
                <h2>¿Necesitas enviar un caso?</h2>
                <p>El formulario de contacto guarda tu solicitud para que el equipo pueda revisarla.</p>
            </div>
            <a href="{{ route('contacto') }}" class="btn-auth">Ir a contacto</a>
        </div>
    </div>
</section>
@endsection
