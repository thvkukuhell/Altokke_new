import '../mapa/leaflet_helpers';

document.addEventListener('DOMContentLoaded', function () {
    const datos = document.getElementById('datos-viaje-activo-conductor');
    if (!datos) return;

    const viajeId = datos.dataset.viajeId;
    const estadoViaje = datos.dataset.estadoViaje || 'aceptado';
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
    let conductor = conductorReal || {
        lat: origen.lat - 0.006,
        lng: origen.lng - 0.004,
    };

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
    let gpsRealActivo = Boolean(conductorReal);
    let watchId = null;
    let ubicacionBloqueada = false;

    const eta = document.getElementById('eta-conductor');
    const distancia = document.getElementById('distancia-conductor');
    const estado = document.getElementById('estado-ruta-conductor');
    const detalle = document.getElementById('detalle-ruta-conductor');
    const panelDistancia = document.getElementById('panel-distancia-conductor');
    const panelTiempo = document.getElementById('panel-tiempo-conductor');

    async function actualizarRutas(forzar = false) {
        if (!origenReal || !destinoReal || !marcadorConductor) {
            if (estado) estado.textContent = 'Coordenadas pendientes';
            if (detalle) detalle.textContent = 'No se encontraron puntos validos para este viaje';
            AltokkeMapa.ajustarVista(mapaConductor, [conductor, origen, destino]);
            return;
        }

        const ahora = Date.now();
        if (!forzar && ahora - ultimaRutaMs < 12000) return;
        ultimaRutaMs = ahora;

        if (estado) estado.textContent = 'Calculando ruta';
        const destinoActivo = estadoViaje === 'en_curso' ? destino : origen;
        const [rutaAlPasajero, rutaDestino] = await Promise.all([
            AltokkeMapa.consultarRuta(conductor, destinoActivo),
            AltokkeMapa.consultarRuta(origen, destino),
        ]);

        rutaConductor = AltokkeMapa.dibujarRuta(mapaConductor, rutaConductor, rutaAlPasajero, {
            color: '#111827',
            weight: 5,
            opacity: 0.85,
        });
        rutaViaje = AltokkeMapa.dibujarRuta(mapaConductor, rutaViaje, rutaDestino, {
            color: '#2d6a2d',
            weight: 6,
            opacity: 0.9,
        });

        const rutaVisible = estadoViaje === 'en_curso' ? rutaDestino : rutaAlPasajero;
        if (eta) eta.textContent = `${rutaVisible.duracion_min || '--'} min`;
        if (distancia) distancia.textContent = `${Number(rutaVisible.distancia_km || 0).toFixed(1)} km`;
        if (panelDistancia) panelDistancia.textContent = `${Number(rutaDestino.distancia_km || 0).toFixed(1)} km`;
        if (panelTiempo) panelTiempo.textContent = `${rutaDestino.duracion_min || '--'} min`;
        if (estado) estado.textContent = rutaVisible.ok ? 'Ruta estimada' : 'Sin ruta disponible';
        if (detalle) {
            detalle.textContent = rutaVisible.ok
                ? 'Ruta real calculada'
                : 'Usando linea simple entre puntos';
        }

        AltokkeMapa.ajustarVista(mapaConductor, [conductor, origen, destino]);
        iniciarSimulacionSiHaceFalta(rutaAlPasajero);
    }

    async function emitirUbicacion(latitud, longitud) {
        const punto = AltokkeMapa.puntoValido(latitud, longitud);
        if (!punto || !viajeId || ubicacionBloqueada) return;

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
        } catch (error) {
            if (detalle) detalle.textContent = 'No se pudo enviar la ubicacion';
        }
    }

    function moverConductor(latitud, longitud, esReal = false) {
        const punto = AltokkeMapa.puntoValido(latitud, longitud);
        if (!punto || !marcadorConductor) return;

        if (esReal) {
            gpsRealActivo = true;
            AltokkeMapa.detenerSimulacion(`conductor-${viajeId}`);
            if (estado) estado.textContent = 'GPS activo';
        }

        conductor = punto;
        AltokkeMapa.moverMarcadorSuave(marcadorConductor, conductor, 900);
        emitirUbicacion(conductor.lat, conductor.lng);
        actualizarRutas();
    }

    function iniciarSimulacionSiHaceFalta(rutaAlPasajero) {
        if (gpsRealActivo || !rutaAlPasajero?.coordenadas?.length || !marcadorConductor) return;

        if (estado) estado.textContent = 'Modo simulacion';
        if (detalle) {
            detalle.textContent = estadoViaje === 'en_curso'
                ? 'Avanzando hacia el destino'
                : 'Acercandote al pasajero';
        }

        AltokkeMapa.iniciarSimulacion(`conductor-${viajeId}`, {
            marcador: marcadorConductor,
            coordenadas: rutaAlPasajero.coordenadas,
            intervaloMs: 2200,
            minimoPasos: 64,
            debeDetener: () => gpsRealActivo,
            alMover: (punto) => {
                conductor = punto;
                emitirUbicacion(punto.lat, punto.lng);
            },
        });
    }

    actualizarRutas(true);

    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition((posicion) => {
            const puntoGps = AltokkeMapa.puntoValido(
                posicion.coords.latitude,
                posicion.coords.longitude
            );
            if (!puntoGps) return;
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
    });
});
