@extends('layouts.main')

@section('content')

<section class="hero" id="inicio">
    <div class="hero-contenido">
        <div class="hero-texto reveal">
            <div class="hero-chip">
                <span class="hero-chip-dot"></span>
                Disponible en Bagua, Amazonas
            </div>

            <h1>
                Tu mototaxi,<br>
                <span>cuando lo necesites.</span>
            </h1>

            <p>
                Altokke conecta pasajeros con conductores verificados en Bagua. Pide un viaje, revisa la tarifa y sigue
                tu ruta desde el celular.
            </p>

            <div class="hero-botones">
                <a href="{{ route('registro_pasajero') }}" class="btn-hero-primario">Pedir mototaxi</a>
                <a href="{{ route('registro_conductor') }}" class="btn-hero-secundario">Ser conductor</a>
            </div>

            <div class="hero-prueba" aria-label="Indicadores de confianza">
                <span><strong>+200</strong> conductores activos</span>
                <span><strong>4.8</strong> calificacion promedio</span>
            </div>
        </div>

        <div class="hero-visual reveal reveal-delay-1" aria-label="Vista previa de solicitud de viaje">
            <div class="hero-card">
                <div class="hero-card-head">
                    <p class="hero-card-titulo">Viaje estimado</p>
                    <span class="precio-badge">Mototaxi</span>
                </div>

                <div class="hero-card-ruta">
                    <div class="hero-card-fila">
                        <span class="punto-hero punto-verde-hero"></span>
                        <span>Mercado Central, Bagua</span>
                    </div>
                    <div class="hero-card-fila">
                        <span class="punto-hero punto-rojo-hero"></span>
                        <span>Hospital Regional</span>
                    </div>
                </div>

                <div class="hero-card-precio">
                    <div>
                        <div class="precio-label">Tarifa estimada</div>
                        <div class="precio-numero">S/ 3.50</div>
                    </div>
                    <div class="hero-card-meta">
                        <strong>~5 min</strong>
                        <span>Conductor cercano</span>
                    </div>
                </div>

                <a href="{{ route('login') }}" class="hero-card-btn">Confirmar viaje</a>
            </div>

            <div class="hero-driver-card">
                <div class="hero-driver-avatar">🏍️</div>
                <div class="hero-driver-info">
                    <strong>Jorge R.</strong>
                    <span>A 3 cuadras · ★ 4.9</span>
                </div>
                <span class="hero-driver-time">2 min</span>
            </div>
        </div>
    </div>
</section>

<section class="seccion como-funciona" id="como-funciona">
    <div class="seccion-inner">
        <div class="seccion-cabecera reveal">
            <span class="seccion-chip">Como funciona</span>
            <h2 class="seccion-titulo">Tres pasos, cero friccion.</h2>
            <p class="seccion-sub">Una experiencia directa para pedir transporte local sin llamadas ni esperas confusas.</p>
        </div>

        <div class="pasos-grid">
            <div class="paso-card reveal reveal-delay-1">
                <div class="paso-numero">01</div>
                <div class="paso-icono-wrap">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                    </svg>
                </div>
                <h3>Registrate gratis</h3>
                <p>Crea tu cuenta con tus datos basicos y queda listo para pedir tu primer viaje.</p>
            </div>

            <div class="paso-card paso-card-destacado reveal reveal-delay-2">
                <div class="paso-numero">02</div>
                <div class="paso-icono-wrap">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <circle cx="12" cy="10" r="3" />
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                    </svg>
                </div>
                <h3>Indica tu destino</h3>
                <p>Escribe a donde quieres ir. Veras la tarifa estimada antes de confirmar.</p>
            </div>

            <div class="paso-card reveal reveal-delay-3">
                <div class="paso-numero">03</div>
                <div class="paso-icono-wrap">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <h3>Tu moto llega</h3>
                <p>Un conductor verificado acepta la solicitud y puedes seguir el estado del viaje.</p>
            </div>
        </div>
    </div>
</section>

