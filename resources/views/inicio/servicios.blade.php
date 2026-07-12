@extends('layouts.main')

@php
    $userType = auth()->user()?->tipo_usuario;
    $actionRoute = match ($userType) {
        'pasajero' => route('pasajero.solicitarViaje'),
        'conductor' => route('conductor.solicitudes'),
        default => route('login'),
    };
    $actionText = match ($userType) {
        'pasajero' => 'Solicitar viaje',
        'conductor' => 'Ver solicitudes disponibles',
        default => 'Iniciar sesión',
    };
@endphp

@section('content')
<section class="services-page">
    <div class="services-page__inner">
        <header class="info-hero services-hero">
            <div class="info-hero__main">
                <div class="info-hero__content">
                    <span class="info-page__eyebrow">Servicios</span>
                    <h1>Todo lo que necesitas para realizar y gestionar tu viaje</h1>
                    <p>
                        Solicita un mototaxi, sigue el recorrido y conserva la información de tus viajes desde una sola plataforma.
                    </p>
                </div>

                <aside class="info-hero__action" aria-label="Acción recomendada">
                    <p>{{ $userType === 'conductor'
                        ? 'Revisa las solicitudes que puedes atender desde tu cuenta.'
                        : ($userType === 'pasajero'
                            ? 'Continúa desde tu cuenta de pasajero.'
                            : 'Accede a tu cuenta para usar las funciones de viaje.') }}</p>
                    <a href="{{ $actionRoute }}" class="info-action">{{ $actionText }}</a>
                </aside>
            </div>
        </header>

        <article class="services-primary" aria-labelledby="servicio-principal">
            <div class="services-primary__content">
                <span class="services-primary__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 16h14l-1.3-4.2A3 3 0 0 0 14.84 9H9.16a3 3 0 0 0-2.86 2.8L5 16Z" />
                        <path d="M7 16v2m10-2v2M8 13h8" stroke-linecap="round" />
                    </svg>
                </span>
                <div>
                    <h2 id="servicio-principal">Solicitar un viaje</h2>
                    <p>
                        El pasajero indica origen y destino, revisa la tarifa estimada y envía la solicitud para que un conductor disponible la acepte.
                    </p>
                </div>
                <ul class="services-benefits">
                    <li>Elegir origen y destino.</li>
                    <li>Conocer la tarifa estimada antes de confirmar.</li>
                    <li>Esperar la aceptación del conductor desde la misma pantalla.</li>
                </ul>
            </div>

            <div class="services-route-card" aria-label="Resumen del flujo de solicitud">
                <div class="services-route-card__row">
                    <span class="route-dot route-dot--start" aria-hidden="true"></span>
                    <div>
                        <strong>Origen</strong>
                        <span>Ubicación del pasajero</span>
                    </div>
                </div>
                <div class="services-route-line" aria-hidden="true"></div>
                <div class="services-route-card__row">
                    <span class="route-dot route-dot--end" aria-hidden="true"></span>
                    <div>
                        <strong>Destino</strong>
                        <span>Punto de llegada</span>
                    </div>
                </div>
                <div class="services-route-meta">
                    <span>Tarifa estimada</span>
                    <span>Aceptación del conductor</span>
                </div>
            </div>
        </article>

        <section class="services-section" aria-labelledby="servicios-complementarios">
            <div class="services-section__header">
                <h2 id="servicios-complementarios">Servicios que acompañan el viaje</h2>
                <p>Funciones reales del sistema para pasajeros y conductores.</p>
            </div>

            <div class="services-grid">
                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 21s6-5.3 6-11a6 6 0 1 0-12 0c0 5.7 6 11 6 11Z" />
                            <circle cx="12" cy="10" r="2.4" />
                        </svg>
                    </span>
                    <h3>Seguimiento del viaje</h3>
                    <p>Consulta el estado del viaje y la ubicación del conductor mientras avanza el recorrido.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 5h14v14H5z" />
                            <path d="M8 9h8M8 13h8M8 17h4" stroke-linecap="round" />
                        </svg>
                    </span>
                    <h3>Historial y comprobantes</h3>
                    <p>Revisa viajes completados, montos, estados y comprobantes cuando necesites consultar evidencia.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="m12 3 2.5 5 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8L12 3Z" />
                        </svg>
                    </span>
                    <h3>Calificación</h3>
                    <p>Después del viaje, el pasajero puede registrar una calificación sobre la atención recibida.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 11V8a5 5 0 0 1 10 0v3" />
                            <path d="M5 11h14v8H5z" />
                            <path d="M12 15v1.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    <h3>Datos del conductor</h3>
                    <p>El sistema muestra información del conductor, vehículo y documentos simulados para la exposición académica.</p>
                </article>

                <article class="service-card">
                    <span class="service-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 8v8m-4-4h8" stroke-linecap="round" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                    <h3>Billetera del conductor</h3>
                    <p>El conductor puede revisar saldo, recargas simuladas y comisiones descontadas por viajes completados.</p>
                </article>
            </div>
        </section>

        <section class="journey-steps" aria-labelledby="como-funciona-servicios">
            <div class="journey-steps__header">
                <span class="info-page__eyebrow">Cómo funciona</span>
                <h2 id="como-funciona-servicios">De la solicitud al historial</h2>
            </div>
            <ol class="journey-steps__list">
                <li>
                    <span>1</span>
                    <strong>Indica origen y destino</strong>
                    <p>El pasajero completa los puntos del viaje.</p>
                </li>
                <li>
                    <span>2</span>
                    <strong>Confirma la solicitud</strong>
                    <p>El sistema muestra la tarifa estimada.</p>
                </li>
                <li>
                    <span>3</span>
                    <strong>Un conductor acepta</strong>
                    <p>La pantalla actualiza el estado del viaje.</p>
                </li>
                <li>
                    <span>4</span>
                    <strong>Completa y revisa</strong>
                    <p>El historial conserva el registro del servicio.</p>
                </li>
            </ol>
        </section>

        <section class="services-action" aria-label="Acción principal">
            <div>
                <h2>{{ $userType === 'conductor' ? 'Revisa las solicitudes disponibles' : 'Empieza desde la pantalla correcta' }}</h2>
                <p>
                    {{ $userType === 'pasajero'
                        ? 'Puedes solicitar un viaje con tu cuenta activa.'
                        : ($userType === 'conductor'
                            ? 'Accede a las solicitudes y atiende viajes desde tu panel de conductor.'
                            : 'Inicia sesión para solicitar un viaje o revisar tus opciones como conductor.') }}
                </p>
            </div>
            <a href="{{ $actionRoute }}" class="info-action info-action--dark">{{ $actionText }}</a>
        </section>
    </div>
</section>
@endsection
