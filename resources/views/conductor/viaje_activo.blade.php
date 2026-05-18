@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <h1 class="titulo-pagina">Viaje en Curso</h1>
    <p class="subtitulo-pagina">Estás llevando al pasajero — mantén la página abierta</p>
 
    <div class="viaje-grid">
 
        {{-- Mapa decorativo --}}
        <div class="mapa-viaje">
            {{-- Contenedor donde Leaflet va a renderizar el mapa --}}
            <div id="mapa-leaflet-conductor" style="width:100%; height:100%; min-height: 350px; border-radius:16px;"></div>
            
            {{-- Caja flotante del ETA --}}
            <div class="eta-caja" style="position: absolute; top: 15px; left: 15px; z-index: 1000;">
                <div class="eta-numero">4</div>
                <div class="eta-unidad">min restantes</div>
            </div>
        </div>
    
        {{-- Cargamos las librerías de Leaflet (CSS y JS) --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
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
                <form method="POST" action="{{ route('conductor.cancelarViaje') }}"
                      style="margin-top:10px;"
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
                    <a href="{{ route('conductor.solicitudes') }}"
                       class="btn btn-verde"
                       style="margin-top:20px;">
                        Ver solicitudes
                    </a>
                </div>
            @endif
 
        </div>
    </div>
</div>

<script>
    @if($viaje)
        // 1. Coordenadas iniciales por defecto (Bagua)
        const LAT_INICIAL = -5.6763;
        const LNG_INICIAL = -78.5311;

        // 2. Inicializar el mapa en el contenedor del conductor
        const mapaConductor = L.map('mapa-leaflet-conductor').setView([LAT_INICIAL, LNG_INICIAL], 16);

        // 3. Cargar la capa de diseño de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaConductor);

        // 4. Crear el marcador de la moto que se va a mover
        let marcadorMoto = L.marker([LAT_INICIAL, LNG_INICIAL], {
            icon: L.divIcon({
                html: '<div style="font-size:28px; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));">🏍️</div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(mapaConductor).bindPopup('Tu ubicación actual');

        // Variables para controlar el envío de GPS al servidor sin saturarlo
        let ultimaLat = null;
        let ultimaLng = null;

        // 5. Rastrear el GPS en tiempo real
        navigator.geolocation.watchPosition((pos) => {
            ultimaLat = pos.coords.latitude;
            ultimaLng = pos.coords.longitude;

            // Mover el marcador de la moto en el mapa del conductor INMEDIATAMENTE
            const nuevaPos = [ultimaLat, ultimaLng];
            marcadorMoto.setLatLng(nuevaPos);
            mapaConductor.panTo(nuevaPos);

        }, null, { maximumAge: 0, timeout: 5000, enableHighAccuracy: true });

        // 6. Enviar la ubicación al backend de forma controlada cada 5 segundos
        setInterval(() => {
            if (ultimaLat && ultimaLng) {
                fetch('/conductor/ubicacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        viaje_id: {{ $viaje->id_viaje }},
                        lat: ultimaLat,
                        lng: ultimaLng
                    })
                }).catch(err => console.error("Error enviando coordenadas:", err));
            }
        }, 5000); 
    @endif
</script>

@endsection