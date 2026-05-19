@extends('layouts.main')
@section('content')

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

        <div class="panel-solicitud">
            <p class="panel-solicitud-titulo">¿A dónde vamos?</p>
            <p class="panel-solicitud-sub">Ingresa tu origen y destino para solicitar tu mototaxi</p>

            <form action="{{ route('pasajero.crearViaje') }}" method="POST">
                @csrf

                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text" name="origen" id="origen-input" placeholder="Ej: Av. 27 de Octubre, Cajaruro"
                            required>
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

{{-- Librerías de Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>



<script>
let mapa;
let marcador;
let circulo;

function inicializarMapa() {
    const contenedor = document.getElementById('mapa-solicitud-pasajero');
    if (!contenedor) return;

    mapa = L.map('mapa-solicitud-pasajero').setView([-5.736426, -78.4277115], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    setTimeout(() => mapa.invalidateSize(), 100);

    const icono = L.divIcon({
        html: '<div style="font-size: 32px;">📍</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });

    marcador = L.marker([-5.736426, -78.4277115], {
        icon: icono
    }).addTo(mapa);
    circulo = L.circle([-5.736426, -78.4277115], {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.1,
        radius: 100
    }).addTo(mapa);

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());
}

async function obtenerDireccionDesdeCoordenadas(lat, lng) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
        const data = await response.json();

        if (data && data.display_name) {
            let direccion = '';
            if (data.address.road) {
                direccion = data.address.road;
                if (data.address.house_number) direccion += ` ${data.address.house_number}`;
            } else if (data.address.suburb) {
                direccion = data.address.suburb;
            } else if (data.address.city) {
                direccion = data.address.city;
            } else if (data.address.town) {
                direccion = data.address.town;
            } else {
                direccion = data.display_name.split(',')[0];
            }
            return direccion;
        }
        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    } catch (e) {
        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }
}

function obtenerUbicacionPorGPS() {
    const origenInput = document.getElementById('origen-input');
    const latInput = document.getElementById('origen-lat');
    const lngInput = document.getElementById('origen-lng');
    const ubicacionSpan = document.getElementById('ubicacion-texto');

    if (!navigator.geolocation) {
        ubicacionSpan.innerHTML = '📍 GPS no soportado';
        origenInput.placeholder = 'Escribe tu ubicación';
        return;
    }

    ubicacionSpan.innerHTML = '📍 Obteniendo GPS...';
    origenInput.value = 'Obteniendo ubicación...';

    navigator.geolocation.getCurrentPosition(
        async function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const precision = pos.coords.accuracy;

                console.log('📍 GPS:', lat, lng);
                console.log('📡 Precisión:', precision, 'metros');

                // Guardar coordenadas
                latInput.value = lat;
                lngInput.value = lng;

                // Centrar mapa
                mapa.setView([lat, lng], 16);
                marcador.setLatLng([lat, lng]);
                circulo.setLatLng([lat, lng]);
                circulo.setRadius(Math.min(precision, 150));

                // Obtener dirección
                const direccion = await obtenerDireccionDesdeCoordenadas(lat, lng);
                origenInput.value = direccion;
                ubicacionSpan.innerHTML = direccion;

                // El usuario puede editar si quiere
                origenInput.readOnly = false;
                origenInput.style.background = 'white';

                console.log('📍 Ubicación:', direccion);

                setTimeout(() => mapa.invalidateSize(), 200);
            },
            function(error) {
                console.error('Error GPS:', error);
                let msg = '📍 GPS no disponible';
                if (error.code === 1) msg = '📍 Permiso denegado';

                ubicacionSpan.innerHTML = msg;
                origenInput.value = '';
                origenInput.placeholder = 'Escribe tu ubicación';
                origenInput.readOnly = false;
                origenInput.style.background = 'white';
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            }
    );
}

async function buscarYActualizarMapa(direccion) {
    if (!direccion || direccion.length < 5) return false;

    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(direccion)}, Peru&limit=1`
            );
        const data = await response.json();

        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);

            mapa.setView([lat, lng], 17);
            marcador.setLatLng([lat, lng]);
            circulo.setLatLng([lat, lng]);

            document.getElementById('origen-lat').value = lat;
            document.getElementById('origen-lng').value = lng;
            document.getElementById('ubicacion-texto').innerHTML = direccion;

            return true;
        }
        return false;
    } catch (e) {
        return false;
    }
}

// Inicializar
window.addEventListener('load', function() {
    inicializarMapa();
    obtenerUbicacionPorGPS();

    const origenInput = document.getElementById('origen-input');
    const ubicacionSpan = document.getElementById('ubicacion-texto');
    let timeoutBusqueda;

    // Buscar mientras escribe (si el usuario cambia manualmente)
    origenInput.addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);

        timeoutBusqueda = setTimeout(async () => {
            if (this.value.length > 5 && !this.value.includes(',')) {
                ubicacionSpan.innerHTML = '📍 Buscando...';
                const encontrado = await buscarYActualizarMapa(this.value);
                if (encontrado) {
                    ubicacionSpan.innerHTML = '📍 ' + this.value;
                } else {
                    ubicacionSpan.innerHTML = '📍 No encontrado - escribe más específico';
                }
            }
        }, 800);
    });

    // Botón de mi ubicación (forzar GPS)
    document.getElementById('mi-ubicacion')?.addEventListener('click', () => {
        obtenerUbicacionPorGPS();
    });

    window.addEventListener('resize', () => setTimeout(() => mapa?.invalidateSize(), 200));
});

// Tarifa dinámica
const destino = document.getElementById('destino-input');
const tarifaNum = document.getElementById('tarifa-numero');
const tarifaDet = document.getElementById('tarifa-detalle');

if (destino) {
    destino.addEventListener('input', function() {
        if (this.value.length > 3) {
            const tipo = document.querySelector('input[name="tipo_servicio"]:checked').value;
            const base = tipo === 'normal' ? 3 : 5;
            const dist = (Math.random() * 5 + 1).toFixed(1);
            const total = (base + (dist * 0.5)).toFixed(2);
            tarifaNum.textContent = `S/ ${total}`;
            tarifaDet.textContent = `~${dist} km · ${Math.floor(dist * 2.5)} min`;
        }
    });
}

// Servicio
document.querySelectorAll('.servicio-chip input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.servicio-chip').forEach(c => c.classList.remove('seleccionado'));
        this.closest('.servicio-chip').classList.add('seleccionado');
        if (destino && destino.value.length > 3) {
            const base = this.value === 'normal' ? 3 : 5;
            const dist = (Math.random() * 5 + 1).toFixed(1);
            tarifaNum.textContent = `S/ ${(base + (dist * 0.5)).toFixed(2)}`;
        } else {
            tarifaNum.textContent = this.value === 'normal' ? 'S/ 3.00' : 'S/ 5.00';
        }
    });
});

// Pago
document.querySelectorAll('.pago-opcion input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.pago-opcion').forEach(o => o.classList.remove('activo'));
        this.closest('.pago-opcion').classList.add('activo');
    });
});
</script>

@endsection