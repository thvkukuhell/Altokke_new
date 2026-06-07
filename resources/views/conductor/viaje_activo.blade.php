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
                <div class="eta-numero">GPS</div>
                <div class="eta-unidad">activo</div>
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
                            Tel: {{ $viaje->pasajero->user->telefono ?? '—' }}
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
                     Completar Viaje
                </button>
            </form>

            {{-- Cancelar viaje --}}
            @if($viaje->metodo_pago === 'efectivo')
                <div class="tarjeta" style="background:#f0fdf4; border:1px solid #bbf7d0; padding:16px; margin-top:16px;">
                    <p style="margin:0 0 4px; font-size:12px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.5px;">
                        💵 Pago en efectivo
                    </p>
                    <p style="font-size:24px; font-weight:900; color:#15803d; margin:4px 0 12px; letter-spacing:-0.5px;">
                        S/ {{ number_format($tarifaVista, 2) }}
                    </p>
                    <p style="font-size:12.5px; color:#166534; margin:0 0 14px;">
                        Cobra el monto al pasajero antes de confirmar.
                    </p>
                    <form method="POST" action="{{ route('conductor.completarViaje') }}">
                        @csrf
                        <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                        <button type="submit" class="btn btn-verde btn-ancho">
                            ✓ Confirmar pago en efectivo recibido
                        </button>
                    </form>
                </div>

            @elseif($viaje->metodo_pago === 'yape')
                <div class="tarjeta" style="background:#faf5ff; border:1px solid #ddd6fe; padding:16px; margin-top:16px;">
                    <p style="margin:0 0 4px; font-size:12px; font-weight:700; color:#6d28d9; text-transform:uppercase; letter-spacing:0.5px;">
                        💜 Pago por Yape
                    </p>
                    <p style="font-size:24px; font-weight:900; color:#7c3aed; margin:4px 0 6px; letter-spacing:-0.5px;">
                        S/ {{ number_format($tarifaVista, 2) }}
                    </p>
                    <p style="font-size:12.5px; color:#6d28d9; margin:0 0 4px;">
                        Pídele al pasajero que yapee a tu número:
                    </p>
                    <p style="font-size:16px; font-weight:800; color:#5b21b6; margin:0 0 14px;">
                        📱 {{ $conductor->user->telefono ?? '—' }}
                    </p>
                    <form method="POST" action="{{ route('conductor.completarViaje') }}">
                        @csrf
                        <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                        <button type="submit" class="btn btn-ancho" style="background:#7c3aed; color:#fff;">
                            ✓ Confirmar Yape recibido
                        </button>
                    </form>
                </div>

            @elseif($viaje->metodo_pago === 'plin')
                <div class="tarjeta" style="background:#eff6ff; border:1px solid #bfdbfe; padding:16px; margin-top:16px;">
                    <p style="margin:0 0 4px; font-size:12px; font-weight:700; color:#1d4ed8; text-transform:uppercase; letter-spacing:0.5px;">
                        💙 Pago por Plin
                    </p>
                    <p style="font-size:24px; font-weight:900; color:#2563eb; margin:4px 0 6px; letter-spacing:-0.5px;">
                        S/ {{ number_format($tarifaVista, 2) }}
                    </p>
                    <p style="font-size:12.5px; color:#1d4ed8; margin:0 0 4px;">
                        Pídele al pasajero que plinee a tu número:
                    </p>
                    <p style="font-size:16px; font-weight:800; color:#1e40af; margin:0 0 14px;">
                        📱 {{ $conductor->user->telefono ?? '—' }}
                    </p>
                    <form method="POST" action="{{ route('conductor.completarViaje') }}">
                        @csrf
                        <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                        <button type="submit" class="btn btn-ancho" style="background:#2563eb; color:#fff;">
                            ✓ Confirmar Plin recibido
                        </button>
                    </form>
                </div>
            @endif

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

    const marcadorConductor = L.marker([conductorLat, conductorLng]).addTo(mapaConductor).bindPopup('Conductor');
    const marcadorPasajero = L.marker([origenLat, origenLng]).addTo(mapaConductor).bindPopup('Pasajero');
    const marcadorDestino = L.marker([destinoLat, destinoLng]).addTo(mapaConductor).bindPopup('Destino');

    mapaConductor.fitBounds(L.latLngBounds([
        [conductorLat, conductorLng],
        [origenLat, origenLng],
        [destinoLat, destinoLng]
    ]).pad(0.30));

    setTimeout(() => mapaConductor.invalidateSize(), 500);

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
        }).catch(err => console.error('Error enviando ubicacion:', err));
    }

    let simulacionActiva = false;
    let simulacionTimer = null;

    function moverConductor(latitud, longitud) {
        conductorLat = latitud;
        conductorLng = longitud;
        marcadorConductor.setLatLng([conductorLat, conductorLng]);
        mapaConductor.panTo([conductorLat, conductorLng]);
        emitirUbicacion(conductorLat, conductorLng);
    }

    function iniciarSimulacionLocal() {
        if (simulacionActiva) return;
        simulacionActiva = true;
        simulacionTimer = setInterval(() => {
            moverConductor(conductorLat + 0.00012, conductorLng + 0.00012);
        }, 4000);
    }

    if (navigator.geolocation) {
        navigator.geolocation.watchPosition((pos) => {
            if (simulacionTimer) clearInterval(simulacionTimer);
            simulacionActiva = false;
            moverConductor(pos.coords.latitude, pos.coords.longitude);
        }, (err) => {
            console.warn('No se pudo obtener GPS real:', err.message);
            iniciarSimulacionLocal();
        }, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        });
    } else {
        iniciarSimulacionLocal();
    }
});
</script>

@endsection