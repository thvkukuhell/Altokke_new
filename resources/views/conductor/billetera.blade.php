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

<div class="pagina-pasajero">

    <div class="solicitar-grid">

        {{-- MAPA CON LEAFLET --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero" style="width: 100%; height: 100%; min-height: 400px;"></div>
            <div class="mapa-etiqueta" id="mapa-etiqueta">
                📍 <span id="ubicacion-texto">Obteniendo tu ubicación...</span>
            </div>
        </div>

        {{-- Panel formulario --}}
        <div class="panel-solicitud">
            <p class="panel-solicitud-titulo">¿A dónde vamos?</p>
            <p class="panel-solicitud-sub">Ingresa tu origen y destino para solicitar tu mototaxi</p>

            <form action="{{ route('pasajero.crearViaje') }}" method="POST" id="formViaje">
                @csrf

                {{-- Ruta --}}
                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text" name="origen" id="origen-input" placeholder="Obteniendo ubicación..."
                            required readonly style="background:#f5f5f5">
                        <input type="hidden" name="origen_lat" id="origen-lat">
                        <input type="hidden" name="origen_lng" id="origen-lng">
                    </div>
                    <div class="ruta-fila">
                        <div class="punto punto-rojo"></div>
                        <input type="text" name="destino" id="destino-input" placeholder="¿A dónde vas?" required>
                    </div>
                </div>

                {{-- Tipo de servicio --}}
                <div class="campo-grupo">
                    <label class="campo-label">Tipo de servicio</label>
                    <div class="servicio-chips">
                        <label class="servicio-chip seleccionado">
                            <input type="radio" name="tipo_servicio" value="normal" checked>
                            <span class="servicio-chip-icono">🛺</span>
                            <span class="servicio-chip-nombre">Normal</span>
                            <span class="servicio-chip-precio">Desde S/ 3</span>
                        </label>
                        <label class="servicio-chip">
                            <input type="radio" name="tipo_servicio" value="express">
                            <span class="servicio-chip-icono">⚡</span>
                            <span class="servicio-chip-nombre">Express</span>
                            <span class="servicio-chip-precio">Desde S/ 5</span>
                        </label>
                    </div>
                </div>

                {{-- Método de pago --}}
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

                {{-- Tarifa estimada --}}
                <div class="tarifa-caja">
                    <div class="tarifa-numero" id="tarifa-numero">S/ 3.00</div>
                    <div class="tarifa-right">
                        <div class="tarifa-label">Tarifa estimada</div>
                        <div class="tarifa-detalle" id="tarifa-detalle">~0 km · 0 min</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-verde btn-ancho" id="btn-solicitar">
                    Solicitar mototaxi
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Librerías de Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* Estilos del mapa */
.mapa-decorativo {
    position: relative;
    width: 100%;
    height: 450px;
    min-height: 450px;
    border-radius: 20px;
    overflow: hidden;
    background: #e2e8f0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

#mapa-solicitud-pasajero {
    width: 100%;
    height: 100%;
    min-height: 450px;
    background: #e2e8f0;
}

.leaflet-container {
    width: 100%;
    height: 100%;
    background: #e2e8f0;
}

.mapa-etiqueta {
    position: absolute;
    bottom: 16px;
    left: 16px;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    color: white;
    padding: 8px 16px;
    border-radius: 24px;
    font-size: 13px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: system-ui;
    pointer-events: none;
}

.solicitar-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
    padding: 20px;
}

@media (max-width: 768px) {
    .solicitar-grid {
        grid-template-columns: 1fr;
        padding: 12px;
    }

    .mapa-decorativo {
        height: 350px;
        min-height: 350px;
    }

    #mapa-solicitud-pasajero {
        min-height: 350px;
    }
}
</style>

