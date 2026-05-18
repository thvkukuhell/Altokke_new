@extends('layouts.main')

@section('content')

{{--HERO--}}
<section class="hero" id="inicio">
    <div class="hero-bg-dots"></div>
    <div class="hero-bg-glow"></div>

    <div class="hero-contenido">
        <div class="hero-texto">
            <div class="hero-chip">
                <span class="hero-chip-dot"></span>
                Disponible en Bagua · Amazonas
            </div>

            <h1>
                Tu mototaxi,<br>
                <span>cuando lo necesites.</span>
            </h1>

            <p>
                Conectamos pasajeros con conductores verificados en Bagua.
                Rápido, seguro y sin complicaciones — desde tu celular.
            </p>

            <div class="hero-botones">
                <a href="{{ route('login') }}" class="btn-hero-primario">Pedir mototaxi ahora</a>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                </a>
                <a href="{{ route('eleccion_registro') }}" class="btn-hero-secundario">Ser conductor</a>
            </div>

            <div class="hero-prueba">
                <div class="hero-avatares">
                    <span>🧑</span><span>👩</span><span>👨</span><span>👩‍🦱</span>
                </div>
                <span>+200 conductores activos en Bagua</span>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card">
                <p class="hero-card-titulo">¿A dónde vamos?</p>
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
                        <div class="precio-numero">S/ 3.50</div>
                        <div class="precio-label">Tarifa estimada · ~5 min</div>
                    </div>
                    <span class="precio-badge">Mototaxi</span>
                </div>
                <button class="hero-card-btn">Confirmar viaje</button>
            </div>

            <div class="hero-chip-conductor">
                <span class="hero-chip-avatar">🏍️</span>
                <div>
                    <strong>Jorge R.</strong>
                    <span>A 3 cuadras · ★ 4.9</span>
                </div>
                <div class="hero-chip-eta">2 min</div>
            </div>
        </div>
    </div>
</section>

{{--CÓMO FUNCIONA--}}
<section class="seccion como-funciona" id="como-funciona">
    <div class="seccion-inner">
        <div class="seccion-cabecera reveal">
            <span class="seccion-chip">Simple y rápido</span>
            <h2 class="seccion-titulo">Tres pasos para tu viaje</h2>
            <p class="seccion-sub">Sin llamadas, sin esperas innecesarias. Solo ingresa tu destino y listo.</p>
        </div>

        <div class="pasos-grid">
            <div class="paso-card reveal reveal-delay-1">
                <div class="paso-numero">01</div>
                <div class="paso-icono-wrap">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                    </svg>
                </div>
                <h3>Regístrate gratis</h3>
                <p>Crea tu cuenta con tu nombre y celular en menos de 1 minuto. Sin costos ocultos.</p>
            </div>

            <div class="paso-card paso-card-destacado reveal reveal-delay-2">
                <div class="paso-numero">02</div>
                <div class="paso-icono-wrap">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <circle cx="12" cy="10" r="3" />
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                    </svg>
                </div>
                <h3>Indica tu destino</h3>
                <p>Escribe a dónde quieres ir. Verás la tarifa estimada antes de confirmar.</p>
            </div>

            <div class="paso-card reveal reveal-delay-3">
                <div class="paso-numero">03</div>
                <div class="paso-icono-wrap">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <h3>¡Tu moto llega!</h3>
                <p>Un conductor verificado acepta tu solicitud y va a recogerte. Sigue el estado en tiempo real.</p>
            </div>
        </div>
    </div>
</section>

