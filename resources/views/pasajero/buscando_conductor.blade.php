@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA: Exactamente igual a solicitar_viaje --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-buscando">-- min</div>
                        <div class="eta-unidad">Ruta</div>
                    </div>
                    <div class="eta-unidad" id="distancia-buscando">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-buscando">Ruta estimada</div>
                <div class="eta-detalle" id="detalle-ruta-buscando">Buscando conductor cercano</div>
            </div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🔍</span>
                <span id="ubicacion-texto">Buscando la mototaxi más cercana en Bagua...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in" title="Acercar">+</button>
                <button class="mapa-boton-zoom" id="zoom-out" title="Alejar">−</button>
            </div>
        </div>

        {{-- Panel derecho: Tu tarjeta original de buscando --}}
        <div class="buscando-centro" style="margin-top: 0;">

            <div class="icono-buscando">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M5 17H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11l4 4v4" />
                    <circle cx="7" cy="17" r="2" />
                    <circle cx="17" cy="17" r="2" />
                    <path d="M9 17h6" />
                </svg>
            </div>

            <h2 class="buscando-titulo" id="buscando-titulo">Buscando mototaxi cercano...</h2>
            <span class="buscando-tiempo buscando-tiempo-ajax" id="buscando-estado-badge">Estado: buscando</span>
            <span class="buscando-tiempo">⏱ Menos de 2 minutos</span>
            <p class="buscando-desc">
                Te conectamos con el conductor más cercano disponible en Bagua
            </p>

            <div class="ajax-estado buscando-ajax" id="buscando-ajax-estado" role="status">
                <span class="ajax-punto"></span>
                <span id="buscando-ajax-texto">Consultando estado cada 7 segundos</span>
            </div>

            <div class="progreso-wrap">
                <div class="progreso-barra"></div>
            </div>

            <div class="tarjeta-viaje">
                <div class="fila-dato">
                    <span>Origen</span>
                    <strong>{{ $viaje['origen'] ?? '—' }}</strong>
                </div>
                <hr class="separador">
                <div class="fila-dato">
                    <span>Destino</span>
                    <strong>{{ $viaje['destino'] ?? '—' }}</strong>
                </div>
                <hr class="separador">
                <div class="fila-dato" style="margin:0;">
                    <span>Tarifa estimada</span>
                    <strong style="font-size:18px; color:var(--p-verde-mid); letter-spacing:-0.5px;">
                        S/ {{ number_format($viaje['tarifa'] ?? 3.00, 2) }}
                    </strong>
                </div>
            </div>

            <p class="nota-cancelar">Si cancelas ahora no se te cobrará nada.</p>

            <form method="POST" action="{{ route('pasajero.cancelarViaje') }}">
                @csrf
                <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                <button type="submit" class="btn btn-outline">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                    Cancelar solicitud
                </button>
            </form>

        </div>
    </div>
</div>

