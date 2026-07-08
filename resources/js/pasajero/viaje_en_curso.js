import '../mapa/leaflet_helpers';

const ordenPasos = { aceptado: 0, recogiendo: 1, en_curso: 2, completado: 3 };

document.addEventListener('DOMContentLoaded', function () {
    const datos = document.getElementById('datos-viaje-en-curso');
    if (!datos) return;

    const INTERVALO_POLLING_ESTADO_MS = 5000;
    const DISTANCIA_MIN_MOVER_CONDUCTOR_KM = 0.003;
    const viajeId = datos.dataset.viajeId;
    const estadoUrl = datos.dataset.estadoUrl;
    const calificarUrl = datos.dataset.calificarUrl;
    const historialUrl = datos.dataset.historialUrl;
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
    const conductorInicial = conductorReal
        || AltokkeMapa.puntoCercano(origenInicial, viajeId, 0.3);

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
    let pollingEstado = null;
    let consultandoEstado = false;
    let estadoAbortController = null;
    let ultimaRutaActivaMs = 0;
    let repintandoRuta = false;
    let ultimaUbicacionRecibida = conductorInicial;

    const eta = document.getElementById('eta-pasajero');
    const distancia = document.getElementById('distancia-pasajero');
    const estadoRuta = document.getElementById('estado-ruta-pasajero');
    const detalleRuta = document.getElementById('detalle-ruta-pasajero');
    const panelDistancia = document.getElementById('panel-distancia-pasajero');
    const panelTiempo = document.getElementById('panel-tiempo-pasajero');
    const tarifaDetalle = document.getElementById('tarifa-detalle-curso');
    const textoEstado = document.getElementById('estado-texto');

    function pasajeroPuedeConfirmarInicio() {
        return estadoActual === 'recogiendo'
            && AltokkeMapa.distanciaSimple(conductorActual, origenInicial) <= 0.05;
    }

    function pasajeroLlegoADestino() {
        return estadoActual === 'en_curso'
            && AltokkeMapa.distanciaSimple(conductorActual, destinoInicial) <= 0.05;
    }

    function esEstadoFinal(estado) {
        return ['completado', 'cancelado', 'expirado', 'finalizado', 'llegado_destino', 'pago_confirmado'].includes(estado);
    }

    function actualizarVistaLlegada() {
        if (eta) eta.textContent = '0 min';
        if (distancia) distancia.textContent = '0.0 km';
        if (panelDistancia) panelDistancia.textContent = '0.0 km';
        if (panelTiempo) panelTiempo.textContent = '0 min';
        if (estadoRuta) estadoRuta.textContent = 'Llegaste';
        if (detalleRuta) detalleRuta.textContent = 'Esperando confirmacion de pago del conductor.';
        if (textoEstado) {
            textoEstado.textContent = 'Llegaste a tu destino. Esperando confirmacion de pago...';
        }
    }

    function detenerConsultaEstado(mensaje) {
        if (pollingEstado) {
            window.clearInterval(pollingEstado);
            pollingEstado = null;
        }

        AltokkeMapa.detenerSimulacion(`pasajero-${viajeId}`);
        estadoAbortController?.abort();
        if (estadoRuta) estadoRuta.textContent = 'Viaje no disponible';
        if (detalleRuta) detalleRuta.textContent = mensaje;
    }

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

        if (pasajeroLlegoADestino()) {
            rutaConductor = AltokkeMapa.dibujarRuta(mapa, rutaConductor, null, {
                color: '#111827',
                weight: 4,
                opacity: 0.55,
                dashArray: '10 6',
            });
            rutaViaje = AltokkeMapa.dibujarRuta(mapa, rutaViaje, rutaDestino, {
                color: '#2d6a2d',
                weight: 6,
                opacity: 0.9,
            });
            actualizarVistaLlegada();
            AltokkeMapa.ajustarVista(mapa, [conductorActual, origenInicial, destinoInicial]);
            return;
        }

        rutaConductor = AltokkeMapa.dibujarRuta(mapa, rutaConductor, estadoActual === 'en_curso' ? null : rutaDestino, {
            color: '#111827',
            weight: 4,
            opacity: 0.55,
            dashArray: '10 6',
        });
        const rutaActiva = rutaLlegada;
        rutaViaje = AltokkeMapa.dibujarRuta(mapa, rutaViaje, rutaActiva, {
            color: '#2d6a2d',
            weight: 6,
            opacity: 0.9,
        });

        const rutaVisible = rutaActiva;
        if (eta) eta.textContent = `${rutaVisible.duracion_min || '--'} min`;
        if (distancia) distancia.textContent = `${Number(rutaVisible.distancia_km || 0).toFixed(1)} km`;
        if (panelDistancia) panelDistancia.textContent = `${Number(rutaVisible.distancia_km || 0).toFixed(1)} km`;
        if (panelTiempo) panelTiempo.textContent = `${rutaVisible.duracion_min || '--'} min`;
        if (tarifaDetalle) tarifaDetalle.textContent = AltokkeMapa.textoRuta(rutaVisible);
        if (estadoRuta) estadoRuta.textContent = rutaVisible.ok ? 'Ruta estimada' : 'Sin ruta disponible';
        if (detalleRuta) {
            detalleRuta.textContent = rutaVisible.ok
                ? 'Ruta real calculada'
                : 'Usando linea simple entre puntos';
        }

        if (pasajeroPuedeConfirmarInicio()) {
            if (estadoRuta) estadoRuta.textContent = 'Antes de iniciar el viaje';
            if (detalleRuta) {
                // 8.1J_MENSAJE_SOLO_PASAJERO -> luego ir a cancelar pasajero
                detalleRuta.textContent = 'Estas seguro de continuar con el conductor asignado? Si no cancelas ahora, el viaje iniciara en pocos segundos.';
            }
            if (eta) eta.textContent = 'Listo';
            if (distancia) distancia.textContent = '0.0 km';
        } else if (estadoActual === 'recogiendo') {
            if (estadoRuta) estadoRuta.textContent = 'Conductor en camino';
            if (detalleRuta) detalleRuta.textContent = 'El conductor esta llegando a tu punto de origen.';
        }

        AltokkeMapa.ajustarVista(mapa, [conductorActual, origenInicial, destinoInicial]);
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
            // 8.1J_MENSAJE_SOLO_PASAJERO -> luego ir a cancelar pasajero
            recogiendo: pasajeroPuedeConfirmarInicio()
                ? 'Estas seguro de continuar con el conductor asignado? Si no cancelas ahora, el viaje iniciara en pocos segundos.'
                : 'El conductor esta llegando a tu punto de origen...',
            en_curso: pasajeroLlegoADestino()
                ? 'Llegaste a tu destino. Esperando confirmacion de pago...'
                : 'Viaje en curso hacia tu destino...',
            completado: 'Has llegado a tu destino'
        };
        if (textoEstado && textos[nuevoEstado]) textoEstado.textContent = textos[nuevoEstado];
        if (nuevoEstado === 'en_curso') {
            const cancelar = document.getElementById('form-cancelar');
            if (cancelar) cancelar.style.display = 'none';
        }

        if (esEstadoFinal(nuevoEstado)) {
            AltokkeMapa.detenerSimulacion(`pasajero-${viajeId}`);
            if (pollingEstado) window.clearInterval(pollingEstado);
            window.location.href = nuevoEstado === 'completado' ? calificarUrl : historialUrl;
            return;
        }

        pintarRutas();
    }

    function moverConductorEnMapa(punto) {
        const nuevoPunto = AltokkeMapa.puntoValido(punto?.lat, punto?.lng);
        if (!nuevoPunto || !marcadorConductor) return;
        if (
            ultimaUbicacionRecibida
            && AltokkeMapa.distanciaSimple(ultimaUbicacionRecibida, nuevoPunto) < DISTANCIA_MIN_MOVER_CONDUCTOR_KM
        ) {
            return;
        }

        conductorActual = nuevoPunto;
        ultimaUbicacionRecibida = nuevoPunto;
        AltokkeMapa.moverMarcadorSuave(marcadorConductor, nuevoPunto, 900);
        if (pasajeroLlegoADestino()) {
            actualizarVistaLlegada();
            return;
        }

        // 3J_MOVIMIENTO_SYNC_CONDUCTOR_PASAJERO -> luego ir a refresh persistente
        const ahora = Date.now();
        if (!repintandoRuta && ahora - ultimaRutaActivaMs >= INTERVALO_POLLING_ESTADO_MS) {
            ultimaRutaActivaMs = ahora;
            repintandoRuta = true;
            pintarRutas().finally(() => {
                repintandoRuta = false;
            });
        }
    }

    async function consultarEstado() {
        if (!estadoUrl || consultandoEstado) return;
        consultandoEstado = true;
        estadoAbortController = new AbortController();

        try {
            const respuesta = await fetch(estadoUrl, {
                signal: estadoAbortController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (respuesta.status === 429) {
                console.warn('[Altokke] demasiadas consultas de estado; se esperara al siguiente intervalo.');
                return;
            }
            if (!respuesta.ok) {
                detenerConsultaEstado('El viaje no existe o no tienes permiso para consultarlo.');
                return;
            }

            const data = await respuesta.json();
            const viaje = data.viaje || data.data || null;
            if (!data.ok || !viaje) {
                detenerConsultaEstado(data.mensaje || data.message || 'La respuesta del viaje no es valida.');
                return;
            }

            if (viaje.estado && viaje.estado !== estadoActual) {
                actualizarPasos(viaje.estado);
            }

            const conductor = data.conductor || viaje.conductor || null;
            const puntoConductor = AltokkeMapa.puntoValido(
                conductor?.lat,
                conductor?.lng
            );
            if (puntoConductor) {
                // 4J_REFRESH_CONTINUA_DESDE_ULTIMA_UBICACION -> luego ir a llegada destino
                moverConductorEnMapa(puntoConductor);
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            detenerConsultaEstado('No se pudo consultar el viaje. Actualiza la pagina para intentar nuevamente.');
        } finally {
            consultandoEstado = false;
        }
    }

    pintarRutas();
    consultarEstado();
    if (!pollingEstado) {
        pollingEstado = window.setInterval(consultarEstado, INTERVALO_POLLING_ESTADO_MS);
    }

    if (window.Echo) {
        window.Echo.private(`viaje.${viajeId}`)
            .listen('.UbicacionConductorActualizada', (data) => {
                const nuevaPosicion = AltokkeMapa.puntoValido(data.lat, data.lng);
                if (!nuevaPosicion) return;
                moverConductorEnMapa(nuevaPosicion);
                if (pasajeroLlegoADestino()) {
                    actualizarVistaLlegada();
                }
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
        estadoAbortController?.abort();
    });
});
