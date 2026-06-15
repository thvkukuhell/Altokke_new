@extends('layouts.main')
@section('content')

@if ($errors->any())
<div class="alerta-errores">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Librerías de Leaflet necesarias --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@include('mapa.partials.leaflet_helpers')


<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-solicitud">-- min</div>
                        <div class="eta-unidad">Ruta</div>
                    </div>
                    <div class="eta-unidad" id="distancia-solicitud">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-solicitud">GPS activo</div>
                <div class="eta-detalle" id="detalle-ruta-solicitud">Elige tu destino</div>
            </div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">📍</span>
                <span id="ubicacion-texto">Obteniendo tu ubicación...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in" title="Acercar">+</button>
                <button class="mapa-boton-zoom" id="zoom-out" title="Alejar">−</button>
                <button class="mapa-boton-milocation" id="mi-ubicacion" title="Mi ubicación">🎯</button>
            </div>
        </div>

        {{-- Panel formulario --}}
        <div class="panel-solicitud">
            <p class="panel-solicitud-titulo">¿A dónde vamos?</p>
            <p class="panel-solicitud-sub">Ingresa tu origen y destino para solicitar tu mototaxi</p>

            <form action="{{ route('pasajero.crearViaje') }}" method="POST" id="form-solicitar-viaje">
                @csrf

                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text" name="origen" id="origen-input" placeholder="Origen" required>
                        <input type="hidden" name="origen_lat"       id="origen-lat">
                        <input type="hidden" name="origen_lng"       id="origen-lng">
                        <input type="hidden" name="destino_lat"      id="destino-lat">
                        <input type="hidden" name="destino_lng"      id="destino-lng">
                        <input type="hidden" name="tarifa_estimada"  id="tarifa-hidden">
                        <input type="hidden" name="distancia_km"     id="distancia-hidden">
                        <input type="hidden" name="tiempo_min"       id="tiempo-hidden">
                    </div>
                    <div class="ruta-fila">
                        <div class="punto punto-rojo"></div>
                        <input type="text" name="destino" id="destino-input" placeholder="¿A dónde vas?" required>
                    </div>
                </div>

                <div class="campo-grupo">
                    <label class="campo-label">Tipo de servicio</label>
                    <div class="servicio-chips">
                        <label class="servicio-chip seleccionado">
                            <input type="radio" name="tipo_servicio" value="normal" checked>
                            <span>🛺</span>
                            <span>Normal</span>
                            <span>Desde S/3</span>
                        </label>
                        <label class="servicio-chip">
                            <input type="radio" name="tipo_servicio" value="express">
                            <span>⚡</span>
                            <span>Express</span>
                            <span>Desde S/5</span>
                        </label>
                    </div>
                </div>

                <div class="campo-grupo">
                    <label class="campo-label">Método de pago</label>
                    <div class="pago-opciones">
                        <label class="pago-opcion activo">
                            <input type="radio" name="metodo_pago" value="efectivo" checked> Efectivo
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="yape"> Yape
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="plin"> Plin
                        </label>
                    </div>
                </div>

                <div class="tarifa-caja">
                    <div class="tarifa-numero" id="tarifa-numero">S/ 3.00</div>
                    <div class="tarifa-right">
                        <div class="tarifa-label">Tarifa estimada</div>
                        <div class="tarifa-detalle" id="tarifa-detalle">~0 km · 0 min</div>
                    </div>
                </div>

                <div class="mapa-form-error" id="mapa-form-error" hidden></div>
                <button type="submit" class="btn btn-verde btn-ancho">Solicitar mototaxi</button>
            </form>
        </div>
    </div>
</div>

<script>
let mapa;

let marcadorOrigen = null;
let marcadorDestino = null;
let lineaRuta = null;
let campoActivo = 'destino';

const DEFAULT_LOCATION = {
    lat: -5.63889,
    lng: -78.5311,
};

