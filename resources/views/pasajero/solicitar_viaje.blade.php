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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
 
<div class="pagina-pasajero">
 
    <div class="solicitar-grid">
 
        {{-- Mapa con Leaflet --}}
        <div class="mapa-decorativo">
            {{-- Contenedor donde se dibuja el mapa real --}}
            <div id="mapa-solicitud-pasajero"></div>
            
            {{-- Etiqueta flotante superior --}}
            <div class="mapa-etiqueta">
                📍 Bagua, Amazonas — Tu Ubicación
            </div>
        </div>

        {{-- Panel formulario --}}
        <div class="panel-solicitud">
            <p class="panel-solicitud-titulo">¿A dónde vamos?</p>
            <p class="panel-solicitud-sub">Ingresa tu origen y destino para solicitar tu mototaxi</p>
 
            <form action="{{ route('pasajero.crearViaje') }}" method="POST">
                @csrf
 
                {{-- Ruta --}}
                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text"
                               name="origen"
                               value="{{ old('origen') }}"
                               placeholder="¿Dónde estás?"
                               required
                               autocomplete="off">
                    </div>
                    <div class="ruta-fila">
                        <div class="punto punto-rojo"></div>
                        <input type="text"
                               name="destino"
                               value="{{ old('destino') }}"
                               placeholder="¿A dónde vas?"
                               required
                               autocomplete="off">
                    </div>
                </div>
 
                {{-- Tipo de servicio --}}
                <div class="campo-grupo">
                    <label class="campo-label">Tipo de servicio</label>
                    <div class="servicio-chips">
                        <label class="servicio-chip seleccionado" id="chip-normal">
                            <input type="radio" name="tipo_servicio" value="normal" checked>
                            <span class="servicio-chip-icono">🛺</span>
                            <span class="servicio-chip-nombre">Normal</span>
                            <span class="servicio-chip-precio">Desde S/ 3</span>
                        </label>
                        <label class="servicio-chip" id="chip-express">
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
                            <input type="radio" name="metodo_pago" value="efectivo" checked>
                            Efectivo
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="yape">
                            Yape
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="plin">
                            Plin
                        </label>
                    </div>
                </div>
 
                {{-- Tarifa estimada --}}
                <div class="tarifa-caja">
                    <div>
                        <div class="tarifa-numero">S/ 3.00</div>
                    </div>
                    <div class="tarifa-right">
                        <div class="tarifa-label">Tarifa estimada</div>
                        <div class="tarifa-detalle">~1.5 km · 5 min</div>
                    </div>
                </div>
 
                <button type="submit" class="btn btn-verde btn-ancho">
                    Solicitar mototaxi
                </button>
 
            </form>
        </div>
    </div>
</div>
 
<script>
    // ── CONFIGURACIÓN DEL MAPA REAL ──
    const LAT_BAGUA = -5.6763;
    const LNG_BAGUA = -78.5311;

    // Inicializar mapa centrado en Bagua
    const mapaSolicitud = L.map('mapa-solicitud-pasajero').setView([LAT_BAGUA, LNG_BAGUA], 15);

    // Cargar las calles de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapaSolicitud);

    setTimeout(() => {
        mapaSolicitud.invalidateSize();
    }, 300);

    // Crear marcador para la ubicación del pasajero (Verde)
    const marcadorPasajero = L.marker([LAT_BAGUA, LNG_BAGUA], {
        icon: L.divIcon({
            html: '<div style="font-size:30px; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));">📍</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        })
    }).addTo(mapaSolicitud).bindPopup('¿Estás aquí?');

    // Intentar obtener la ubicación real del GPS del Pasajero al cargar
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            const miLat = pos.coords.latitude;
            const miLng = pos.coords.longitude;
            
            // Mover el mapa y el marcador a su posición GPS real
            mapaSolicitud.setView([miLat, miLng], 16);
            marcadorPasajero.setLatLng([miLat, miLng]);
        }, () => {
            console.log("El pasajero denegó el GPS o hubo un error, usando Bagua por defecto.");
        });
    }

    // ── CHIPS DE SERVICIO (Tu código existente) ──
    document.querySelectorAll('.servicio-chip input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.servicio-chip').forEach(c => c.classList.remove('seleccionado'));
            radio.closest('.servicio-chip').classList.add('seleccionado');
        });
    });

    // ── OPCIONES DE PAGO (Tu código existente) ──
    document.querySelectorAll('.pago-opcion input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.pago-opcion').forEach(o => o.classList.remove('activo'));
            radio.closest('.pago-opcion').classList.add('activo');
        });
    });
</script>
 
@endsection