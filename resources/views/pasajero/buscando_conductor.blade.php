@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA: Exactamente igual a solicitar_viaje --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🔍</span>
                <span id="ubicacion-texto">Buscando la mototaxi más cercana en Bagua...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in" title="Acercar">+</button>
                <button class="mapa-boton-zoom" id="zoom-out" title="Alejar">−</button>
            </div>
        </div>

        {{-- Panel derecho: Tu tarjeta original de buscando --}}
        <div class="buscando-centro" style="margin-top: 0;">

            <div class="icono-buscando">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M5 17H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11l4 4v4" />
                    <circle cx="7" cy="17" r="2" />
                    <circle cx="17" cy="17" r="2" />
                    <path d="M9 17h6" />
                </svg>
            </div>

            <h2 class="buscando-titulo">Buscando mototaxi cercano...</h2>
            <span class="buscando-tiempo">⏱ Menos de 2 minutos</span>
            <p class="buscando-desc">
                Te conectamos con el conductor más cercano disponible en Bagua
            </p>

            <div class="progreso-wrap">
                <div class="progreso-barra"></div>
            </div>

            <div class="tarjeta-viaje">
                <div class="fila-dato">
                    <span>Origen</span>
                    <strong>{{ $viaje['origen'] ?? '—' }}</strong>
                </div>
                <hr class="separador">
                <div class="fila-dato">
                    <span>Destino</span>
                    <strong>{{ $viaje['destino'] ?? '—' }}</strong>
                </div>
                <hr class="separador">
                <div class="fila-dato" style="margin:0;">
                    <span>Tarifa estimada</span>
                    <strong style="font-size:18px; color:var(--p-verde-mid); letter-spacing:-0.5px;">
                        S/ {{ number_format($viaje['tarifa'] ?? 3.00, 2) }}
                    </strong>
                </div>
            </div>

            <p class="nota-cancelar">Si cancelas ahora no se te cobrará nada.</p>

            <form method="POST" action="{{ route('pasajero.cancelarViaje') }}">
                @csrf
                <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                <button type="submit" class="btn btn-outline">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                    Cancelar solicitud
                </button>
            </form>

        </div>
    </div>
</div>

{{-- Leaflet CSS y JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let mapa;
let lineaRuta;

// Coordenadas dinámicas obtenidas del viaje creado
const origLat = parseFloat("{{ $viaje['origen_lat'] ?? -5.63889 }}");
const origLng = parseFloat("{{ $viaje['origen_lng'] ?? -78.5311 }}");
const destLat = parseFloat("{{ $viaje['destino_lat'] ?? -5.6800 }}");
const destLng = parseFloat("{{ $viaje['destino_lng'] ?? -78.5400 }}");

const viajeIdActual = "{{ $viaje['id'] ?? '' }}";

window.addEventListener('load', () => {
    mapa = L.map('mapa-solicitud-pasajero', {
        zoomControl: false
    }).setView([origLat, origLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);

    setTimeout(() => mapa.invalidateSize(), 200);

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    const iconoOrigen = L.divIcon({
        html: '<div style="font-size: 30px;">📍</div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    const iconoDestino = L.divIcon({
        html: '<div style="font-size: 30px;">🏁</div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    L.marker([origLat, origLng], {
            icon: iconoOrigen
        })
        .addTo(mapa)
        .bindPopup('Tu origen');

    L.marker([destLat, destLng], {
            icon: iconoDestino
        })
        .addTo(mapa)
        .bindPopup('Tu destino');

    fetch(
            `https://router.project-osrm.org/route/v1/driving/${origLng},${origLat};${destLng},${destLat}?overview=full&geometries=geojson`)
        .then(res => res.json())
        .then(data => {
            if (!data.routes || !data.routes.length) return;

            const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);

            lineaRuta = L.polyline(coords, {
                color: '#16a34a',
                weight: 6,
                opacity: 0.9
            }).addTo(mapa);

            mapa.fitBounds(lineaRuta.getBounds(), {
                padding: [50, 50]
            });
        });

    /*
    | ESCUCHA EN TIEMPO REAL
    */
    if (window.Echo) {

        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('.ViajeAceptado', (data) => {
                const viajeId = data.viajeId || viajeIdActual;
                if (!viajeId) return;

                console.log("Conductor asignado:", data);

                window.location.href = `/pasajero/enCurso/${viajeId}`;
            });

        window.Echo.private(`pasajero.{{ auth()->id() }}`)
            .listen('.ViajeActualizado', (data) => {

                if (!data.estado) return;

                console.log("Estado viaje:", data.estado);

            });
    }
});
</script>

@endsection
