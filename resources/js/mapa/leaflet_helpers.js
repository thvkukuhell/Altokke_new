const configuracionMapa = document.getElementById('altokke-mapa-config');
const rutaUrl = configuracionMapa?.dataset.rutaUrl || '';
const csrfToken = configuracionMapa?.dataset.csrfToken || '';

window.AltokkeMapa = window.AltokkeMapa || (() => {
    const BAGUA = { lat: -5.63889, lng: -78.5311 };
    const CAJARURO = { lat: -5.6763, lng: -78.5311 };
    const simulaciones = new Map();
    const rutasCache = new Map();
    const rutasPendientes = new Map();

    function numeroSeguro(value, fallback = null) {
        const n = Number(value);
        return Number.isFinite(n) ? n : fallback;
    }

    function esLatLngValido(lat, lng) {
        if ([lat, lng].some((valor) => valor === null || valor === undefined || String(valor).trim() === '')) {
            return false;
        }

        const latNum = Number(lat);
        const lngNum = Number(lng);
        return Number.isFinite(latNum)
            && Number.isFinite(lngNum)
            && !(latNum === 0 && lngNum === 0)
            && latNum >= -90
            && latNum <= 90
            && lngNum >= -180
            && lngNum <= 180;
    }

    function puntoSeguro(lat, lng, fallback = BAGUA) {
        const fallbackSeguro = esLatLngValido(fallback?.lat, fallback?.lng) ? fallback : BAGUA;
        if (!esLatLngValido(lat, lng)) {
            return { lat: Number(fallbackSeguro.lat), lng: Number(fallbackSeguro.lng) };
        }

        return {
            lat: Number(lat),
            lng: Number(lng),
        };
    }

    function puntoValido(lat, lng) {
        if (!esLatLngValido(lat, lng)) return null;
        return { lat: Number(lat), lng: Number(lng) };
    }

    function puntoCercano(origen, semilla, distanciaKm = 0.3) {
        const origenSeguro = puntoValido(origen?.lat, origen?.lng) || BAGUA;
        const angulo = (((Number(semilla) || 1) * 137.508) % 360) * Math.PI / 180;
        const latRadianes = origenSeguro.lat * Math.PI / 180;

        return puntoSeguro(
            origenSeguro.lat + ((distanciaKm / 111.32) * Math.cos(angulo)),
            origenSeguro.lng + ((distanciaKm / (111.32 * Math.cos(latRadianes))) * Math.sin(angulo)),
            BAGUA
        );
    }

    async function fetchJson(url, options = {}) {
        if (!url) {
            console.warn('AltokkeMapa: fetchJson missing url');
            return null;
        }

        try {
            const response = await fetch(url, options);
            if (response.status === 429) {
                console.warn('AltokkeMapa: demasiadas solicitudes. Se esperara antes de volver a consultar.', url);
                return null;
            }

            if (!response.ok) {
                console.warn('AltokkeMapa: fetchJson HTTP error', response.status, response.statusText, url);
                return null;
            }

            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            if (!contentType.includes('application/json')) {
                console.warn('AltokkeMapa: fetchJson expected JSON, got', contentType, url);
                return null;
            }

            return await response.json();
        } catch (error) {
            console.error('AltokkeMapa: fetchJson failed', error, url);
            return null;
        }
    }

    function puntosDistintos(origen, destino, metrosMinimos = 25) {
        if (!origen || !destino) return false;
        return distanciaSimple(origen, destino) * 1000 >= metrosMinimos;
    }

    function puedeUsarMapa(id) {
        const el = document.getElementById(id);
        return Boolean(el && window.L && !el.dataset.leafletReady);
    }

    function marcarMapaListo(id) {
        const el = document.getElementById(id);
        if (el) el.dataset.leafletReady = '1';
    }

    function icono(tipo, texto) {
        const labels = {
            conductor: 'Moto',
            origen: 'Origen',
            destino: 'Destino',
        };

        return L.divIcon({
            className: `altokke-pin altokke-pin-${tipo}`,
            html: `<span>${texto}</span><small>${labels[tipo] || ''}</small>`,
            iconSize: [46, 54],
            iconAnchor: [23, 47],
            popupAnchor: [0, -42],
        });
    }

    function crearMapa(id, centro = BAGUA, zoom = 15) {
        if (!puedeUsarMapa(id)) return null;
        marcarMapaListo(id);

        const punto = puntoSeguro(centro.lat, centro.lng, BAGUA);
        const mapa = L.map(id, { zoomControl: false })
            .setView([punto.lat, punto.lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'OpenStreetMap'
        }).addTo(mapa);

        window.setTimeout(() => mapa.invalidateSize(), 250);
        return mapa;
    }

    function crearMarcador(mapa, punto, tipo, texto, popup = null) {
        if (!mapa || !punto || !esLatLngValido(punto.lat, punto.lng)) return null;

        const marcador = L.marker([punto.lat, punto.lng], {
            icon: icono(tipo, texto),
            zIndexOffset: tipo === 'conductor' ? 1000 : 0,
        }).addTo(mapa);

        if (popup) marcador.bindPopup(popup);
        if (tipo === 'conductor' && typeof marcador.setZIndexOffset === 'function') {
            marcador.setZIndexOffset(1000);
        }
        return marcador;
    }

    function ajustarVista(mapa, puntos, padding = [42, 42]) {
        if (!mapa) return;
        const validos = puntos.filter((p) => p && esLatLngValido(p.lat, p.lng));
        if (!validos.length) return;

        if (validos.length === 1) {
            mapa.setView([validos[0].lat, validos[0].lng], 15);
            return;
        }

        const bounds = L.latLngBounds(validos.map((p) => [p.lat, p.lng]));
        let distanciaMayor = 0;
        validos.forEach((punto, index) => {
            validos.slice(index + 1).forEach((siguiente) => {
                distanciaMayor = Math.max(distanciaMayor, distanciaSimple(punto, siguiente));
            });
        });

        if (distanciaMayor > 0 && distanciaMayor < 0.08) {
            mapa.setView(bounds.getCenter(), 16);
            return;
        }

        mapa.fitBounds(bounds, { padding, maxZoom: 17 });
    }

    async function consultarRuta(origen, destino) {
        const origenSeguro = puntoValido(origen?.lat, origen?.lng);
        const destinoSeguro = puntoValido(destino?.lat, destino?.lng);

        if (!origenSeguro || !destinoSeguro) {
            return {
                ok: false,
                estado: 'invalida',
                coordenadas: [],
                distancia_km: 0,
                duracion_min: 0,
            };
        }

        const fallback = {
            ok: false,
            estado: 'fallback',
            coordenadas: [[origenSeguro.lat, origenSeguro.lng], [destinoSeguro.lat, destinoSeguro.lng]],
            distancia_km: distanciaSimple(origenSeguro, destinoSeguro),
            duracion_min: Math.max(1, Math.ceil(distanciaSimple(origenSeguro, destinoSeguro) * 3)),
        };

        if (!rutaUrl) {
            console.warn('AltokkeMapa: consultarRuta missing rutaUrl');
            return fallback;
        }

        const claveRuta = [
            origenSeguro.lat.toFixed(5),
            origenSeguro.lng.toFixed(5),
            destinoSeguro.lat.toFixed(5),
            destinoSeguro.lng.toFixed(5),
        ].join('|');

        if (rutasCache.has(claveRuta)) {
            return rutasCache.get(claveRuta);
        }

        if (rutasPendientes.has(claveRuta)) {
            return rutasPendientes.get(claveRuta);
        }

        const peticion = fetchJson(rutaUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ origen: origenSeguro, destino: destinoSeguro }),
        }).finally(() => {
            rutasPendientes.delete(claveRuta);
        });

        rutasPendientes.set(claveRuta, peticion);

        const data = await peticion;

        if (!data || typeof data !== 'object') {
            return fallback;
        }

        const ruta = {
            ...fallback,
            ...data,
            coordenadas: Array.isArray(data.coordenadas) && data.coordenadas.length
                ? data.coordenadas
                : fallback.coordenadas,
        };

        rutasCache.set(claveRuta, ruta);
        return ruta;
    }

    function dibujarRuta(mapa, capaActual, data, opciones = {}) {
        if (!mapa) return null;
        if (capaActual) {
            try {
                mapa.removeLayer(capaActual);
            } catch (error) {
            }
        }

        const coords = (data?.coordenadas || [])
            .map((p) => [numeroSeguro(p[0], null), numeroSeguro(p[1], null)])
            .filter((p) => esLatLngValido(p[0], p[1]));

        if (coords.length < 2) return null;

        return L.polyline(coords, {
            color: opciones.color || '#1f7a3a',
            weight: opciones.weight || 6,
            opacity: opciones.opacity || 0.9,
            dashArray: opciones.dashArray ?? (data.ok ? null : '8 7'),
            lineCap: 'round',
            lineJoin: 'round',
        }).addTo(mapa);
    }

    function distanciaSimple(origen, destino) {
        if (!puntoValido(origen?.lat, origen?.lng) || !puntoValido(destino?.lat, destino?.lng)) {
            return 0;
        }

        const r = 6371;
        const dLat = (destino.lat - origen.lat) * Math.PI / 180;
        const dLng = (destino.lng - origen.lng) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(origen.lat * Math.PI / 180)
            * Math.cos(destino.lat * Math.PI / 180)
            * Math.sin(dLng / 2) ** 2;
        return Number((r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))).toFixed(2));
    }

    function textoRuta(data) {
        const distancia = Number(data.distancia_km || 0).toFixed(1);
        const minutos = Math.max(1, Number(data.duracion_min || 1));
        return `${distancia} km | ${minutos} min`;
    }

    function actualizarTexto(id, texto) {
        const el = document.getElementById(id);
        if (el) el.textContent = texto;
    }

    function normalizarRutaParaMovimiento(coordenadas, minimoPasos = 48) {
        const validas = (coordenadas || [])
            .map((p) => puntoValido(p[0], p[1]))
            .filter(Boolean);

        if (validas.length < 2) return validas;
        if (validas.length >= minimoPasos) return validas;

        let distanciaTotal = 0;
        for (let i = 0; i < validas.length - 1; i += 1) {
            distanciaTotal += Math.max(0.001, distanciaSimple(validas[i], validas[i + 1]));
        }

        const puntos = [];
        // 1J_RUTA_REAL_NO_LINEA_RECTA -> luego ir a mover moto por puntos OSRM
        for (let i = 0; i < validas.length - 1; i += 1) {
            const actual = validas[i];
            const siguiente = validas[i + 1];
            const distanciaSegmento = Math.max(0.001, distanciaSimple(actual, siguiente));
            const pasosSegmento = Math.max(
                1,
                Math.ceil((distanciaSegmento / distanciaTotal) * Math.max(minimoPasos, validas.length))
            );

            for (let paso = 0; paso < pasosSegmento; paso += 1) {
                const avance = paso / pasosSegmento;
                puntos.push({
                    lat: actual.lat + ((siguiente.lat - actual.lat) * avance),
                    lng: actual.lng + ((siguiente.lng - actual.lng) * avance),
                });
            }
        }

        puntos.push(validas[validas.length - 1]);

        return puntos;
    }

    function moverMarcadorSuave(marcador, destino, duracionMs = 900) {
        if (!marcador || !destino || !esLatLngValido(destino.lat, destino.lng)) return;

        const inicio = marcador.getLatLng();
        const desde = { lat: inicio.lat, lng: inicio.lng };
        const inicioMs = performance.now();

        function animar(ahora) {
            const avance = Math.min(1, (ahora - inicioMs) / duracionMs);
            const lat = desde.lat + ((destino.lat - desde.lat) * avance);
            const lng = desde.lng + ((destino.lng - desde.lng) * avance);
            marcador.setLatLng([lat, lng]);
            if (typeof marcador.setZIndexOffset === 'function') {
                marcador.setZIndexOffset(1000);
            }

            if (avance < 1) {
                window.requestAnimationFrame(animar);
            }
        }

        window.requestAnimationFrame(animar);
    }

    function detenerSimulacion(id) {
        const activa = simulaciones.get(id);
        if (!activa) return;
        window.clearInterval(activa.timer);
        simulaciones.delete(id);
    }

    function iniciarSimulacion(id, opciones) {
        detenerSimulacion(id);

        const marcador = opciones.marcador;
        const ruta = normalizarRutaParaMovimiento(opciones.coordenadas, opciones.minimoPasos || 54);
        if (!marcador || ruta.length < 2) return null;

        let indice = Math.max(0, opciones.indiceInicial || 0);
        const intervaloMs = opciones.intervaloMs || 1700;
        const avancePorTick = Math.max(1, Math.floor(opciones.avancePorTick || 1));

        const timer = window.setInterval(() => {
            if (typeof opciones.debeDetener === 'function' && opciones.debeDetener()) {
                detenerSimulacion(id);
                return;
            }

            indice += avancePorTick;
            const punto = ruta[indice];

            if (!punto) {
                detenerSimulacion(id);
                if (typeof opciones.alFinalizar === 'function') opciones.alFinalizar();
                return;
            }

            moverMarcadorSuave(marcador, punto, Math.min(1200, intervaloMs));
            if (typeof opciones.alMover === 'function') opciones.alMover(punto, indice, ruta.length);
        }, intervaloMs);

        simulaciones.set(id, { timer });
        return timer;
    }

    return {
        BAGUA,
        CAJARURO,
        safeNumber: numeroSeguro,
        numeroSeguro,
        esLatLngValido,
        puntoSeguro,
        puntoValido,
        puntoCercano,
        puntosDistintos,
        puedeUsarMapa,
        marcarMapaListo,
        icono,
        crearMapa,
        crearMarcador,
        ajustarVista,
        consultarRuta,
        dibujarRuta,
        distanciaSimple,
        textoRuta,
        actualizarTexto,
        moverMarcadorSuave,
        iniciarSimulacion,
        detenerSimulacion,
        normalizarRutaParaMovimiento,
    };
})();