{{--SEGURIDAD--}}
<section class="seccion seguridad">
    <div class="seccion-inner">
        <div class="seccion-cabecera seccion-cabecera-claro reveal">
            <span class="seccion-chip seccion-chip-claro">Tu seguridad primero</span>
            <h2 class="seccion-titulo seccion-titulo-claro">Viajas protegido,<br>siempre.</h2>
            <p class="seccion-sub seccion-sub-claro">Cada viaje es registrado con datos del conductor, placa y ruta.</p>
        </div>

        <div class="seguridad-grid">
            <div class="seguridad-card reveal reveal-delay-1">
                <div class="seguridad-icono">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                </div>
                <h3>Conductores verificados</h3>
                <p>Cada conductor pasa por verificación de DNI y placa antes de operar en la plataforma.</p>
            </div>

            <div class="seguridad-card reveal reveal-delay-2">
                <div class="seguridad-icono">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <circle cx="12" cy="10" r="3" />
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                    </svg>
                </div>
                <h3>Seguimiento en tiempo real</h3>
                <p>Sigue cada viaje en vivo. Sabe exactamente dónde está tu mototaxi en cada momento.</p>
            </div>

            <div class="seguridad-card reveal reveal-delay-3">
                <div class="seguridad-icono">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.63 19a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.33 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                </div>
                <h3>Soporte directo</h3>
                <p>¿Algo salió mal? Contacta soporte desde la app. Estamos disponibles para ayudarte.</p>
            </div>
        </div>
    </div>
</section>

{{--SOBRE NOSOTROS--}}
<section class="seccion sobre-nosotros-sec" id="sobre-nosotros">
    <div class="seccion-inner">
        <div class="sobre-grid">
            <div class="sobre-texto reveal">
                <span class="seccion-chip">Quiénes somos</span>
                <h2 class="seccion-titulo" style="margin-top:14px;">Nació en Bagua.<br>Creado para Bagua.</h2>
                <p>Somos estudiantes de la <strong>Universidad Nacional Toribio Rodríguez de Mendoza</strong> que vimos
                    el caos del transporte en nuestra ciudad y decidimos resolverlo con tecnología.</p>
                <p>Altokke conecta pasajeros con conductores de forma transparente — con tarifas claras, conductores
                    verificados y sin intermediarios.</p>
                <div class="sobre-chips">
                    <span class="sobre-chip">✓ Conductores verificados</span>
                    <span class="sobre-chip">✓ Tarifas transparentes</span>
                    <span class="sobre-chip">✓ Disponible 24/7</span>
                    <span class="sobre-chip">✓ Hecho en Bagua</span>
                </div>
            </div>

            <div class="sobre-tarjetas reveal reveal-delay-1">
                <div class="sobre-tarjeta">
                    <div class="sobre-tarjeta-icono">🎯</div>
                    <h3>Nuestra misión</h3>
                    <p>Conectar ciudadanos con conductores de confianza, rápido y desde cualquier celular.</p>
                </div>
                <div class="sobre-tarjeta sobre-tarjeta-verde">
                    <div class="sobre-tarjeta-icono">🌎</div>
                    <h3>Nuestra visión</h3>
                    <p>Ser la plataforma de transporte de referencia en toda la región Amazonas.</p>
                </div>
                <div class="sobre-tarjeta sobre-tarjeta-oscura">
                    <div class="sobre-tarjeta-icono">🏛️</div>
                    <h3>UNTRM</h3>
                    <p>Universidad Nacional Toribio Rodríguez de Mendoza · Chachapoyas, Amazonas.</p>
                </div>
                <div class="sobre-tarjeta">
                    <div class="sobre-tarjeta-icono">🛡️</div>
                    <h3>Seguridad</h3>
                    <p>Cada viaje registrado con datos del conductor, placa y ruta completa.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{--CTA FINAL--}}
<section class="cta-final">
    <div class="cta-bg-dots"></div>
    <div class="cta-inner reveal">
        <span class="seccion-chip seccion-chip-claro" style="margin-bottom:20px; display:inline-block;">Empieza
            ahora</span>
        <h2>¿Listo para tu primer<br>viaje en Bagua?</h2>
        <p>Únete a los pasajeros que ya viajan más cómodo. Gratis, rápido y seguro.</p>
        <div class="cta-botones">
            <a href="{{ url('/auth/login') }}" class="btn-cta-primario">
                Pedir mototaxi
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
            </a>
            <a href="{{ url('/auth/eleccion_registro') }}" class="btn-cta-secundario">
                Registrarme como conductor
            </a>
        </div>
    </div>
</section>

<script>
// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
            observer.unobserve(e.target);
        }
    });
}, {
    threshold: 0.12
});

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// Header scroll
const header = document.querySelector('header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 40) header.classList.add('scrolled');
    else header.classList.remove('scrolled');
});
</script>

@endsection