<script>
// INICIALIZAR MAPA Y OBTENER UBICACIÓN
document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Página cargada, iniciando...');

    // Verificar que el contenedor existe
    const contenedorMapa = document.getElementById('mapa-solicitud-pasajero');
    if (!contenedorMapa) {
        console.error('❌ No existe el elemento #mapa-solicitud-pasajero');
        return;
    }

    console.log('✅ Contenedor del mapa encontrado');
    console.log('📐 Alto:', contenedorMapa.offsetHeight);
    console.log('📏 Ancho:', contenedorMapa.offsetWidth);

    // Crear mapa
    const mapa = L.map('mapa-solicitud-pasajero').setView([-9.19, -75.0152], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    // Forzar actualización del tamaño
    setTimeout(() => {
        mapa.invalidateSize();
        console.log('🔄 Mapa actualizado, nuevo alto:', contenedorMapa.offsetHeight);
    }, 200);

    // Icono personalizado
    const iconoPasajero = L.divIcon({
        html: '<div style="font-size: 32px; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));">📍</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });

    // Marcador inicial
    const marcador = L.marker([-9.19, -75.0152], {
        icon: iconoPasajero
    }).addTo(mapa);
    const circulo = L.circle([-9.19, -75.0152], {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.1,
        radius: 100
    }).addTo(mapa);

    // OBTENER UBICACIÓN REAL
    const origenInput = document.getElementById('origen-input');
    const latInput = document.getElementById('origen-lat');
    const lngInput = document.getElementById('origen-lng');
    const ubicacionTexto = document.getElementById('ubicacion-texto');

    if (!origenInput) {
        console.error('❌ No se encontró el input de origen');
        return;
    }

    if (navigator.geolocation) {
        console.log('🌍 GPS disponible, solicitando ubicación...');
        ubicacionTexto.innerHTML = 'Solicitando permiso GPS...';
        origenInput.value = 'Obteniendo ubicación...';

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const precision = position.coords.accuracy;

                    console.log(`🎯 Ubicación obtenida: ${lat}, ${lng}`);
                    console.log(`📡 Precisión: ±${precision} metros`);

                    // Guardar coordenadas
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;

                    // Centrar mapa
                    mapa.setView([lat, lng], 17);
                    marcador.setLatLng([lat, lng]);
                    circulo.setLatLng([lat, lng]);
                    circulo.setRadius(Math.min(precision, 150));

                    // Obtener dirección
                    try {
                        ubicacionTexto.innerHTML = 'Obteniendo dirección...';
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`
                            );
                        const data = await response.json();

                        if (data.display_name) {
                            let direccion = data.display_name.split(',')[0];
                            if (data.address.road) {
                                direccion = data.address.road;
                                if (data.address.house_number) {
                                    direccion += ` ${data.address.house_number}`;
                                }
                            }
                            origenInput.value = direccion;
                            ubicacionTexto.innerHTML = direccion;
                            console.log(`📍 Dirección: ${direccion}`);
                        } else {
                            origenInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            ubicacionTexto.innerHTML = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        }
                    } catch (e) {
                        console.error('Error obteniendo dirección:', e);
                        origenInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        ubicacionTexto.innerHTML = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    }

                    setTimeout(() => mapa.invalidateSize(), 300);
                },
                function(error) {
                    console.error('❌ Error GPS:', error);
                    let mensaje = '';
                    switch (error.code) {
                        case 1:
                            mensaje = '❌ Permiso denegado. Activa el GPS en tu navegador.';
                            break;
                        case 2:
                            mensaje = '❌ Ubicación no disponible.';
                            break;
                        case 3:
                            mensaje = '❌ Tiempo de espera agotado.';
                            break;
                        default:
                            mensaje = '❌ Error desconocido.';
                    }
                    ubicacionTexto.innerHTML = mensaje;
                    origenInput.value = '';
                    origenInput.placeholder = 'Escribe tu ubicación manualmente';
                    origenInput.readOnly = false;
                    origenInput.style.background = 'white';
                    alert(mensaje);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
        );
    } else {
        console.error('❌ GPS no soportado');
        ubicacionTexto.innerHTML = 'GPS no soportado por tu navegador';
        origenInput.placeholder = 'Escribe tu ubicación manualmente';
        origenInput.readOnly = false;
        origenInput.style.background = 'white';
    }

    // Ajustar mapa al redimensionar
    window.addEventListener('resize', function() {
        setTimeout(() => mapa.invalidateSize(), 200);
    });
});

// Actualizar tarifa
const destinoInput = document.getElementById('destino-input');
const tarifaNumero = document.getElementById('tarifa-numero');
const tarifaDetalle = document.getElementById('tarifa-detalle');

if (destinoInput) {
    destinoInput.addEventListener('input', function() {
        if (destinoInput.value.length > 3) {
            const tipo = document.querySelector('input[name="tipo_servicio"]:checked').value;
            const base = tipo === 'normal' ? 3 : 5;
            const distancia = (Math.random() * 5 + 1).toFixed(1);
            const minutos = Math.floor(distancia * 2.5);
            const total = (base + (distancia * 0.5)).toFixed(2);
            tarifaNumero.textContent = `S/ ${total}`;
            tarifaDetalle.textContent = `~${distancia} km · ${minutos} min`;
        }
    });
}

// Chips de servicio
document.querySelectorAll('.servicio-chip input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.servicio-chip').forEach(c => c.classList.remove('seleccionado'));
        this.closest('.servicio-chip').classList.add('seleccionado');
        if (destinoInput && destinoInput.value.length > 3) {
            const base = this.value === 'normal' ? 3 : 5;
            const distancia = (Math.random() * 5 + 1).toFixed(1);
            const total = (base + (distancia * 0.5)).toFixed(2);
            tarifaNumero.textContent = `S/ ${total}`;
        } else {
            tarifaNumero.textContent = this.value === 'normal' ? 'S/ 3.00' : 'S/ 5.00';
        }
    });
});

// Opciones de pago
document.querySelectorAll('.pago-opcion input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.pago-opcion').forEach(o => o.classList.remove('activo'));
        this.closest('.pago-opcion').classList.add('activo');
    });
});
</script>

@endsection