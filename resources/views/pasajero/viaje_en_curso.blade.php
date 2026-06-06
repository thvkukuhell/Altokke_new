@extends('layouts.main')
@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA --}}
        <div class="mapa-decorativo">
            <div id="mapa-en-curso"></div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🛺</span>
                <span id="estado-texto">Conductor en camino...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in">+</button>
                <button class="mapa-boton-zoom" id="zoom-out">−</button>
            </div>
        </div>

        {{-- PANEL DERECHO --}}
        <div class="panel-solicitud" style="display:flex; flex-direction:column; gap:16px;">

            {{-- Conductor --}}
            <div>
                <p class="panel-solicitud-titulo">Tu conductor</p>
                <p class="panel-solicitud-sub">Sigue el trayecto en el mapa</p>

                <div class="conductor-card-encurso">
                    <div class="avatar-conductor">{{ $iniciales ?? '—' }}</div>
                    <div class="conductor-info">
                        <div class="conductor-nombre-encurso">{{ $conductor['nombre'] ?? '—' }}</div>
                        <div class="conductor-dato-encurso">
                            ★ {{ number_format($conductor['calificacion'] ?? 0, 1) }}
                            &nbsp;·&nbsp; {{ $conductor['modelo'] ?? '—' }}
                        </div>
                    </div>
                    <div class="placa-encurso">{{ $conductor['placa'] ?? '—' }}</div>
                </div>
            </div>

            {{-- Datos del viaje --}}
            <div class="ruta-selector" style="margin-bottom:0;">
                <div class="ruta-fila">
                    <div class="punto punto-verde"></div>
                    <span style="font-size:13px; color:var(--text);">{{ $viaje['origen'] ?? '—' }}</span>
                </div>
                <div class="ruta-fila">
                    <div class="punto punto-rojo"></div>
                    <span style="font-size:13px; color:var(--text);">{{ $viaje['destino'] ?? '—' }}</span>
                </div>
            </div>

            {{-- Tarifa --}}
            <div class="tarifa-caja">
                <div class="tarifa-numero">S/ {{ number_format($viaje['tarifa'] ?? 0, 2) }}</div>
                <div class="tarifa-right">
                    <div class="tarifa-label">Tarifa estimada</div>
                    <div class="tarifa-detalle">{{ ucfirst($viaje['metodo_pago'] ?? 'efectivo') }}</div>
                </div>
            </div>

            {{-- Timeline de pasos --}}
            <div class="tarjeta-viaje">
                <p class="campo-label" style="margin-bottom:14px;">Estado del viaje</p>
                <div class="timeline" id="timeline-pasos">
                    @foreach($pasos as $i => $paso)
                        <div class="paso {{ $paso['estado'] }}" id="paso-{{ $i }}">
                            <div class="paso-icono">
                                @if($paso['estado'] === 'hecho')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div>
                                <div class="paso-titulo">{{ $paso['titulo'] }}</div>
                                <div class="paso-sub">{{ $paso['sub'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Botón calificar — oculto hasta que el viaje se complete --}}
            <div id="seccion-calificar" style="display:none;">
                <a href="{{ route('pasajero.calificar', $viaje['id'] ?? 0) }}"
                   class="btn btn-verde btn-ancho"
                   style="font-size:15px; padding:14px;">
                    ⭐ Calificar conductor
                </a>
            </div>

            {{-- Cancelar --}}
            <form method="POST" action="{{ route('pasajero.cancelarViaje') }}"
                  id="form-cancelar"
                  onsubmit="return confirm('¿Cancelar el viaje?')">
                @csrf
                <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                <button type="submit" class="btn btn-outline btn-ancho">✕ Cancelar viaje</button>
            </form>

        </div>
    </div>
</div>

<script>
const VIAJE_ID   = {{ $viaje['id'] ?? 0 }};
const origLat    = {{ $viaje['origen_lat']  ?? -5.63889 }};
const origLng    = {{ $viaje['origen_lng']  ?? -78.5311 }};
const destLat    = {{ $viaje['destino_lat'] ?? -5.6800  }};
const destLng    = {{ $viaje['destino_lng'] ?? -78.5400 }};
const condLat    = {{ $conductor['lat']     ?? -5.63889 }};
const condLng    = {{ $conductor['lng']     ?? -78.5311 }};

const ORDEN_PASOS = { aceptado: 0, recogiendo: 1, en_curso: 2, completado: 3 };

document.addEventListener('DOMContentLoaded', () => {
    if (!VIAJE_ID) return;

    // MAPA 
    const mapa = L.map('mapa-en-curso', { zoomControl: false }).setView([origLat, origLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
    setTimeout(() => mapa.invalidateSize(), 250);

    document.getElementById('zoom-in')?.addEventListener('click',  () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    // Marcador pasajero
    L.marker([origLat, origLng], {
        icon: L.divIcon({ html: '<div style="font-size:26px">📍</div>', iconSize:[30,30], iconAnchor:[15,30] })
    }).addTo(mapa).bindPopup('Tu ubicación');

    // Marcador destino
    L.marker([destLat, destLng], {
        icon: L.divIcon({ html: '<div style="font-size:26px">🏁</div>', iconSize:[30,30], iconAnchor:[15,30] })
    }).addTo(mapa).bindPopup('Tu destino');

    // Ruta fija origen -> destino
    fetch(`https://router.project-osrm.org/route/v1/driving/${origLng},${origLat};${destLng},${destLat}?overview=full&geometries=geojson`)
        .then(r => r.json())
        .then(data => {
            if (!data.routes?.length) return;
            const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
            L.polyline(coords, { color: '#16a34a', weight: 5, opacity: 0.7, dashArray: '8 5' }).addTo(mapa);
        });

    // Marcador conductor dinámico
    const marcadorConductor = L.marker([condLat, condLng], {
        icon: L.divIcon({ html: '<div style="font-size:28px">🏍️</div>', iconSize:[35,35], iconAnchor:[17,17] })
    }).addTo(mapa).bindPopup('Tu conductor');

    // ACTUALIZAR TIMELINE 
    function actualizarPasos(nuevoEstado) {
        const posNueva = ORDEN_PASOS[nuevoEstado] ?? 0;

        document.querySelectorAll('.paso').forEach((el, i) => {
            if (i < posNueva) {
                el.className = 'paso hecho';
            } else if (i === posNueva) {
                el.className = 'paso activo';
            } else {
                el.className = 'paso';
            }
        });

        // Cambiar dinámicamente el texto flotante de la etiqueta del mapa
        const textos = {
            aceptado: 'Conductor asignado y en camino...',
            recogiendo: 'El conductor está llegando a tu punto de origen...',
            en_curso: '¡Viaje en curso! Te diriges a tu destino...',
            completado: '¡Has llegado a tu destino!'
        };
        const txtLabel = document.getElementById('estado-texto');
        if (txtLabel && textos[nuevoEstado]) txtLabel.innerText = textos[nuevoEstado];
    }

    // ESCUCHA WEBSOCKETS (ECHO) 
    if (window.Echo) {
        // 1. Escuchar la ubicación en tiempo real de la moto
        window.Echo.private(`viaje.${VIAJE_ID}`)
            .listen('.UbicacionConductorActualizada', (data) => {
                if (data.lat && data.lng) {
                    const nuevaPos = [parseFloat(data.lat), parseFloat(data.lng)];
                    marcadorConductor.setLatLng(nuevaPos);
                }
            });

        // 2. Escuchar cambios de estado del viaje (Recogiendo, En curso, Completado)
        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('.ViajeActualizado', (data) => {
                if (!data.estado) return;
                
                actualizarPasos(data.estado);

                if (data.estado === 'completado') {
                    document.getElementById('seccion-calificar').style.display = 'block';
                    document.getElementById('form-cancelar').style.display = 'none';
                }
            });
    }
});
</script>

@endsection