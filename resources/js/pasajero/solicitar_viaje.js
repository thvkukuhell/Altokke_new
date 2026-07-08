import '../mapa/leaflet_helpers';

document.addEventListener('DOMContentLoaded', function () {
    let mapa;
    let marcadorOrigen = null;
    let marcadorDestino = null;
    let lineaRuta = null;
    let campoActivo = 'destino';
    let ultimaRutaClave = '';
    let secuenciaRuta = 0;
    const direccionesCache = new Map();
    const busquedasCache = new Map();

    const ubicacionInicial = {
        lat: -5.63889,
        lng: -78.5311,
    };

    function actualizarEstadoSeleccion(mensaje) {
        const ubicacionTexto = document.getElementById('ubicacion-texto');
        if (ubicacionTexto) ubicacionTexto.textContent = mensaje;
    }

    async function obtenerDireccion(lat, lng) {
        const clave = `${Number(lat).toFixed(5)},${Number(lng).toFixed(5)}`;
        if (direccionesCache.has(clave)) {
            return direccionesCache.get(clave);
        }

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1`,
                { headers: { 'Accept-Language': 'es' } }
            );

            if (!response.ok) return 'Ubicacion actual';

            const data = await response.json();
            if (!data.address) return 'Ubicacion actual';

            const direccion = data.address;
            const calle = direccion.road
                || direccion.residential
                || direccion.pedestrian
                || data.name
                || '';
            const distrito = direccion.suburb
                || direccion.city_district
                || direccion.neighbourhood
                || direccion.village
                || direccion.town
                || direccion.city
                || '';

            const resultado = calle && distrito ? `${calle}, ${distrito}` : calle || distrito || 'Ubicacion actual';
            direccionesCache.set(clave, resultado);
            return resultado;
        } catch (error) {
            return 'Ubicacion actual';
        }
    }

    function limpiarResumenRuta() {
        const distancia = document.getElementById('distancia-hidden');
        const tiempo = document.getElementById('tiempo-hidden');
        const eta = document.getElementById('eta-solicitud');
        const distanciaLabel = document.getElementById('distancia-solicitud');
        const tarifaDetalle = document.getElementById('tarifa-detalle');

        if (distancia) distancia.value = '';
        if (tiempo) tiempo.value = '';
        if (eta) eta.textContent = '-- min';
        if (distanciaLabel) distanciaLabel.textContent = '-- km';
        if (tarifaDetalle) tarifaDetalle.textContent = '~0 km | 0 min';
    }

    async function actualizarRuta(forzar = false) {
        if (!marcadorOrigen || !marcadorDestino) {
            limpiarResumenRuta();
            return;
        }

        const origenLeaflet = marcadorOrigen.getLatLng();
        const destinoLeaflet = marcadorDestino.getLatLng();
        const origen = AltokkeMapa.puntoValido(origenLeaflet.lat, origenLeaflet.lng);
        const destino = AltokkeMapa.puntoValido(destinoLeaflet.lat, destinoLeaflet.lng);
        const claveRuta = origen && destino
            ? `${origen.lat.toFixed(5)},${origen.lng.toFixed(5)}|${destino.lat.toFixed(5)},${destino.lng.toFixed(5)}`
            : '';
        const estadoRuta = document.getElementById('estado-ruta-solicitud');
        const detalleRuta = document.getElementById('detalle-ruta-solicitud');

        if (!origen || !destino || !AltokkeMapa.puntosDistintos(origen, destino)) {
            if (lineaRuta && mapa) {
                mapa.removeLayer(lineaRuta);
                lineaRuta = null;
            }
            if (estadoRuta) estadoRuta.textContent = 'Sin ruta disponible';
            if (detalleRuta) detalleRuta.textContent = 'Marca origen y destino diferentes';
            limpiarResumenRuta();
            return;
        }

        if (!forzar && claveRuta && claveRuta === ultimaRutaClave) {
            return;
        }

        ultimaRutaClave = claveRuta;
        const solicitudActual = ++secuenciaRuta;

        if (estadoRuta) estadoRuta.textContent = 'Calculando ruta';
        const ruta = await AltokkeMapa.consultarRuta(origen, destino);
        if (solicitudActual !== secuenciaRuta) return;
        lineaRuta = AltokkeMapa.dibujarRuta(mapa, lineaRuta, ruta, {
            color: '#2d6a2d',
            weight: 6,
            opacity: 0.9,
        });

        if (lineaRuta) {
            mapa.fitBounds(lineaRuta.getBounds(), { padding: [50, 50] });
        }

        const distanciaKm = Number(ruta.distancia_km || 0);
        const tiempoMin = Number(ruta.duracion_min || 0);
        const radioExpress = document.querySelector('input[name="tipo_servicio"][value="express"]');
        const expressSeleccionado = radioExpress
            && radioExpress.closest('.servicio-chip')?.classList.contains('seleccionado');
        const tarifaBase = expressSeleccionado ? 5 : 3;
        const tarifa = (tarifaBase + (distanciaKm * 1.5)).toFixed(2);

        document.getElementById('tarifa-numero').textContent = `S/ ${tarifa}`;
        document.getElementById('tarifa-detalle').textContent = `~${distanciaKm.toFixed(1)} km | ${tiempoMin} min`;
        document.getElementById('eta-solicitud').textContent = `${tiempoMin || '--'} min`;
        document.getElementById('distancia-solicitud').textContent = `${distanciaKm.toFixed(1)} km`;
        if (estadoRuta) estadoRuta.textContent = ruta.ok ? 'Ruta estimada' : 'Sin ruta disponible';
        if (detalleRuta) detalleRuta.textContent = ruta.ok ? 'Ruta real calculada' : 'Usando linea simple entre puntos';

        document.getElementById('tarifa-hidden').value = tarifa;
        document.getElementById('distancia-hidden').value = distanciaKm.toFixed(2);
        document.getElementById('tiempo-hidden').value = tiempoMin;
    }

    function colocarOrigen(lat, lng, centrarMapa = false) {
        const punto = AltokkeMapa.puntoValido(lat, lng);
        if (!punto || !mapa) return;

        if (marcadorOrigen) mapa.removeLayer(marcadorOrigen);
        marcadorOrigen = L.marker(
            [punto.lat, punto.lng],
            { icon: AltokkeMapa.icono('origen', 'O') }
        ).addTo(mapa);

        if (centrarMapa) {
            mapa.flyTo([punto.lat, punto.lng], 17, {
                animate: true,
                duration: 0.8
            });
        }

        document.getElementById('origen-lat').value = punto.lat;
        document.getElementById('origen-lng').value = punto.lng;
        actualizarRuta();
    }

    function colocarDestino(lat, lng) {
        const punto = AltokkeMapa.puntoValido(lat, lng);
        if (!punto || !mapa) return;

        if (marcadorDestino) mapa.removeLayer(marcadorDestino);
        marcadorDestino = L.marker(
            [punto.lat, punto.lng],
            { icon: AltokkeMapa.icono('destino', 'D') }
        ).addTo(mapa);

        document.getElementById('destino-lat').value = punto.lat;
        document.getElementById('destino-lng').value = punto.lng;
        actualizarRuta();
    }

    function obtenerUbicacion() {
        const ubicacionTexto = document.getElementById('ubicacion-texto');
        if (!ubicacionTexto) return;

        if (!navigator.geolocation) {
            ubicacionTexto.textContent = 'Tu navegador no permite usar ubicacion';
            return;
        }

        ubicacionTexto.textContent = 'Obteniendo tu ubicacion...';
        navigator.geolocation.getCurrentPosition(
            async function (posicion) {
                const lat = posicion.coords.latitude;
                const lng = posicion.coords.longitude;

                if (!AltokkeMapa.esLatLngValido(lat, lng)) {
                    ubicacionTexto.textContent = 'No se pudo obtener una ubicacion valida';
                    return;
                }

                colocarOrigen(lat, lng, true);
                const direccion = await obtenerDireccion(lat, lng);
                document.getElementById('origen-input').value = direccion;
                ubicacionTexto.textContent = direccion;
            },
            function () {
                ubicacionTexto.textContent = 'Permite acceso a ubicacion o marca tu origen en el mapa';
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }

    function inicializarMapa() {
        mapa = AltokkeMapa.crearMapa('mapa-solicitud-pasajero', ubicacionInicial, 15);
        if (!mapa) return;

        document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
        document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());
        document.getElementById('mi-ubicacion')?.addEventListener('click', obtenerUbicacion);

        document.getElementById('origen-input')?.addEventListener('focus', () => {
            campoActivo = 'origen';
            actualizarEstadoSeleccion('Toca el mapa para marcar tu origen');
        });

        document.getElementById('destino-input')?.addEventListener('focus', () => {
            campoActivo = 'destino';
            actualizarEstadoSeleccion('Toca el mapa para marcar tu destino');
        });

        mapa.on('click', async (evento) => {
            const punto = AltokkeMapa.puntoValido(evento.latlng.lat, evento.latlng.lng);
            if (!punto) return;

            if (campoActivo === 'origen' || !marcadorOrigen) {
                colocarOrigen(punto.lat, punto.lng, false);
                document.getElementById('origen-input').value = await obtenerDireccion(punto.lat, punto.lng);
                campoActivo = 'destino';
                actualizarEstadoSeleccion('Origen marcado. Ahora marca tu destino');
                return;
            }

            colocarDestino(punto.lat, punto.lng);
            document.getElementById('destino-input').value = await obtenerDireccion(punto.lat, punto.lng);
            actualizarEstadoSeleccion('Destino marcado');
        });

        obtenerUbicacion();
    }

    function limpiarCoordenadas(tipo) {
        const esOrigen = tipo === 'origen';
        document.getElementById(`${tipo}-lat`).value = '';
        document.getElementById(`${tipo}-lng`).value = '';

        if (esOrigen && marcadorOrigen && mapa) {
            mapa.removeLayer(marcadorOrigen);
            marcadorOrigen = null;
        }

        if (!esOrigen && marcadorDestino && mapa) {
            mapa.removeLayer(marcadorDestino);
            marcadorDestino = null;
        }

        if (lineaRuta && mapa) {
            mapa.removeLayer(lineaRuta);
            lineaRuta = null;
        }

        ultimaRutaClave = '';
        limpiarResumenRuta();
    }

    function crearAutocomplete(input, tipo) {
        if (!input) return;

        const lista = document.createElement('div');
        lista.className = 'autocomplete-lista';
        lista.style.display = 'none';
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(lista);
        let tiempoBusqueda;
        let busquedaAbortController = null;

        input.addEventListener('input', function () {
            limpiarCoordenadas(tipo);
            clearTimeout(tiempoBusqueda);
            const texto = this.value;

            if (texto.length < 3) {
                lista.style.display = 'none';
                return;
            }

            tiempoBusqueda = setTimeout(async function () {
                const claveBusqueda = texto.trim().toLowerCase();
                try {
                    busquedaAbortController?.abort();

                    let data = busquedasCache.get(claveBusqueda);
                    if (!data) {
                        busquedaAbortController = new AbortController();
                        const response = await fetch(
                            `https://photon.komoot.io/api/?q=${encodeURIComponent(texto)}&limit=5`,
                            { signal: busquedaAbortController.signal }
                        );
                        if (!response.ok) throw new Error('No se pudo buscar');

                        data = await response.json();
                        busquedasCache.set(claveBusqueda, data);
                    }
                    lista.replaceChildren();

                    if (!data.features || data.features.length === 0) {
                        lista.style.display = 'none';
                        return;
                    }

                    data.features.forEach(function (lugar) {
                        const propiedades = lugar.properties;
                        const nombre = propiedades.name || propiedades.street || propiedades.city || 'Lugar';
                        const ciudad = propiedades.city || propiedades.state || '';
                        const lat = lugar.geometry.coordinates[1];
                        const lng = lugar.geometry.coordinates[0];

                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';

                        const titulo = document.createElement('div');
                        titulo.className = 'autocomplete-title';
                        titulo.textContent = `📍 ${nombre}`;

                        const subtitulo = document.createElement('div');
                        subtitulo.className = 'autocomplete-sub';
                        subtitulo.textContent = ciudad;

                        item.append(titulo, subtitulo);
                        item.addEventListener('click', function () {
                            input.value = `${nombre}${ciudad ? `, ${ciudad}` : ''}`;
                            lista.style.display = 'none';

                            if (tipo === 'origen') {
                                colocarOrigen(lat, lng, true);
                            } else {
                                colocarDestino(lat, lng);
                            }
                        });

                        lista.appendChild(item);
                    });

                    lista.style.display = 'block';
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    lista.style.display = 'none';
                }
            }, 600);
        });

        document.addEventListener('click', function (evento) {
            if (!input.parentNode.contains(evento.target)) {
                lista.style.display = 'none';
            }
        });
    }

    function leerPuntoFormulario(prefijo) {
        return AltokkeMapa.puntoValido(
            document.getElementById(`${prefijo}-lat`)?.value,
            document.getElementById(`${prefijo}-lng`)?.value
        );
    }

    function mostrarErrorMapa(mensaje) {
        const caja = document.getElementById('mapa-form-error');
        if (!caja) return;
        caja.textContent = mensaje;
        caja.hidden = false;
    }

    crearAutocomplete(document.getElementById('origen-input'), 'origen');
    crearAutocomplete(document.getElementById('destino-input'), 'destino');

    document.querySelectorAll('.servicio-chip').forEach(function (chip, indice, chips) {
        chip.addEventListener('click', function () {
            chips.forEach((elemento) => elemento.classList.remove('seleccionado'));
            this.classList.add('seleccionado');

            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            if (marcadorOrigen && marcadorDestino) {
                actualizarRuta(true);
            } else {
                const tarifa = radio && radio.value === 'express' ? 'S/ 5.00' : 'S/ 3.00';
                document.getElementById('tarifa-numero').textContent = tarifa;
            }
        });
    });

    document.querySelectorAll('.pago-opcion').forEach(function (opcion, indice, opciones) {
        opcion.addEventListener('click', function () {
            opciones.forEach((elemento) => elemento.classList.remove('activo'));
            this.classList.add('activo');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    document.getElementById('form-solicitar-viaje')?.addEventListener('submit', function (evento) {
        const cajaError = document.getElementById('mapa-form-error');
        if (cajaError) cajaError.hidden = true;

        const origen = leerPuntoFormulario('origen');
        const destino = leerPuntoFormulario('destino');
        const origenTexto = document.getElementById('origen-input')?.value?.trim();
        const destinoTexto = document.getElementById('destino-input')?.value?.trim();

        if (!origenTexto || !destinoTexto) {
            evento.preventDefault();
            mostrarErrorMapa('Ingresa origen y destino antes de solicitar.');
            return;
        }

        if (!origen || !destino) {
            evento.preventDefault();
            mostrarErrorMapa('Marca origen y destino en el mapa antes de solicitar.');
            return;
        }

        if (!AltokkeMapa.puntosDistintos(origen, destino)) {
            evento.preventDefault();
            mostrarErrorMapa('El origen y destino deben ser puntos diferentes.');
        }
    });

    inicializarMapa();
});