{{-- Leaflet CSS y JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@include('mapa.partials.leaflet_helpers')

<script>
let mapa;
let lineaRuta;

const origenBuscandoReal = AltokkeMapa.puntoValido(
    @json($viaje['origen_lat'] ?? null),
    @json($viaje['origen_lng'] ?? null)
);
const destinoBuscandoReal = AltokkeMapa.puntoValido(
    @json($viaje['destino_lat'] ?? null),
    @json($viaje['destino_lng'] ?? null)
);
const origenBuscando = origenBuscandoReal || AltokkeMapa.puntoSeguro(
    @json($viaje['origen_lat'] ?? null),
    @json($viaje['origen_lng'] ?? null),
    AltokkeMapa.BAGUA
);
const destinoBuscando = destinoBuscandoReal || AltokkeMapa.puntoSeguro(
    @json($viaje['destino_lat'] ?? null),
    @json($viaje['destino_lng'] ?? null),
    AltokkeMapa.CAJARURO
);
const viajeIdActual = @json($viaje['id'] ?? '');
const estadoViajeUrl = @json(($viaje['id'] ?? 0) ? route('api.internal.viajes.show', $viaje['id']) : null);
let pollingEstado = null;
let consultandoEstado = false;

window.addEventListener('load', () => {
    document.querySelectorAll('.buscando-tiempo:not(#buscando-estado-badge)')
        .forEach((el) => { el.style.display = 'none'; });

    mapa = AltokkeMapa.crearMapa('mapa-solicitud-pasajero', origenBuscando, 15);
    if (!mapa) return;

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    if (origenBuscandoReal) {
        AltokkeMapa.crearMarcador(mapa, origenBuscando, 'origen', 'O', 'Tu origen');
    }

    if (destinoBuscandoReal) {
        AltokkeMapa.crearMarcador(mapa, destinoBuscando, 'destino', 'D', 'Tu destino');
    }

    pintarRutaBuscando();
    iniciarPollingEstado();

    if (window.Echo) {
        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('.ViajeAceptado', (data) => {
                const viajeId = data.viajeId || viajeIdActual;
                if (!viajeId) return;
                window.location.href = `/pasajero/enCurso/${viajeId}`;
            });

        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('.ViajeActualizado', (data) => {
                if (!data.estado) return;
            });
    }
});

async function pintarRutaBuscando() {
    const estado = document.getElementById('estado-ruta-buscando');
    const detalle = document.getElementById('detalle-ruta-buscando');
    if (!origenBuscandoReal || !destinoBuscandoReal) {
        if (estado) estado.textContent = 'Coordenadas pendientes';
        if (detalle) detalle.textContent = 'No se encontraron puntos validos para este viaje';
        AltokkeMapa.ajustarVista(mapa, [origenBuscando, destinoBuscando]);
        return;
    }

    if (estado) estado.textContent = 'Calculando ruta';

    const ruta = await AltokkeMapa.consultarRuta(origenBuscando, destinoBuscando);
    lineaRuta = AltokkeMapa.dibujarRuta(mapa, lineaRuta, ruta, {
        color: '#2d6a2d',
        weight: 6,
        opacity: 0.9,
    });

    if (lineaRuta) {
        AltokkeMapa.ajustarVista(mapa, [origenBuscando, destinoBuscando], [50, 50]);
    }

    document.getElementById('eta-buscando').textContent = `${ruta.duracion_min || '--'} min`;
    document.getElementById('distancia-buscando').textContent = `${Number(ruta.distancia_km || 0).toFixed(1)} km`;
    if (estado) estado.textContent = ruta.ok ? 'Ruta estimada' : 'Sin ruta disponible';
    if (detalle) detalle.textContent = ruta.ok ? 'Ruta real calculada' : 'Usando linea simple entre puntos';
}

function pintarEstadoAjax(mensaje, tipo = 'normal') {
    const estado = document.getElementById('buscando-ajax-estado');
    const texto = document.getElementById('buscando-ajax-texto');
    if (!estado || !texto) return;

    estado.setAttribute('data-tipo', tipo);
    texto.textContent = mensaje;
}

function detenerPollingEstado() {
    if (!pollingEstado) return;
    window.clearInterval(pollingEstado);
    pollingEstado = null;
}

function actualizarPanelEstado(viaje, conductor = null) {
    const titulo = document.getElementById('buscando-titulo');
    const badge = document.getElementById('buscando-estado-badge');
    const desc = document.querySelector('.buscando-desc');

    if (badge && viaje?.estado_label) {
        badge.textContent = `Estado: ${viaje.estado_label}`;
    }

    if (!titulo || !desc || !viaje?.estado) return;

    const textos = {
        buscando: {
            titulo: 'Buscando mototaxi cercano...',
            desc: 'Te conectamos con el conductor mas cercano disponible en Bagua',
        },
        aceptado: {
            titulo: 'Conductor asignado',
            desc: conductor?.nombre
                ? `${conductor.nombre} acepto tu solicitud. Abriendo el viaje en curso...`
                : 'Un conductor acepto tu solicitud. Abriendo el viaje en curso...',
        },
        recogiendo: {
            titulo: 'Conductor en camino',
            desc: 'El conductor va hacia tu punto de origen.',
        },
        en_curso: {
            titulo: 'Viaje en curso',
            desc: 'Tu viaje ya esta activo.',
        },
        completado: {
            titulo: 'Viaje completado',
            desc: 'El viaje finalizo correctamente.',
        },
        cancelado: {
            titulo: 'Solicitud cancelada',
            desc: 'La solicitud fue cancelada.',
        },
        expirado: {
            titulo: 'Solicitud expirada',
            desc: 'No se encontro conductor disponible para este viaje.',
        },
    };

    const contenido = textos[viaje.estado] || textos.buscando;
    titulo.textContent = contenido.titulo;
    desc.textContent = contenido.desc;
}

async function consultarEstadoViaje() {
    if (!estadoViajeUrl || consultandoEstado) return;
    consultandoEstado = true;
    pintarEstadoAjax('Consultando estado...', 'cargando');

    try {
        const respuesta = await fetch(estadoViajeUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!respuesta.ok) {
            throw new Error('No se pudo consultar el estado');
        }

        const data = await respuesta.json();
        const viaje = data.data || null;
        if (!data.ok || !viaje) {
            throw new Error(data.message || 'Respuesta no valida');
        }

        actualizarPanelEstado(viaje, viaje.conductor);
        pintarEstadoAjax('Estado actualizado correctamente', 'ok');

        if (viaje.redirect_url) {
            detenerPollingEstado();
            window.setTimeout(() => {
                window.location.href = viaje.redirect_url;
            }, 900);
            return;
        }

        if (['completado', 'cancelado', 'expirado'].includes(viaje.estado)) {
            detenerPollingEstado();
            pintarEstadoAjax('Consulta detenida: viaje finalizado', 'ok');
        }
    } catch (error) {
        pintarEstadoAjax('No se pudo actualizar el estado. Intentaremos otra vez.', 'error');
    } finally {
        consultandoEstado = false;
    }
}

function iniciarPollingEstado() {
    if (!estadoViajeUrl || pollingEstado) return;
    consultarEstadoViaje();
    pollingEstado = window.setInterval(consultarEstadoViaje, 7000);
}

window.addEventListener('beforeunload', detenerPollingEstado);
</script>
@endsection
