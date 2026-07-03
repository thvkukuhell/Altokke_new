import '../mapa/leaflet_helpers';

document.addEventListener('DOMContentLoaded', function () {
    const datos = document.getElementById('datos-viaje-activo-conductor');
    if (!datos) return;

    const viajeId = datos.dataset.viajeId;
    const recogerUrl = datos.dataset.recogerUrl;
    const iniciarUrl = datos.dataset.iniciarUrl;
    const completarUrl = datos.dataset.completarUrl;
    const estadoUrl = datos.dataset.estadoUrl;
    const solicitudesUrl = datos.dataset.solicitudesUrl;
    let estadoViaje = datos.dataset.estadoViaje || 'aceptado';
    const conductorReal = AltokkeMapa.puntoValido(
        datos.dataset.conductorLat,
        datos.dataset.conductorLng
    );
    const origenReal = AltokkeMapa.puntoValido(
        datos.dataset.origenLat,
        datos.dataset.origenLng
    );
    const destinoReal = AltokkeMapa.puntoValido(
        datos.dataset.destinoLat,
        datos.dataset.destinoLng
    );

    if (!viajeId) return;

    const origen = origenReal || AltokkeMapa.puntoSeguro(
        datos.dataset.origenLat,
        datos.dataset.origenLng,
        AltokkeMapa.BAGUA
    );
    const destino = destinoReal || AltokkeMapa.puntoSeguro(
        datos.dataset.destinoLat,
        datos.dataset.destinoLng,
        AltokkeMapa.CAJARURO
    );
    // 4J_REFRESH_CONTINUA_DESDE_ULTIMA_UBICACION -> luego ir a llegada destino
    const conductorPersistido = conductorReal;
    let conductor = conductorPersistido || AltokkeMapa.puntoCercano(origen, viajeId, 0.3);
    const simulacionForzada = !conductorPersistido;

    const mapaConductor = AltokkeMapa.crearMapa('mapa-leaflet-conductor', conductor, 15);
    if (!mapaConductor) return;

    const marcadorConductor = AltokkeMapa.crearMarcador(
        mapaConductor,
        conductor,
        'conductor',
        'M',
        'Conductor'
    );
    if (origenReal) AltokkeMapa.crearMarcador(mapaConductor, origen, 'origen', 'O', 'Origen del pasajero');
    if (destinoReal) AltokkeMapa.crearMarcador(mapaConductor, destino, 'destino', 'D', 'Destino');

    AltokkeMapa.ajustarVista(mapaConductor, [conductor, origen, destino]);

    let rutaConductor = null;
    let rutaViaje = null;
    let ultimaRutaMs = 0;
    let gpsRealActivo = false;
    let watchId = null;
    let ubicacionBloqueada = false;
    let procesandoTransicion = false;
    let viajeTerminado = false;
    let pollingEstado = null;
    let simulacionDestinoActiva = false;
    let ultimaRutaActivaMs = 0;
    let llegoDestino = estadoViaje === 'en_curso'
        && AltokkeMapa.distanciaSimple(conductor, destino) <= 0.05;

    const eta = document.getElementById('eta-conductor');
    const distancia = document.getElementById('distancia-conductor');
    const estado = document.getElementById('estado-ruta-conductor');
    const detalle = document.getElementById('detalle-ruta-conductor');
    const panelDistancia = document.getElementById('panel-distancia-conductor');
    const panelTiempo = document.getElementById('panel-tiempo-conductor');
    const botonesCompletar = document.querySelectorAll(`form[action="${completarUrl}"] button`);
    botonesCompletar.forEach((boton) => {
        boton.disabled = estadoViaje !== 'en_curso' && !llegoDestino;
    });

    function conductorLlegoADestino() {
        return estadoViaje === 'en_curso'
            && AltokkeMapa.distanciaSimple(conductor, destino) <= 0.05;
    }

    function actualizarVistaLlegada() {
        llegoDestino = true;
        conductor = destino;
        if (marcadorConductor) {
            marcadorConductor.setLatLng([destino.lat, destino.lng]);
        }
        botonesCompletar.forEach((boton) => {
            boton.disabled = false;
        });
        if (eta) eta.textContent = '0 min';
        if (distancia) distancia.textContent = '0.0 km';
        if (panelDistancia) panelDistancia.textContent = '0.0 km';
        if (panelTiempo) panelTiempo.textContent = '0 min';
        if (estado) estado.textContent = 'Llegaste';
        if (detalle) detalle.textContent = 'Esperando confirmacion de pago para completar el viaje';
    }

    async function actualizarRutas(forzar = false) {
        if (!origenReal || !destinoReal || !marcadorConductor) {
            if (estado) estado.textContent = 'Coordenadas pendientes';
            if (detalle) detalle.textContent = 'No se encontraron puntos validos para este viaje';
            AltokkeMapa.ajustarVista(mapaConductor, [conductor, origen, destino]);
            return;
        }

        const ahora = Date.now();
        if (!forzar && ahora - ultimaRutaMs < 3000) return;
        ultimaRutaMs = ahora;

        if (estado) estado.textContent = 'Calculando ruta';
        const destinoActivo = estadoViaje === 'en_curso' ? destino : origen;
        const [rutaAlPasajero, rutaDestino] = await Promise.all([
            AltokkeMapa.consultarRuta(conductor, destinoActivo),
            AltokkeMapa.consultarRuta(origen, destino),
        ]);

        if (conductorLlegoADestino()) {
            rutaConductor = AltokkeMapa.dibujarRuta(mapaConductor, rutaConductor, null, {
                color: '#111827',
                weight: 4,
                opacity: 0.55,
                dashArray: '10 6',
            });
            rutaViaje = AltokkeMapa.dibujarRuta(mapaConductor, rutaViaje, rutaDestino, {
                color: '#2d6a2d',
                weight: 6,
                opacity: 0.9,
            });
            actualizarVistaLlegada();
            AltokkeMapa.ajustarVista(mapaConductor, [conductor, origen, destino]);
            return;
        }

        rutaConductor = AltokkeMapa.dibujarRuta(mapaConductor, rutaConductor, estadoViaje === 'en_curso' ? null : rutaDestino, {
            color: '#111827',
            weight: 4,
            opacity: 0.55,
            dashArray: '10 6',
        });
        const rutaActiva = rutaAlPasajero;
        rutaViaje = AltokkeMapa.dibujarRuta(mapaConductor, rutaViaje, rutaActiva, {
            color: '#2d6a2d',
            weight: 6,
            opacity: 0.9,
        });

        const rutaVisible = rutaActiva;
        if (eta) eta.textContent = `${rutaVisible.duracion_min || '--'} min`;
        if (distancia) distancia.textContent = `${Number(rutaVisible.distancia_km || 0).toFixed(1)} km`;
        if (panelDistancia) panelDistancia.textContent = `${Number(rutaVisible.distancia_km || 0).toFixed(1)} km`;
        if (panelTiempo) panelTiempo.textContent = `${rutaVisible.duracion_min || '--'} min`;
        if (estado) estado.textContent = rutaVisible.ok ? 'Ruta estimada' : 'Sin ruta disponible';
        if (detalle) {
            detalle.textContent = rutaVisible.ok
                ? 'Ruta real calculada'
                : 'Usando linea simple entre puntos';
        }

        AltokkeMapa.ajustarVista(mapaConductor, [conductor, origen, destino]);
        if (estadoViaje === 'en_curso') {
            iniciarSimulacionDestinoSiCorresponde(rutaAlPasajero);
        } else {
            iniciarSimulacionSiHaceFalta(rutaAlPasajero);
        }
    }

    async function emitirUbicacion(latitud, longitud) {
        const punto = AltokkeMapa.puntoValido(latitud, longitud);
        if (!punto || !viajeId || ubicacionBloqueada) return false;

        try {
            const respuesta = await fetch(datos.dataset.ubicacionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': datos.dataset.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    viaje_id: viajeId,
                    lat: punto.lat,
                    lng: punto.lng
                })
            });

            if (!respuesta.ok) {
                if ([401, 403, 404, 409].includes(respuesta.status)) {
                    ubicacionBloqueada = true;
                    AltokkeMapa.detenerSimulacion(`conductor-${viajeId}`);

                    if (watchId !== null && navigator.geolocation) {
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                    }
                }

                throw new Error('No se pudo enviar la ubicacion');
            }
            return true;
        } catch (error) {
            if (detalle) detalle.textContent = 'No se pudo enviar la ubicacion';
            return false;
        }
    }

    async function actualizarEstado(url, nuevoEstado) {
        const respuesta = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': datos.dataset.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id_viaje: viajeId })
        });

        if (!respuesta.ok) {
            throw new Error(`No se pudo cambiar el viaje a ${nuevoEstado}`);
        }

        const data = await respuesta.json();
        estadoViaje = data.estado || nuevoEstado;
        return data;
    }

    async function finalizarTramo() {
        if (procesandoTransicion || ubicacionBloqueada) return;
        procesandoTransicion = true;

        try {
            if (estadoViaje === 'aceptado') {
                await actualizarEstado(recogerUrl, 'recogiendo');
            }

            if (estadoViaje === 'recogiendo') {
                if (estado) estado.textContent = 'Pasajero abordando';
                if (detalle) {
                    detalle.textContent = 'Pasajero abordando. Esperando inicio del trayecto.';
                }

                const origenGuardado = await emitirUbicacion(origen.lat, origen.lng);
                if (!origenGuardado) {
                    throw new Error('No se pudo guardar la llegada al origen');
                }
                for (let segundos = 3; segundos > 0; segundos -= 1) {
                    if (detalle) {
                        detalle.textContent = `Pasajero abordando. Salida en ${segundos} s.`;
                    }
                    await new Promise((resolve) => window.setTimeout(resolve, 1000));
                    await consultarEstadoViaje();
                    if (viajeTerminado || estadoViaje !== 'recogiendo') {
                        return;
                    }
                }

                await actualizarEstado(iniciarUrl, 'en_curso');

                botonesCompletar.forEach((boton) => {
                    boton.disabled = false;
                });
                await actualizarRutas(true);
                return;
            }

            if (estadoViaje === 'en_curso') {
                // 5J_LLEGADA_DESTINO_SIN_COMPLETAR -> luego ir a confirmar pago
                conductor = destino;
                marcadorConductor.setLatLng([destino.lat, destino.lng]);
                const ubicacionGuardada = await emitirUbicacion(destino.lat, destino.lng);
                if (!ubicacionGuardada) {
                    throw new Error('No se pudo guardar la ubicacion final');
                }
                llegoDestino = true;
                botonesCompletar.forEach((boton) => {
                    boton.disabled = false;
                });
                if (estado) estado.textContent = 'Llegaste al destino';
                if (detalle) detalle.textContent = 'Confirma el pago y completa el viaje';
            }
        } catch (error) {
            if (detalle) detalle.textContent = error.message;
        } finally {
            procesandoTransicion = false;
        }
    }

    function moverConductor(latitud, longitud, esReal = false) {
        const punto = AltokkeMapa.puntoValido(latitud, longitud);
        if (!punto || !marcadorConductor) return;

        if (esReal) {
            if (estado) estado.textContent = 'GPS activo';
        }

        conductor = punto;
        AltokkeMapa.moverMarcadorSuave(marcadorConductor, conductor, 900);
        emitirUbicacion(conductor.lat, conductor.lng);
        actualizarRutas();

        const objetivo = estadoViaje === 'en_curso' ? destino : origen;
        if (AltokkeMapa.distanciaSimple(conductor, objetivo) <= 0.05) {
            finalizarTramo();
        }
    }

    function iniciarSimulacionSiHaceFalta(rutaAlPasajero) {
        if (gpsRealActivo || llegoDestino || !marcadorConductor) return;

        const objetivo = estadoViaje === 'en_curso' ? destino : origen;
        if (AltokkeMapa.distanciaSimple(conductor, objetivo) <= 0.05) {
            finalizarTramo();
            return;
        }
        if (!rutaAlPasajero?.coordenadas?.length) return;

        if (estado) estado.textContent = 'Modo simulacion';
        if (detalle) {
            detalle.textContent = estadoViaje === 'en_curso'
                ? 'Avanzando hacia el destino'
                : 'Acercandote al pasajero';
        }

        AltokkeMapa.iniciarSimulacion(`conductor-${viajeId}`, {
            marcador: marcadorConductor,
            coordenadas: rutaAlPasajero.coordenadas,
            intervaloMs: estadoViaje === 'en_curso' ? 360 : 90,
            minimoPasos: estadoViaje === 'en_curso' ? 64 : 28,
            avancePorTick: estadoViaje === 'en_curso' ? 2 : 4,
            debeDetener: () => gpsRealActivo,
            alMover: (punto) => {
                conductor = punto;
                emitirUbicacion(punto.lat, punto.lng);
            },
            alFinalizar: finalizarTramo,
        });
    }

    function iniciarSimulacionDestinoSiCorresponde(rutaAlPasajero) {
        if (!rutaAlPasajero?.coordenadas?.length || llegoDestino || !marcadorConductor) return;
        if (estadoViaje !== 'en_curso') return;
        if (conductorLlegoADestino()) {
            actualizarVistaLlegada();
            return;
        }
        if (simulacionDestinoActiva) return;

        simulacionDestinoActiva = true;
        // 2J_MOTO_POR_PUNTOS_DE_RUTA -> luego ir a guardar ubicacion backend
        AltokkeMapa.iniciarSimulacion(`conductor-${viajeId}`, {
            marcador: marcadorConductor,
            coordenadas: rutaAlPasajero.coordenadas,
            intervaloMs: 360,
            minimoPasos: 64,
            avancePorTick: 2,
            debeDetener: () => ubicacionBloqueada || estadoViaje !== 'en_curso',
            alMover: (punto) => {
                conductor = punto;
                // 3J_MOVIMIENTO_SYNC_CONDUCTOR_PASAJERO -> luego ir a refresh persistente
                emitirUbicacion(punto.lat, punto.lng);
            },
            alFinalizar: () => {
                simulacionDestinoActiva = false;
                llegoDestino = true;
                conductor = destino;
                marcadorConductor.setLatLng([destino.lat, destino.lng]);
                emitirUbicacion(destino.lat, destino.lng);
                actualizarVistaLlegada();
            },
        });
    }

    async function consultarEstadoViaje() {
        if (!estadoUrl) return;

        try {
            const respuesta = await fetch(estadoUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!respuesta.ok) return;

            const data = await respuesta.json();
            const viaje = data.data || data.viaje || null;
            if (!viaje) return;

            estadoViaje = viaje.estado || estadoViaje;
            const puntoConductor = AltokkeMapa.puntoValido(viaje.conductor_lat, viaje.conductor_lng);
            if (puntoConductor) {
                conductor = puntoConductor;
                marcadorConductor.setLatLng([puntoConductor.lat, puntoConductor.lng]);
            }
            if (estadoViaje !== 'en_curso') {
                simulacionDestinoActiva = false;
            }
            if (estadoViaje === 'en_curso') {
                botonesCompletar.forEach((boton) => {
                    boton.disabled = false;
                });
                if (conductorLlegoADestino()) {
                    actualizarVistaLlegada();
                    AltokkeMapa.detenerSimulacion(`conductor-${viajeId}`);
                }
            }
            if (!['cancelado', 'completado', 'expirado'].includes(viaje.estado)) return;

            viajeTerminado = true;
            ubicacionBloqueada = true;
            AltokkeMapa.detenerSimulacion(`conductor-${viajeId}`);
            if (pollingEstado) window.clearInterval(pollingEstado);
            window.location.href = solicitudesUrl;
        } catch (error) {
        }
    }

    emitirUbicacion(conductor.lat, conductor.lng)
        .finally(() => actualizarRutas(true));
    pollingEstado = window.setInterval(consultarEstadoViaje, 1500);

    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition((posicion) => {
            const puntoGps = AltokkeMapa.puntoValido(
                posicion.coords.latitude,
                posicion.coords.longitude
            );
            if (
                !puntoGps
                || simulacionForzada
                || AltokkeMapa.distanciaSimple(puntoGps, conductor) > 0.05
            ) return;
            moverConductor(puntoGps.lat, puntoGps.lng, true);
        }, () => {
            gpsRealActivo = false;
            actualizarRutas(true);
        }, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 12000
        });
    } else {
        gpsRealActivo = false;
        actualizarRutas(true);
    }

    window.addEventListener('beforeunload', () => {
        AltokkeMapa.detenerSimulacion(`conductor-${viajeId}`);
        if (watchId !== null && navigator.geolocation) {
            navigator.geolocation.clearWatch(watchId);
        }
        if (pollingEstado) window.clearInterval(pollingEstado);
    });
});
