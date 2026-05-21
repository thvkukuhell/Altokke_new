@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <h1 class="titulo-pagina">Viaje en Curso</h1>
    <p class="subtitulo-pagina">Estás llevando al pasajero — mantén la página abierta</p>

    <div class="viaje-grid">

        {{-- Mapa decorativo --}}
        <div class="mapa-viaje">
            {{-- Contenedor donde Leaflet va a renderizar el mapa --}}
            <div id="mapa-leaflet-conductor" style="width:100%; height:100%; min-height: 350px; border-radius:16px;">
            </div>

            {{-- Caja flotante del ETA --}}
            <div class="eta-caja" style="position: absolute; top: 15px; left: 15px; z-index: 1000;">
                <div class="eta-numero">4</div>
                <div class="eta-unidad">min restantes</div>
            </div>
        </div>

        {{-- Cargamos las librerías de Leaflet (CSS y JS) --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        {{-- Panel derecho --}}
        <div class="panel-viaje">

            @if(session('mensaje'))
            <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            @if($viaje)

            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:12px;">Pasajero</p>
                <div class="conductor-fila">
                    <div class="avatar">
                        {{-- Iniciales del pasajero --}}
                        {{ strtoupper(substr($viaje->pasajero->user->nombre_completo ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div class="conductor-nombre">
                            {{ $viaje->pasajero->user->nombre_completo ?? 'Pasajero' }}
                        </div>
                        <div class="conductor-dato">
                            📱 {{ $viaje->pasajero->user->telefono ?? '—' }}
                        </div>
                    </div>
                </div>

                <hr class="separador">

                <div class="fila-dato">
                    <span>Origen</span>
                    <strong>{{ $viaje->origen_texto ?? '—' }}</strong>
                </div>
                <div class="fila-dato">
                    <span>Destino</span>
                    <strong>{{ $viaje->destino_texto ?? '—' }}</strong>
                </div>
                <div class="fila-dato">
                    <span>Tarifa</span>
                    @php
                        $tarifaVista = $viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0;
                        $tarifaLabel = $viaje->tarifa_final ? 'Tarifa final' : 'Tarifa estimada';
                    @endphp
                    <strong style="color:var(--p-verde-dark); font-size:17px;">
                        S/ {{ number_format($tarifaVista, 2) }}
                    </strong>
                </div>
                <div class="fila-dato" style="margin:0;">
                    <span>{{ $tarifaLabel }}</span>
                    <strong>{{ ucfirst($viaje->estado_viaje ?? 'en_curso') }}</strong>
                </div>
            </div>

            {{-- Completar viaje --}}
            <form method="POST" action="{{ route('conductor.completarViaje') }}" style="margin-top:16px;">
                @csrf
                <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                <button type="submit" class="btn btn-verde btn-ancho">
                    ✅ Completar Viaje
                </button>
            </form>

            {{-- Cancelar viaje --}}
            <form method="POST" action="{{ route('conductor.cancelarViaje') }}" style="margin-top:10px;"
                onsubmit="return confirm('¿Seguro que quieres cancelar este viaje?')">
                @csrf
                <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                <button type="submit" class="btn btn-rojo btn-ancho">
                    ✕ Cancelar viaje
                </button>
            </form>

            @else
            <div class="tarjeta" style="text-align:center; padding:48px 24px;">
                <div style="font-size:48px; margin-bottom:16px;">⏳</div>
                <h2 style="font-family:var(--font-display); margin-bottom:8px;">En espera</h2>
                <p style="color:var(--gray);">
                    Aún no hay un viaje activo. Aquí aparecerá cuando se asigne.
                </p>
                <a href="{{ route('conductor.solicitudes') }}" class="btn btn-verde" style="margin-top:20px;">
                    Ver solicitudes
                </a>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
const VIAJE_ID = @json($viaje->id_viaje ?? null);

const PASAJERO_ORIG_LAT = parseFloat("{{ $viaje->lat_origen ?? -5.63889 }}");
const PASAJERO_ORIG_LNG = parseFloat("{{ $viaje->lng_origen ?? -78.5311 }}");
const DESTINO_LAT      = parseFloat("{{ $viaje->lat_destino ?? -5.6800 }}");
const DESTINO_LNG      = parseFloat("{{ $viaje->lng_destino ?? -78.5400 }}");

window.addEventListener('load', () => {
    if (!VIAJE_ID) return;

    const safeNumber = (value, fallback) => {
        const n = Number(value);
        return Number.isFinite(n) ? n : fallback;
    };

    let conductorLat = safeNumber("{{ $viaje->conductor->lat ?? -5.6763 }}", -5.6763);
    let conductorLng = safeNumber("{{ $viaje->conductor->lng ?? -78.5311 }}", -78.5311);
    const origenLat    = safeNumber("{{ $viaje->lat_origen ?? -5.63889 }}", -5.63889);
    const origenLng    = safeNumber("{{ $viaje->lng_origen ?? -78.5311 }}", -78.5311);
    const destinoLat   = safeNumber("{{ $viaje->lat_destino ?? -5.6800 }}", -5.6800);
    const destinoLng   = safeNumber("{{ $viaje->lng_destino ?? -78.5400 }}", -78.5400);

    const mapaConductor = L.map('mapa-leaflet-conductor').setView([conductorLat, conductorLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapaConductor);

    const iconoMoto = L.divIcon({
        html: '<div style="font-size:28px;">🏍️</div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    const marcadorConductor = L.marker([conductorLat, conductorLng], { icon: iconoMoto }).addTo(mapaConductor);

    const marcadorPasajero = L.marker([origenLat, origenLng], {
        icon: L.divIcon({ html: '<div style="font-size:26px">📍</div>', iconSize: [30, 30], iconAnchor: [15, 30] })
    }).addTo(mapaConductor).bindPopup('Pasajero');

    const marcadorDestino = L.marker([destinoLat, destinoLng], {
        icon: L.divIcon({ html: '<div style="font-size:26px">🏁</div>', iconSize: [30, 30], iconAnchor: [15, 30] })
    }).addTo(mapaConductor).bindPopup('Destino');

    const bounds = L.latLngBounds([
        [conductorLat, conductorLng],
        [origenLat, origenLng],
        [destinoLat, destinoLng]
    ]);
    mapaConductor.fitBounds(bounds.pad(0.30));

    setTimeout(() => mapaConductor.invalidateSize(), 500);

    fetch(`https://router.project-osrm.org/route/v1/driving/${origenLng},${origenLat};${destinoLng},${destinoLat}?overview=full&geometries=geojson`)
        .then(res => res.json())
        .then(data => {
            if (!data.routes?.length) return;
            const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
            L.polyline(coords, { color: '#16a34a', weight: 5, opacity: 0.7, dashArray: '8 5' }).addTo(mapaConductor);
        })
        .catch(err => console.warn('Error dibujando ruta:', err));

    function emitirUbicacion(latitud, longitud) {
        fetch('{{ route('conductor.ubicacion') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                viaje_id: VIAJE_ID,
                lat: latitud,
                lng: longitud
            })
        })
        .then(res => res.json())
        .then(data => console.log('Transmisión Reverb exitosa:', data))
        .catch(err => console.error('Error de red en actualización:', err));
    }

    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        setInterval(() => {
            conductorLat += 0.00012;
            conductorLng += 0.00012;

            marcadorConductor.setLatLng([conductorLat, conductorLng]);
            mapaConductor.panTo([conductorLat, conductorLng]);
            emitirUbicacion(conductorLat, conductorLng);
        }, 4000);
    } else {
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition((pos) => {
                const nuevaLat = pos.coords.latitude;
                const nuevaLng = pos.coords.longitude;

                marcadorConductor.setLatLng([nuevaLat, nuevaLng]);
                mapaConductor.panTo([nuevaLat, nuevaLng]);
                emitirUbicacion(nuevaLat, nuevaLng);
            }, (err) => {
                console.warn('Error capturando GPS del dispositivo:', err.message);
            }, {
                enableHighAccuracy: true,
                maximumAge: 0
            });
        } else {
            console.error('El navegador no soporta geolocalización.');
        }
    }
});
</script>

@endsection