@extends('layouts.main')

@section('content')
<section class="info-page info-page--services">
    <div class="info-page__inner">
        <header class="info-page__header">
            <span class="info-page__eyebrow">Servicios</span>
            <h1>Lo que Altokke resuelve en cada viaje</h1>
            <p>
                Una forma directa de pedir mototaxi, seguir el estado del viaje y guardar el historial sin depender de llamadas.
            </p>
        </header>

        <div class="services-layout">
            <article class="service-main-card">
                <div class="service-main-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 16h14l-1.3-4.2A3 3 0 0 0 14.84 9H9.16a3 3 0 0 0-2.86 2.8L5 16Z" />
                        <path d="M7 16v2m10-2v2M8 13h8" stroke-linecap="round" />
                    </svg>
                </div>
                <div>
                    <h2>Solicitar un mototaxi</h2>
                    <p>
                        El pasajero elige origen y destino, revisa la tarifa estimada y envía la solicitud para que un conductor disponible la acepte.
                    </p>
                </div>
            </article>

            <div class="services-grid" aria-label="Funciones principales">
                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 21s6-5.3 6-11a6 6 0 1 0-12 0c0 5.7 6 11 6 11Z" />
                            <circle cx="12" cy="10" r="2.4" />
                        </svg>
                    </span>
                    <h2>Seguimiento del viaje</h2>
                    <p>La pantalla muestra el estado y la ubicación del conductor mientras el viaje avanza.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 5h14v14H5z" />
                            <path d="M8 9h8M8 13h8M8 17h4" stroke-linecap="round" />
                        </svg>
                    </span>
                    <h2>Historial y comprobantes</h2>
                    <p>Los viajes completados quedan registrados para revisar rutas, montos, estados y comprobantes.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="m12 3 2.5 5 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8L12 3Z" />
                        </svg>
                    </span>
                    <h2>Calificación</h2>
                    <p>Después del viaje, el pasajero puede calificar la experiencia para dejar evidencia del servicio recibido.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 11V8a5 5 0 0 1 10 0v3" />
                            <path d="M5 11h14v8H5z" />
                            <path d="M12 15v1.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    <h2>Datos del conductor</h2>
                    <p>El sistema conserva datos del conductor, vehículo y documentos simulados para la demostración académica.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 8v8m-4-4h8" stroke-linecap="round" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                    <h2>Billetera del conductor</h2>
                    <p>El conductor puede revisar saldo, recargas simuladas y comisiones descontadas por viajes completados.</p>
                </article>
            </div>
        </div>

        <section class="info-steps" aria-labelledby="servicios-flujo">
            <div>
                <span class="info-page__eyebrow">Flujo del viaje</span>
                <h2 id="servicios-flujo">De la solicitud al historial</h2>
            </div>
            <ol class="info-steps__list">
                <li><span>1</span>El pasajero indica origen y destino.</li>
                <li><span>2</span>Un conductor disponible acepta la solicitud.</li>
                <li><span>3</span>El viaje cambia de estado hasta completarse.</li>
                <li><span>4</span>El historial conserva el registro para consultas y reportes.</li>
            </ol>
        </section>
    </div>
</section>
@endsection
