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


<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
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

            <form action="{{ route('pasajero.crearViaje') }}" method="POST">
                @csrf

                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text" name="origen" id="origen-input" placeholder="Origen" required>
                        <input type="hidden" name="origen_lat" id="origen-lat">
                        <input type="hidden" name="origen_lng" id="origen-lng">
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
    mapa = L.map('mapa-solicitud-pasajero', {
        zoomControl: false
    }).setView(
        [DEFAULT_LOCATION.lat, DEFAULT_LOCATION.lng],
        15
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }
    ).addTo(mapa);

    // BOTONES
    document.getElementById('zoom-in')
        ?.addEventListener('click', () => mapa.zoomIn());

    document.getElementById('zoom-out')
        ?.addEventListener('click', () => mapa.zoomOut());

    document.getElementById('mi-ubicacion')
        ?.addEventListener('click', () => {
            obtenerUbicacion();
        });

    obtenerUbicacion();
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

            colocarOrigen(lat, lng, true);
            const direccion =
                await obtenerDireccion(lat, lng);
            document.getElementById(
                'origen-input'
            ).value = direccion;

            ubicacionTexto.textContent = direccion;
        },

        function(error) {
            console.log(error);
            ubicacionTexto.textContent =
                'Permite acceso a ubicación para detectar tu origen';
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
        console.log(e);
        return 'Ubicación actual';
    }
}

// ===============================
// MARCADOR ORIGEN
// ===============================
function colocarOrigen(lat, lng, centrarMapa = false) {
    if (marcadorOrigen) {
        mapa.removeLayer(marcadorOrigen);
    }
     marcadorOrigen = L.marker(
        [lat, lng]
    ).addTo(mapa);

    if (centrarMapa) {
        mapa.flyTo(
            [lat, lng],
            17, {
                animate: true,
                duration: 0.8
            }
        );
    }

    document.getElementById(
        'origen-lat'
    ).value = lat;

    document.getElementById(
        'origen-lng'
    ).value = lng;

    actualizarRuta();
}

// ===============================
// MARCADOR DESTINO
// ===============================
function colocarDestino(lat, lng) {
    if (marcadorDestino) {
        mapa.removeLayer(marcadorDestino);
    }
    marcadorDestino = L.marker(
        [lat, lng]
    ).addTo(mapa);

    actualizarRuta();
}

// ===============================
// DIBUJAR RUTA REAL
// ===============================
async function actualizarRuta() {
    if (!marcadorOrigen || !marcadorDestino) return;
    const origen = marcadorOrigen.getLatLng();
    const destino = marcadorDestino.getLatLng();
    if (lineaRuta) mapa.removeLayer(lineaRuta);
    
    try {
        const response = await fetch(
            `https://router.project-osrm.org/route/v1/driving/${origen.lng},${origen.lat};${destino.lng},${destino.lat}?overview=full&geometries=geojson`
        );
        const data = await response.json();
        if (!data.routes || !data.routes.length) return;
        const ruta = data.routes[0];
        const coordenadas = ruta.geometry.coordinates.map(coord => [coord[1], coord[0]]);

        lineaRuta = L.polyline(coordenadas, {
            color: '#16a34a',
            weight: 6,
            opacity: 0.9,
            lineJoin: 'round'
        }).addTo(mapa);

        mapa.fitBounds(lineaRuta.getBounds(), { padding: [50, 50] });
        
        const distanciaKm = ruta.distance / 1000;
        const tiempoMin = Math.ceil(ruta.duration / 60);
        
        // --- AQUÍ ESTÁ EL TRUCO: Detectar cuál servicio está marcado ---
        const radioExpress = document.querySelector('input[name="tipo_servicio"][value="express"]');
        let tarifaBase = 3.00;
        
        // Validamos si el elemento existe y además su contenedor tiene la clase "seleccionado"
        if (radioExpress && radioExpress.closest('.servicio-chip').classList.contains('seleccionado')) {
            tarifaBase = 5.00;
        }

        // Realizar el cálculo matemático con la base correspondiente
        let tarifa = tarifaBase + (distanciaKm * 1.5);
        tarifa = tarifa.toFixed(2);
        
        // Renderizar los datos actualizados en la tarjeta verde
        document.getElementById('tarifa-numero').innerHTML = `S/ ${tarifa}`;
        document.getElementById('tarifa-detalle').innerHTML = `~${distanciaKm.toFixed(1)} km · ${tiempoMin} min`;
        
    } catch (e) {
        console.log(e);
    }
}

// ===============================
// DISTANCIA Y TARIFA
// ===============================
function calcularTarifa(
    lat1,
    lon1,
    lat2,
    lon2
) {

    const R = 6371;
    const dLat =
        (lat2 - lat1) * Math.PI / 180;
    const dLon =
        (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) *
        Math.sin(dLat / 2) +

        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *

        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);
    const c =
        2 * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );
    const distancia = R * c;

    // TARIFA SIMPLE
    let tarifa =
        3 + (distancia * 1.5);

    tarifa = tarifa.toFixed(2);

    // TIEMPO APROX
    const tiempo =
        Math.ceil(distancia * 3);

    document.getElementById(
            'tarifa-numero'
        ).innerHTML =
        `S/ ${tarifa}`;

    document.getElementById(
            'tarifa-detalle'
        ).innerHTML =
        `~${distancia.toFixed(1)} km · ${tiempo} min`;
}

// ===============================
// AUTOCOMPLETE
// ===============================
crearAutocomplete(
    document.getElementById('origen-input'),
    'origen'
);

crearAutocomplete(
    document.getElementById('destino-input'),
    'destino'
);

function crearAutocomplete(input, tipo) {

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

                console.log(e);
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
});
</script>

@endsection