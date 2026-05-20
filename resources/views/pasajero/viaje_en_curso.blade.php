@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <h1 class="titulo-pagina">Viaje en curso</h1>
    <p class="subtitulo-pagina">Estás en camino — mantén la página abierta</p>
 
    <div class="solicitar-grid">
 
        {{-- ── MAPA ── --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🛺</span>
                <span id="ubicacion-texto">Monitoreando tu trayecto en tiempo real...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in" title="Acercar">+</button>
                <button class="mapa-boton-zoom" id="zoom-out" title="Alejar">−</button>
            </div>
        </div>
 
        {{-- ── Panel lateral ── --}}
        <div class="panel-viaje">
 
            {{-- Conductor --}}
            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:14px;">Tu conductor</p>
                <div class="conductor-fila">
                    <div class="avatar">{{ $iniciales ?? '—' }}</div>
                    <div style="flex:1;">
                        <div class="conductor-nombre">{{ $conductor['nombre'] ?? '—' }}</div>
                        <div class="conductor-dato">
                            ★ {{ number_format($conductor['calificacion'] ?? 0, 1) }}
                            · {{ $conductor['modelo'] ?? '—' }}
                        </div>
                    </div>
                    <div class="placa">{{ $conductor['placa'] ?? '—' }}</div>
                </div>
 
                <hr class="separador">
 
                <div class="fila-dato">
                    <span>Origen</span>
                    <strong>{{ $viaje['origen'] ?? '—' }}</strong>
                </div>
                <div class="fila-dato">
                    <span>Destino</span>
                    <strong>{{ $viaje['destino'] ?? '—' }}</strong>
                </div>
                <div class="fila-dato">
                    <span>Tarifa</span>
                    <strong style="color:var(--p-verde-mid); font-size:17px; letter-spacing:-0.5px;">
                        S/ {{ number_format($viaje['tarifa'] ?? 0, 2) }}
                    </strong>
                </div>
                <div class="fila-dato" style="margin:0;">
                    <span>Pago</span>
                    <strong>{{ ucfirst($viaje['metodo_pago'] ?? 'efectivo') }}</strong>
                </div>
            </div>
 
            {{-- Timeline (Pasos del viaje) --}}
            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:14px;">Estado del viaje</p>
                <div class="timeline">
                    @foreach($pasos ?? [] as $i => $paso)
                        <div class="paso {{ $paso['estado'] }}" id="contenedor-paso-{{ $i + 1 }}">
                            <div class="paso-icono">
                                <span class="icono-contenido">
                                    @if($paso['estado'] === 'hecho')
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <path d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <div class="paso-titulo">{{ $paso['titulo'] }}</div>
                                <div class="paso-sub">{{ $paso['sub'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
 
                <hr class="separador">
 
                {{-- SECCIÓN DE ACCIONES DIRECTA SIN RESTRICCIONES --}}
                <div style="width: 100%; display: flex; flex-direction: column; gap: 10px;">
                    
                    {{-- BOTÓN CALIFICAR FIJO SIN ARROBA IF --}}
                    <a id="boton-calificar-real" 
                       href="/pasajero/calificar/{{ $viaje['id'] ?? $viaje['id_viaje'] ?? 0 }}" 
                       class="btn" 
                       style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background-color: #10b981; color: white; padding: 14px; border-radius: 8px; font-weight: bold; text-decoration: none; border: none; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Calificar servicio del conductor
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Librerías indispensables de Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let mapa;
let marcadorOrigen = null;
let marcadorDestino = null;
let marcadorConductor = null;
let lineaRuta = null;

document.addEventListener('DOMContentLoaded', () => {
    
    const LAT_BAGUA = -5.6763;
    const LNG_BAGUA = -78.5311;
    
    const VIAJE_ID = "{{ $viaje['id'] ?? $viaje['id_viaje'] ?? 0 }}";
    
    const origLat = parseFloat("{{ $viaje['origen_lat'] ?? 0 }}") || LAT_BAGUA;
    const origLng = parseFloat("{{ $viaje['origen_lng'] ?? 0 }}") || LNG_BAGUA;
    const destLat = parseFloat("{{ $viaje['destino_lat'] ?? 0 }}") || null;
    const destLng = parseFloat("{{ $viaje['destino_lng'] ?? 0 }}") || null;

    // Inicialización del mapa
    mapa = L.map('mapa-solicitud-pasajero', { zoomControl: false }).setView([origLat, origLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapa);
    setTimeout(() => { mapa.invalidateSize(); }, 250);

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    // Marcadores base
    marcadorOrigen = L.marker([origLat, origLng]).addTo(mapa);
    
    if (destLat && destLng) {
        marcadorDestino = L.marker([destLat, destLng]).addTo(mapa);
        
        fetch(`https://router.project-osrm.org/route/v1/driving/${origLng},${origLat};${destLng},${destLat}?overview=full&geometries=geojson`)
            .then(res => res.json())
            .then(data => {
                if (data.routes && data.routes.length) {
                    const coordenadas = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    lineaRuta = L.polyline(coordenadas, { color: '#16a34a', weight: 6, opacity: 0.9 }).addTo(mapa);
                    mapa.fitBounds(lineaRuta.getBounds(), { padding: [50, 50] });
                }
            });
    }

    marcadorConductor = L.marker([origLat, origLng], {
        icon: L.divIcon({
            html: '<div style="font-size:32px;">🛺</div>',
            iconSize: [35, 35],
            iconAnchor: [17, 17]
        })
    }).addTo(mapa);

    // ==========================================
    // ESCUCHAR EVENTOS EN TIEMPO REAL (Echo)
    // ==========================================
    if (window.Echo && VIAJE_ID > 0) {
        
        window.Echo.channel(`viaje.${VIAJE_ID}`)
            .listen('ConductorMovido', (data) => {
                if (data.lat && data.lng) {
                    marcadorConductor.setLatLng([data.lat, data.lng]);
                }
            });

        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('ViajeActualizado', (data) => {
                if (data.estado === 'completado' || data.estado === 'finalizado') {
                    
                    // Al completarse el viaje, pintamos todos los pasos de verde ("hecho")
                    for (let pasoNum = 1; pasoNum <= 4; pasoNum++) {
                        const pasoDiv = document.getElementById(`contenedor-paso-${pasoNum}`);
                        if (pasoDiv) {
                            pasoDiv.className = 'paso hecho';
                            const spanIcono = pasoDiv.querySelector('.icono-contenido');
                            if (spanIcono) {
                                spanIcono.innerHTML = `
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                `;
                            }
                        }
                    }
                }
            });
    }
});
</script>

@endsection