// ===============================
// INICIAR
// ===============================
window.addEventListener('load', () => {
    inicializarMapa();
});

// ===============================
// MAPA
// ===============================
function inicializarMapa() {
    mapa = AltokkeMapa.crearMapa('mapa-solicitud-pasajero', DEFAULT_LOCATION, 15);
    if (!mapa) return;

    // BOTONES
    document.getElementById('zoom-in')
        ?.addEventListener('click', () => mapa.zoomIn());

    document.getElementById('zoom-out')
        ?.addEventListener('click', () => mapa.zoomOut());

    document.getElementById('mi-ubicacion')
        ?.addEventListener('click', () => {
            obtenerUbicacion();
        });

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

function actualizarEstadoSeleccion(mensaje) {
    const ubicacionTexto = document.getElementById('ubicacion-texto');
    if (ubicacionTexto) ubicacionTexto.textContent = mensaje;
}

// ===============================
// UBICACION ACTUAL
// ===============================
function obtenerUbicacion() {
    const ubicacionTexto = document.getElementById('ubicacion-texto');

    if (!navigator.geolocation) {
        ubicacionTexto.textContent = 'Tu navegador no permite usar ubicación';
        return;
    }

    ubicacionTexto.textContent = 'Obteniendo tu ubicación...';
    navigator.geolocation.getCurrentPosition(
        async function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            if (!AltokkeMapa.esLatLngValido(lat, lng)) {
                ubicacionTexto.textContent = 'No se pudo obtener una ubicacion valida';
                return;
            }

            colocarOrigen(lat, lng, true);
            const direccion =
                await obtenerDireccion(lat, lng);
            document.getElementById(
                'origen-input'
            ).value = direccion;

            ubicacionTexto.textContent = direccion;
        },

        function(error) {
            ubicacionTexto.textContent =
                'Permite acceso a ubicacion o marca tu origen en el mapa';
        },

        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

// ===============================
// DIRECCIÓN HUMANA
// ===============================
async function obtenerDireccion(lat, lng) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1`, {
                headers: {
                    'Accept-Language': 'es'
                }
            }
        );

        const data = await response.json();
        if (!data.address) {
            return 'Ubicación actual';
        }

        const a = data.address;
        let calle =
            a.road ||
            a.residential ||
            a.pedestrian ||
            data.name ||
            '';

        let distrito =
            a.suburb ||
            a.city_district ||
            a.neighbourhood ||
            a.village ||
            a.town ||
            a.city ||
            '';

        let direccion = calle;
        if (distrito) {
            direccion += `, ${distrito}`;
        }

        return direccion || 'Ubicación actual';

    } catch (e) {
        return 'Ubicación actual';
    }
}

// ===============================
// MARCADOR ORIGEN
// ===============================
function colocarOrigen(lat, lng, centrarMapa = false) {
    const punto = AltokkeMapa.puntoValido(lat, lng);
    if (!punto || !mapa) return;

    if (marcadorOrigen) {
        mapa.removeLayer(marcadorOrigen);
    }
     marcadorOrigen = L.marker(
        [punto.lat, punto.lng],
        { icon: AltokkeMapa.icono('origen', 'O') }
    ).addTo(mapa);

    if (centrarMapa) {
        mapa.flyTo(
            [punto.lat, punto.lng],
            17, {
                animate: true,
                duration: 0.8
            }
        );
    }

    document.getElementById(
        'origen-lat'
    ).value = punto.lat;

    document.getElementById(
        'origen-lng'
    ).value = punto.lng;

    actualizarRuta();
}

// ===============================
// MARCADOR DESTINO
// ===============================
function colocarDestino(lat, lng) {
    const punto = AltokkeMapa.puntoValido(lat, lng);
    if (!punto || !mapa) return;

    if (marcadorDestino) {
        mapa.removeLayer(marcadorDestino);
    }
    marcadorDestino = L.marker(
        [punto.lat, punto.lng],
        { icon: AltokkeMapa.icono('destino', 'D') }
    ).addTo(mapa);

    document.getElementById('destino-lat').value = punto.lat;
    document.getElementById('destino-lng').value = punto.lng;

    actualizarRuta();
}

// ===============================
// DIBUJAR RUTA REAL
// ===============================
async function actualizarRuta() {
    if (!marcadorOrigen || !marcadorDestino) {
        limpiarResumenRuta();
        return;
    }
    const origenLeaflet = marcadorOrigen.getLatLng();
    const destinoLeaflet = marcadorDestino.getLatLng();
    const origen = AltokkeMapa.puntoValido(origenLeaflet.lat, origenLeaflet.lng);
    const destino = AltokkeMapa.puntoValido(destinoLeaflet.lat, destinoLeaflet.lng);
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

    if (estadoRuta) estadoRuta.textContent = 'Calculando ruta';
    const ruta = await AltokkeMapa.consultarRuta(origen, destino);
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
    let tarifaBase = 3.00;

    if (radioExpress && radioExpress.closest('.servicio-chip')?.classList.contains('seleccionado')) {
        tarifaBase = 5.00;
    }

    let tarifa = tarifaBase + (distanciaKm * 1.5);
    tarifa = tarifa.toFixed(2);

    document.getElementById('tarifa-numero').innerHTML = `S/ ${tarifa}`;
    document.getElementById('tarifa-detalle').innerHTML = `~${distanciaKm.toFixed(1)} km | ${tiempoMin} min`;
    document.getElementById('eta-solicitud').textContent = `${tiempoMin || '--'} min`;
    document.getElementById('distancia-solicitud').textContent = `${distanciaKm.toFixed(1)} km`;
    if (estadoRuta) estadoRuta.textContent = ruta.ok ? 'Ruta estimada' : 'Sin ruta disponible';
    if (detalleRuta) detalleRuta.textContent = ruta.ok ? 'Ruta real calculada' : 'Usando linea simple entre puntos';

    document.getElementById('tarifa-hidden').value    = tarifa;
    document.getElementById('distancia-hidden').value = distanciaKm.toFixed(2);
    document.getElementById('tiempo-hidden').value    = tiempoMin;
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
// AUTOCOMPLETE
crearAutocomplete(
    document.getElementById('origen-input'),
    'origen'
);

crearAutocomplete(
    document.getElementById('destino-input'),
    'destino'
);

function crearAutocomplete(input, tipo) {
    if (!input) return;

    const lista =
        document.createElement('div');
    lista.className =
        'autocomplete-lista';
    lista.style.display = 'none';
    input.parentNode.style.position =
        'relative';
    input.parentNode.appendChild(lista);
    let timeoutBusqueda;

    input.addEventListener('input', function() {
        limpiarCoordenadas(tipo);
        clearTimeout(timeoutBusqueda);
        const query = this.value;
        if (query.length < 3) {

            lista.style.display = 'none';
            return;
        }
        timeoutBusqueda = setTimeout(async () => {
            try {
                const response = await fetch(
                    `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5`
                );
                const data =
                    await response.json();
                lista.innerHTML = '';

                if (
                    !data.features ||
                    data.features.length === 0
                ) {
                    lista.style.display = 'none';
                    return;
                }

                data.features.forEach(lugar => {
                    const props =
                        lugar.properties;

                    const nombre =
                        props.name ||
                        props.street ||
                        props.city ||
                        'Lugar';

                    const ciudad =
                        props.city ||
                        props.state ||
                        '';

                    const lat =
                        lugar.geometry.coordinates[1];

                    const lng =
                        lugar.geometry.coordinates[0];

                    const item =
                        document.createElement('div');

                    item.className =
                        'autocomplete-item';

                    item.innerHTML = `
                        <div class="autocomplete-title">
                            📍 ${nombre}
                        </div>

                        <div class="autocomplete-sub">
                            ${ciudad}
                        </div>
                    `;

                    item.addEventListener(
                        'click',
                        () => {

                            input.value =
                                `${nombre}${ciudad ? ', ' + ciudad : ''}`;

                            lista.style.display =
                                'none';

                            if (tipo === 'origen') {
                                colocarOrigen(
                                    lat,
                                    lng,
                                    true
                                );

                            } else {
                                colocarDestino(
                                    lat,
                                    lng
                                );
                            }
                        }
                    );

                    lista.appendChild(item);
                });

                lista.style.display = 'block';

            } catch (e) {

                lista.style.display = 'none';
            }

        }, 300);
    });
    // CERRAR
    document.addEventListener(
        'click',
        function(e) {
            if (
                !input.parentNode.contains(e.target)
            ) {
                lista.style.display =
                    'none';
            }
        }
    );
}

function limpiarCoordenadas(tipo) {
    if (tipo === 'origen') {
        document.getElementById('origen-lat').value = '';
        document.getElementById('origen-lng').value = '';
        if (marcadorOrigen && mapa) {
            mapa.removeLayer(marcadorOrigen);
            marcadorOrigen = null;
        }
        if (lineaRuta && mapa) {
            mapa.removeLayer(lineaRuta);
            lineaRuta = null;
        }
        limpiarResumenRuta();
        return;
    }

    document.getElementById('destino-lat').value = '';
    document.getElementById('destino-lng').value = '';
    if (marcadorDestino && mapa) {
        mapa.removeLayer(marcadorDestino);
        marcadorDestino = null;
    }
    if (lineaRuta && mapa) {
        mapa.removeLayer(lineaRuta);
        lineaRuta = null;
    }
    limpiarResumenRuta();
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

document.addEventListener('DOMContentLoaded', () => {
    // Manejo de clicks en Tipo de Servicio (Normal / Express)
    const chips = document.querySelectorAll('.servicio-chip');
    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            // 1. Limpiamos y asignamos la clase visual primero
            chips.forEach(c => c.classList.remove('seleccionado'));
            this.classList.add('seleccionado');
            
            // 2. Marcamos el input radio correspondiente internamente
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
            
            // 3. Forzamos el recalculo inmediato si ya hay una ruta trazada
            if (marcadorOrigen && marcadorDestino) {
                actualizarRuta();
            } else {
                // Si aún no hay ruta, solo actualiza el texto del precio base inicial
                const tarifaNumElement = document.getElementById('tarifa-numero');
                if (tarifaNumElement) {
                    tarifaNumElement.innerHTML = radio && radio.value === 'express' ? 'S/ 5.00' : 'S/ 3.00';
                }
            }
        });
    });

    // Manejo de clicks en Métodos de Pago
    const opcionesPago = document.querySelectorAll('.pago-opcion');
    opcionesPago.forEach(opcion => {
        opcion.addEventListener('click', function() {
            opcionesPago.forEach(o => o.classList.remove('activo'));
            this.classList.add('activo');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    document.getElementById('form-solicitar-viaje')?.addEventListener('submit', (event) => {
        const cajaError = document.getElementById('mapa-form-error');
        if (cajaError) cajaError.hidden = true;

        const origen = leerPuntoFormulario('origen');
        const destino = leerPuntoFormulario('destino');
        const origenTexto = document.getElementById('origen-input')?.value?.trim();
        const destinoTexto = document.getElementById('destino-input')?.value?.trim();

        if (!origenTexto || !destinoTexto) {
            event.preventDefault();
            mostrarErrorMapa('Ingresa origen y destino antes de solicitar.');
            return;
        }

        if (!origen || !destino) {
            event.preventDefault();
            mostrarErrorMapa('Marca origen y destino en el mapa antes de solicitar.');
            return;
        }

        if (!AltokkeMapa.puntosDistintos(origen, destino)) {
            event.preventDefault();
            mostrarErrorMapa('El origen y destino deben ser puntos diferentes.');
        }
    });
});
</script>

@endsection
