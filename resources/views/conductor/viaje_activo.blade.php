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
                    <strong style="color:var(--p-verde-dark); font-size:17px;">
                        S/ {{ number_format($viaje->tarifa_final ?? $viaje->tarifa_estimada, 2) }}
                    </strong>
                </div>
                <div class="fila-dato" style="margin:0;">
                    <span>Estado</span>
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

document.addEventListener('DOMContentLoaded', () => {
    if (!VIAJE_ID) return;

    // Coordenadas base de Bagua
    let ultimaLat = -5.6763;
    let ultimaLng = -78.5311;

    // Inicializar mapa del conductor
    const mapaConductor = L.map('mapa-leaflet-conductor').setView([ultimaLat, ultimaLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapaConductor);

    // Marcador visual para que el conductor vea su propia posición
    const iconoMoto = L.divIcon({
        html: '<div style="font-size:28px;">🏍️</div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    const marcadorConductor = L.marker([ultimaLat, ultimaLng], { icon: iconoMoto }).addTo(mapaConductor);

    // Función unificada para enviar las coordenadas al servidor mediante Fetch
    function emitirUbicacion(latitud, longitud) {
        fetch('/conductor/ubicacion', {
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

    // DISPOSITIVO REAL EN CALLE vs SIMULADOR LOCAL EN PC
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log("Detectado entorno Localhost. Activando simulador de movimiento automático...");
        
        // Simula avance cada 4 segundos hacia el noreste de Bagua
        setInterval(() => {
            ultimaLat += 0.00012;
            ultimaLng += 0.00012;

            const nuevaPos = [ultimaLat, ultimaLng];
            marcadorConductor.setLatLng(nuevaPos);
            mapaConductor.panTo(nuevaPos);

            emitirUbicacion(ultimaLat, ultimaLng);
        }, 4000);

    } else {
        console.log("Detectado dispositivo real. Activando GPS integrado...");

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition((pos) => {
                ultimaLat = pos.coords.latitude;
                ultimaLng = pos.coords.longitude;

                const nuevaPos = [ultimaLat, ultimaLng];
                marcadorConductor.setLatLng(nuevaPos);
                mapaConductor.panTo(nuevaPos);
            }, (err) => {
                console.warn('Error capturando GPS del dispositivo:', err.message);
            }, {
                enableHighAccuracy: true,
                maximumAge: 0
            });

            // Enviar la posición real al servidor periódicamente cada 4 segundos
            setInterval(() => {
                emitirUbicacion(ultimaLat, ultimaLng);
            }, 4000);
        } else {
            console.error("El navegador no soporta geolocalización.");
        }
    }
});
</script>

@endsection