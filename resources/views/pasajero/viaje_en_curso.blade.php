@extends('layouts.main')
@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@include('mapa.partials.leaflet_helpers')

<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA --}}
        <div class="mapa-decorativo">
            <div id="mapa-en-curso"></div>
            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-pasajero">-- min</div>
                        <div class="eta-unidad">Llegada</div>
                    </div>
                    <div class="eta-unidad" id="distancia-pasajero">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-pasajero">Calculando ruta</div>
                <div class="eta-detalle" id="detalle-ruta-pasajero">Conectando con el mapa</div>
            </div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🛺</span>
                <span id="estado-texto">Conductor en camino...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in">+</button>
                <button class="mapa-boton-zoom" id="zoom-out">−</button>
            </div>
        </div>

        {{-- PANEL DERECHO --}}
        <div class="panel-solicitud" style="display:flex; flex-direction:column; gap:16px;">

            {{-- Conductor --}}
            <div>
                <p class="panel-solicitud-titulo">Tu conductor</p>
                <p class="panel-solicitud-sub">Sigue el trayecto en el mapa</p>

                <div class="conductor-card-encurso">
                    <div class="avatar-conductor">{{ $iniciales ?? '—' }}</div>
                    <div class="conductor-info">
                        <div class="conductor-nombre-encurso">{{ $conductor['nombre'] ?? '—' }}</div>
                        <div class="conductor-dato-encurso">
                            ★ {{ number_format($conductor['calificacion'] ?? 0, 1) }}
                            &nbsp;·&nbsp; {{ $conductor['modelo'] ?? '—' }}
                        </div>
                    </div>
                    <div class="placa-encurso">{{ $conductor['placa'] ?? '—' }}</div>
                </div>
            </div>

            {{-- Datos del viaje --}}
            <div class="ruta-selector" style="margin-bottom:0;">
                <div class="ruta-fila">
                    <div class="punto punto-verde"></div>
                    <span style="font-size:13px; color:var(--text);">{{ $viaje['origen'] ?? '—' }}</span>
                </div>
                <div class="ruta-fila">
                    <div class="punto punto-rojo"></div>
                    <span style="font-size:13px; color:var(--text);">{{ $viaje['destino'] ?? '—' }}</span>
                </div>
            </div>

            {{-- Tarifa --}}
            <div class="tarifa-caja">
                <div class="tarifa-numero">S/ {{ number_format($viaje['tarifa'] ?? 0, 2) }}</div>
                <div class="tarifa-right">
                    <div class="tarifa-label">Tarifa estimada</div>
                    <div class="tarifa-detalle" id="tarifa-detalle-curso">{{ ucfirst($viaje['metodo_pago'] ?? 'efectivo') }}</div>
                </div>
            </div>

            <div class="mapa-resumen-ruta">
                <div class="dato-ruta">
                    <span>Distancia</span>
                    <strong id="panel-distancia-pasajero">-- km</strong>
                </div>
                <div class="dato-ruta">
                    <span>ETA</span>
                    <strong id="panel-tiempo-pasajero">-- min</strong>
                </div>
            </div>

            {{-- Timeline de pasos --}}
            <div class="tarjeta-viaje">
                <p class="campo-label" style="margin-bottom:14px;">Estado del viaje</p>
                <div class="timeline" id="timeline-pasos">
                    @foreach($pasos as $i => $paso)
                        <div class="paso {{ $paso['estado'] }}" id="paso-{{ $i }}">
                            <div class="paso-icono">
                                @if($paso['estado'] === 'hecho')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div>
                                <div class="paso-titulo">{{ $paso['titulo'] }}</div>
                                <div class="paso-sub">{{ $paso['sub'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Botón calificar — oculto hasta que el viaje se complete --}}
            <div id="seccion-calificar" style="display:none;">
                <a href="{{ route('pasajero.calificar', $viaje['id'] ?? 0) }}"
                   class="btn btn-verde btn-ancho"
                   style="font-size:15px; padding:14px;">
                    ⭐ Calificar conductor
                </a>
            </div>

            {{-- Cancelar --}}
            <form method="POST" action="{{ route('pasajero.cancelarViaje') }}"
                  id="form-cancelar"
                  onsubmit="return confirm('¿Cancelar el viaje?')">
                @csrf
                <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                <button type="submit" class="btn btn-outline btn-ancho">✕ Cancelar viaje</button>
            </form>

        </div>
    </div>
</div>

<script>
const VIAJE_ID = @json($viaje['id'] ?? 0);
const ESTADO_INICIAL = @json($viaje['estado'] ?? 'aceptado');
const ESTADO_URL = @json(($viaje['id'] ?? 0) ? route('api.internal.viajes.show', $viaje['id']) : null);
const ORDEN_PASOS = { aceptado: 0, recogiendo: 1, en_curso: 2, completado: 3 };

const origenReal = AltokkeMapa.puntoValido(
    @json($viaje['origen_lat'] ?? null),
    @json($viaje['origen_lng'] ?? null)
);
const destinoReal = AltokkeMapa.puntoValido(
    @json($viaje['destino_lat'] ?? null),
    @json($viaje['destino_lng'] ?? null)
);
const conductorReal = AltokkeMapa.puntoValido(
    @json($conductor['lat'] ?? null),
    @json($conductor['lng'] ?? null)
);
const origenInicial = origenReal || AltokkeMapa.puntoSeguro(
    @json($viaje['origen_lat'] ?? null),
    @json($viaje['origen_lng'] ?? null),
    AltokkeMapa.BAGUA
);
const destinoInicial = destinoReal || AltokkeMapa.puntoSeguro(
    @json($viaje['destino_lat'] ?? null),
    @json($viaje['destino_lng'] ?? null),
    AltokkeMapa.CAJARURO
);
const conductorInicial = conductorReal || {
    lat: origenInicial.lat - 0.006,
    lng: origenInicial.lng - 0.004,
};

document.addEventListener('DOMContentLoaded', () => {
    if (!VIAJE_ID) return;
    const mapa = AltokkeMapa.crearMapa('mapa-en-curso', origenInicial, 15);
    if (!mapa) return;

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    if (origenReal) AltokkeMapa.crearMarcador(mapa, origenInicial, 'origen', 'O', 'Tu origen');
    if (destinoReal) AltokkeMapa.crearMarcador(mapa, destinoInicial, 'destino', 'D', 'Tu destino');

    const marcadorConductor = AltokkeMapa.crearMarcador(mapa, conductorInicial, 'conductor', 'M', 'Tu conductor');

    let conductorActual = conductorInicial;
    let rutaConductor = null;
    let rutaViaje = null;
    let estadoActual = ESTADO_INICIAL;
    let ubicacionRealActiva = Boolean(conductorReal);
    let pollingEstado = null;
    let consultandoEstado = false;

    const eta = document.getElementById('eta-pasajero');
    const distancia = document.getElementById('distancia-pasajero');
    const estadoRuta = document.getElementById('estado-ruta-pasajero');
    const detalleRuta = document.getElementById('detalle-ruta-pasajero');
    const panelDistancia = document.getElementById('panel-distancia-pasajero');
    const panelTiempo = document.getElementById('panel-tiempo-pasajero');
    const tarifaDetalle = document.getElementById('tarifa-detalle-curso');

    async function pintarRutas() {
        if (!origenReal || !destinoReal || !marcadorConductor) {
            if (estadoRuta) estadoRuta.textContent = 'Coordenadas pendientes';
            if (detalleRuta) detalleRuta.textContent = 'No se encontraron puntos validos para este viaje';
            AltokkeMapa.ajustarVista(mapa, [conductorActual, origenInicial, destinoInicial]);
            return;
        }

        if (estadoRuta) estadoRuta.textContent = 'Calculando ruta';
        const destinoConductor = estadoActual === 'en_curso' ? destinoInicial : origenInicial;
        const [rutaLlegada, rutaDestino] = await Promise.all([
            AltokkeMapa.consultarRuta(conductorActual, destinoConductor),
            AltokkeMapa.consultarRuta(origenInicial, destinoInicial),
        ]);

        rutaConductor = AltokkeMapa.dibujarRuta(mapa, rutaConductor, rutaLlegada, {
            color: '#111827',
            weight: 5,
            opacity: 0.85,
        });
        rutaViaje = AltokkeMapa.dibujarRuta(mapa, rutaViaje, rutaDestino, {
            color: '#2d6a2d',
            weight: 6,
            opacity: 0.9,
        });

        const rutaVisible = estadoActual === 'en_curso' ? rutaDestino : rutaLlegada;
        if (eta) eta.textContent = `${rutaVisible.duracion_min || '--'} min`;
        if (distancia) distancia.textContent = `${Number(rutaVisible.distancia_km || 0).toFixed(1)} km`;
        if (panelDistancia) panelDistancia.textContent = `${Number(rutaDestino.distancia_km || 0).toFixed(1)} km`;
        if (panelTiempo) panelTiempo.textContent = `${rutaDestino.duracion_min || '--'} min`;
        if (tarifaDetalle) tarifaDetalle.textContent = AltokkeMapa.textoRuta(rutaDestino);
        if (estadoRuta) estadoRuta.textContent = rutaVisible.ok ? 'Ruta estimada' : 'Sin ruta disponible';
        if (detalleRuta) detalleRuta.textContent = rutaVisible.ok
            ? 'Ruta real calculada'
            : 'Usando linea simple entre puntos';

        AltokkeMapa.ajustarVista(mapa, [conductorActual, origenInicial, destinoInicial]);
        iniciarSimulacionSiHaceFalta(rutaLlegada);
    }

    function actualizarPasos(nuevoEstado) {
        estadoActual = nuevoEstado;
        const posNueva = ORDEN_PASOS[nuevoEstado] ?? 0;

        document.querySelectorAll('.paso').forEach((el, i) => {
            if (i < posNueva) {
                el.className = 'paso hecho';
            } else if (i === posNueva) {
                el.className = 'paso activo';
            } else {
                el.className = 'paso';
            }
        });

        const textos = {
            aceptado: 'Conductor asignado y en camino...',
            recogiendo: 'El conductor esta llegando a tu punto de origen...',
            en_curso: 'Viaje en curso hacia tu destino...',
            completado: 'Has llegado a tu destino'
        };
        const txtLabel = document.getElementById('estado-texto');
        if (txtLabel && textos[nuevoEstado]) txtLabel.innerText = textos[nuevoEstado];
        if (['completado', 'cancelado', 'expirado'].includes(nuevoEstado)) {
            AltokkeMapa.detenerSimulacion(`pasajero-${VIAJE_ID}`);
            if (pollingEstado) window.clearInterval(pollingEstado);
        }
        pintarRutas();
    }

    function moverConductorEnMapa(punto, esReal = false) {
        const nuevoPunto = AltokkeMapa.puntoValido(punto?.lat, punto?.lng);
        if (!nuevoPunto || !marcadorConductor) return;

        if (esReal) {
            ubicacionRealActiva = true;
            AltokkeMapa.detenerSimulacion(`pasajero-${VIAJE_ID}`);
            if (estadoRuta) estadoRuta.textContent = 'GPS activo';
        }

        conductorActual = nuevoPunto;
        AltokkeMapa.moverMarcadorSuave(marcadorConductor, nuevoPunto, 900);
    }

    function iniciarSimulacionSiHaceFalta(rutaLlegada) {
        if (ubicacionRealActiva || !rutaLlegada?.coordenadas?.length || !marcadorConductor) return;
        if (['completado', 'cancelado', 'expirado'].includes(estadoActual)) return;

        if (estadoRuta) estadoRuta.textContent = estadoActual === 'en_curso' ? 'Modo simulacion' : 'Simulando llegada';
        if (detalleRuta) detalleRuta.textContent = estadoActual === 'en_curso'
            ? 'Conductor avanzando hacia el destino'
            : 'Conductor acercandose al origen';

        AltokkeMapa.iniciarSimulacion(`pasajero-${VIAJE_ID}`, {
            marcador: marcadorConductor,
            coordenadas: rutaLlegada.coordenadas,
            intervaloMs: 1800,
            minimoPasos: 60,
            debeDetener: () => ubicacionRealActiva || ['completado', 'cancelado', 'expirado'].includes(estadoActual),
            alMover: (punto) => {
                conductorActual = punto;
            },
        });
    }

    async function consultarEstado() {
        if (!ESTADO_URL || consultandoEstado) return;
        consultandoEstado = true;

        try {
            const respuesta = await fetch(ESTADO_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!respuesta.ok) throw new Error('No se pudo consultar el viaje');
            const data = await respuesta.json();
            const viaje = data.data || null;
            if (!data.ok || !viaje) throw new Error(data.message || 'Respuesta no valida');

            if (viaje.estado && viaje.estado !== estadoActual) {
                actualizarPasos(viaje.estado);
            }

            const puntoConductor = AltokkeMapa.puntoValido(viaje.conductor?.lat, viaje.conductor?.lng);
            if (puntoConductor) {
                moverConductorEnMapa(puntoConductor, true);
                pintarRutas();
            }
        } catch (error) {
            if (detalleRuta) detalleRuta.textContent = 'Seguimos mostrando la ultima ubicacion disponible';
        } finally {
            consultandoEstado = false;
        }
    }

    pintarRutas();
    consultarEstado();
    pollingEstado = window.setInterval(consultarEstado, 8000);

    if (window.Echo) {
        window.Echo.private(`viaje.${VIAJE_ID}`)
            .listen('.UbicacionConductorActualizada', (data) => {
                const nuevaPos = AltokkeMapa.puntoValido(data.lat, data.lng);
                if (!nuevaPos) return;
                moverConductorEnMapa(nuevaPos, true);
                pintarRutas();
            });

        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('.ViajeActualizado', (data) => {
                if (!data.estado) return;
                actualizarPasos(data.estado);

                if (data.estado === 'completado') {
                    const calificar = document.getElementById('seccion-calificar');
                    const cancelar = document.getElementById('form-cancelar');
                    if (calificar) calificar.style.display = 'block';
                    if (cancelar) cancelar.style.display = 'none';
                }
            });
    }

    window.addEventListener('beforeunload', () => {
        AltokkeMapa.detenerSimulacion(`pasajero-${VIAJE_ID}`);
        if (pollingEstado) window.clearInterval(pollingEstado);
    });
});
</script>
@endsection