<section class="seccion seguridad">
    <div class="seccion-inner">
        <div class="seccion-cabecera seccion-cabecera-claro reveal">
            <span class="seccion-chip seccion-chip-claro">Seguridad</span>
            <h2 class="seccion-titulo seccion-titulo-claro">Viaja con informacion clara.</h2>
            <p class="seccion-sub seccion-sub-claro">Cada solicitud muestra datos clave del viaje para que tomes mejores decisiones.</p>
        </div>

        <div class="seguridad-grid">
            <div class="seguridad-card reveal reveal-delay-1">
                <div class="seguridad-icono">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                </div>
                <h3>Conductores verificados</h3>
                <p>Validacion de identidad, placa y datos principales antes de operar en la plataforma.</p>
            </div>

            <div class="seguridad-card reveal reveal-delay-2">
                <div class="seguridad-icono">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <circle cx="12" cy="10" r="3" />
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                    </svg>
                </div>
                <h3>Ruta visible</h3>
                <p>Consulta el origen, destino y avance del viaje sin depender de llamadas externas.</p>
            </div>

            <div class="seguridad-card reveal reveal-delay-3">
                <div class="seguridad-icono">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.63 19a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.33 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                </div>
                <h3>Soporte directo</h3>
                <p>Si algo cambia durante el viaje, puedes contactar soporte desde la aplicacion.</p>
            </div>
        </div>
    </div>
</section>

<section class="seccion sobre-nosotros-sec" id="sobre-nosotros">
    <div class="seccion-inner">
        <div class="sobre-grid">
            <div class="sobre-texto reveal">
                <span class="seccion-chip">Hecho en Bagua</span>
                <h2 class="seccion-titulo">Transporte local con una experiencia mas simple.</h2>
                <p>Altokke nace para ordenar la forma en que pasajeros y conductores se conectan en la ciudad.</p>
                <p>La plataforma prioriza tarifas claras, conductores identificados y un flujo de solicitud facil de entender.</p>
                <div class="sobre-chips">
                    <span class="sobre-chip">Conductores verificados</span>
                    <span class="sobre-chip">Tarifas claras</span>
                    <span class="sobre-chip">Disponible 24/7</span>
                    <span class="sobre-chip">Pensado para Bagua</span>
                </div>
            </div>

            <div class="sobre-tarjetas reveal reveal-delay-1">
                <div class="sobre-tarjeta">
                    <span class="sobre-tarjeta-kicker">Mision</span>
                    <h3>Conectar rapido</h3>
                    <p>Reducir la friccion entre pedir un mototaxi y empezar el viaje.</p>
                </div>
                <div class="sobre-tarjeta sobre-tarjeta-verde">
                    <span class="sobre-tarjeta-kicker">Vision</span>
                    <h3>Escalar con confianza</h3>
                    <p>Convertirse en una opcion de transporte confiable para la region Amazonas.</p>
                </div>
                <div class="sobre-tarjeta sobre-tarjeta-oscura">
                    <span class="sobre-tarjeta-kicker">Origen</span>
                    <h3>UNTRM</h3>
                    <p>Proyecto academico orientado a resolver un problema real de movilidad local.</p>
                </div>
                <div class="sobre-tarjeta">
                    <span class="sobre-tarjeta-kicker">Control</span>
                    <h3>Viajes registrados</h3>
                    <p>Cada viaje conserva informacion esencial del conductor, placa y ruta.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-final">
    <div class="cta-inner reveal">
        <span class="seccion-chip seccion-chip-claro">Empieza ahora</span>
        <h2>Listo para pedir tu primer viaje en Bagua.</h2>
        <p>Registrate gratis y empieza con una experiencia mas clara para moverte por la ciudad.</p>
        <div class="cta-botones">
            <a href="{{ url('/auth/login') }}" class="btn-cta-primario">Pedir mototaxi</a>
            <a href="{{ url('/auth/eleccion_registro') }}" class="btn-cta-secundario">Registrarme</a>
        </div>
    </div>
</section>

@vite(['resources/js/inicio/inicio.js'])

@endsection
