import '../mapa/leaflet_helpers';

const ordenPasos = { aceptado: 0, recogiendo: 1, en_curso: 2, completado: 3 };

document.addEventListener('DOMContentLoaded', function () {
    const datos = document.getElementById('datos-viaje-en-curso');
    if (!datos) return;

    const viajeId = datos.dataset.viajeId;
    const estadoUrl = datos.dataset.estadoUrl;
    const pasajeroId = datos.dataset.pasajeroId;
    const origenReal = AltokkeMapa.puntoValido(datos.dataset.origenLat, datos.dataset.origenLng);
    const destinoReal = AltokkeMapa.puntoValido(datos.dataset.destinoLat, datos.dataset.destinoLng);
    const conductorReal = AltokkeMapa.puntoValido(datos.dataset.conductorLat, datos.dataset.conductorLng);
    const origenInicial = origenReal || AltokkeMapa.puntoSeguro(
        datos.dataset.origenLat,
        datos.dataset.origenLng,
        AltokkeMapa.BAGUA
    );
    const destinoInicial = destinoReal || AltokkeMapa.puntoSeguro(
        datos.dataset.destinoLat,
        datos.dataset.destinoLng,
        AltokkeMapa.CAJARURO
    );
    const conductorInicial = conductorReal || {
        lat: origenInicial.lat - 0.006,
        lng: origenInicial.lng - 0.004,
    };

    if (!viajeId) return;

    document.getElementById('form-cancelar')?.addEventListener('submit', function (evento) {
        if (!window.confirm('¿Cancelar el viaje?')) {
            evento.preventDefault();
        }
    });

    const mapa = AltokkeMapa.crearMapa('mapa-en-curso', origenInicial, 15);
    if (!mapa) return;

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    if (origenReal) AltokkeMapa.crearMarcador(mapa, origenInicial, 'origen', 'O', 'Tu origen');
    if (destinoReal) AltokkeMapa.crearMarcador(mapa, destinoInicial, 'destino', 'D', 'Tu destino');

    const marcadorConductor = AltokkeMapa.crearMarcador(
        mapa,
        conductorInicial,
        'conductor',
        'M',
        'Tu conductor'
    );

    let conductorActual = conductorInicial;
    let rutaConductor = null;
    let rutaViaje = null;
    let estadoActual = datos.dataset.estadoInicial || 'aceptado';
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
        if (detalleRuta) {
            detalleRuta.textContent = rutaVisible.ok
                ? 'Ruta real calculada'
                : 'Usando linea simple entre puntos';
        }

        AltokkeMapa.ajustarVista(mapa, [conductorActual, origenInicial, destinoInicial]);
        iniciarSimulacionSiHaceFalta(rutaLlegada);
    }

    function actualizarPasos(nuevoEstado) {
        estadoActual = nuevoEstado;
        const posicionNueva = ordenPasos[nuevoEstado] ?? 0;

        document.querySelectorAll('.paso').forEach((elemento, indice) => {
            if (indice < posicionNueva) {
                elemento.className = 'paso hecho';
            } else if (indice === posicionNueva) {
                elemento.className = 'paso activo';
            } else {
                elemento.className = 'paso';
            }
        });

        const textos = {
            aceptado: 'Conductor asignado y en camino...',
            recogiendo: 'El conductor esta llegando a tu punto de origen...',
            en_curso: 'Viaje en curso hacia tu destino...',
            completado: 'Has llegado a tu destino'
        };
        const textoEstado = document.getElementById('estado-texto');
        if (textoEstado && textos[nuevoEstado]) textoEstado.textContent = textos[nuevoEstado];

        if (['completado', 'cancelado', 'expirado'].includes(nuevoEstado)) {
            AltokkeMapa.detenerSimulacion(`pasajero-${viajeId}`);
            if (pollingEstado) window.clearInterval(pollingEstado);
        }

        pintarRutas();
    }

    function moverConductorEnMapa(punto, esReal = false) {
        const nuevoPunto = AltokkeMapa.puntoValido(punto?.lat, punto?.lng);
        if (!nuevoPunto || !marcadorConductor) return;

        if (esReal) {
            ubicacionRealActiva = true;
            AltokkeMapa.detenerSimulacion(`pasajero-${viajeId}`);
            if (estadoRuta) estadoRuta.textContent = 'GPS activo';
        }

        conductorActual = nuevoPunto;
        AltokkeMapa.moverMarcadorSuave(marcadorConductor, nuevoPunto, 900);
    }

    function iniciarSimulacionSiHaceFalta(rutaLlegada) {
        if (ubicacionRealActiva || !rutaLlegada?.coordenadas?.length || !marcadorConductor) return;
        if (['completado', 'cancelado', 'expirado'].includes(estadoActual)) return;

        if (estadoRuta) {
            estadoRuta.textContent = estadoActual === 'en_curso' ? 'Modo simulacion' : 'Simulando llegada';
        }
        if (detalleRuta) {
            detalleRuta.textContent = estadoActual === 'en_curso'
                ? 'Conductor avanzando hacia el destino'
                : 'Conductor acercandose al origen';
        }

        AltokkeMapa.iniciarSimulacion(`pasajero-${viajeId}`, {
            marcador: marcadorConductor,
            coordenadas: rutaLlegada.coordenadas,
            intervaloMs: 1800,
            minimoPasos: 60,
            debeDetener: () => ubicacionRealActiva
                || ['completado', 'cancelado', 'expirado'].includes(estadoActual),
            alMover: (punto) => {
                conductorActual = punto;
            },
        });
    }

    async function consultarEstado() {
        if (!estadoUrl || consultandoEstado) return;
        consultandoEstado = true;

        try {
            const respuesta = await fetch(estadoUrl, {
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

            const puntoConductor = AltokkeMapa.puntoValido(
                viaje.conductor?.lat,
                viaje.conductor?.lng
            );
            if (puntoConductor) {
                moverConductorEnMapa(puntoConductor, true);
                pintarRutas();
            }
        } catch (error) {
            if (detalleRuta) {
                detalleRuta.textContent = 'Seguimos mostrando la ultima ubicacion disponible';
            }
        } finally {
            consultandoEstado = false;
        }
    }

    pintarRutas();
    consultarEstado();
    pollingEstado = window.setInterval(consultarEstado, 8000);

    if (window.Echo) {
        window.Echo.private(`viaje.${viajeId}`)
            .listen('.UbicacionConductorActualizada', (data) => {
                const nuevaPosicion = AltokkeMapa.puntoValido(data.lat, data.lng);
                if (!nuevaPosicion) return;
                moverConductorEnMapa(nuevaPosicion, true);
                pintarRutas();
            });

        if (pasajeroId) {
            window.Echo.private(`pasajero.${pasajeroId}`)
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
    }

    window.addEventListener('beforeunload', () => {
        AltokkeMapa.detenerSimulacion(`pasajero-${viajeId}`);
        if (pollingEstado) window.clearInterval(pollingEstado);
    });
});
