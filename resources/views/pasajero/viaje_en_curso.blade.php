@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <h1 class="titulo-pagina">Viaje en curso</h1>
    <p class="subtitulo-pagina">Estás en camino — mantén la página abierta</p>
 
    <div class="viaje-grid">
 
        {{-- ── Mapa ── --}}
        <div class="mapa-viaje">
            <div id="mapa-leaflet" style="width:100%; height:100%; border-radius:16px;"></div>

            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

            <script>
                const mapa = L.map('mapa-leaflet').setView([-5.6763, -78.5311], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);

                // Marcador del conductor — empieza en Bagua
                let marcadorConductor = L.marker([-5.6763, -78.5311], {
                    icon: L.divIcon({
                        html: '<div style="font-size:28px;">🏍️</div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                }).addTo(mapa).bindPopup('Tu conductor en camino');

                // Escuchar movimiento del conductor en tiempo real
                window.Echo.channel(`viaje.{{ $viaje['id'] }}`)
                    .listen('ConductorMovido', (data) => {
                        const nuevaPos = [data.lat, data.lng];
                        marcadorConductor.setLatLng(nuevaPos);
                        mapa.panTo(nuevaPos);
                    });
            </script>
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
                        S/ {{ $viaje['tarifa'] ?? '0.00' }}
                    </strong>
                </div>
                <div class="fila-dato" style="margin:0;">
                    <span>Pago</span>
                    <strong>{{ ucfirst($viaje['metodo_pago'] ?? 'efectivo') }}</strong>
                </div>
            </div>
 
            {{-- Timeline --}}
            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:14px;">Estado del viaje</p>
                <div class="timeline">
                    @foreach($pasos ?? [] as $i => $paso)
                        <div class="paso {{ $paso['estado'] }}">
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
 
                <hr class="separador">
 
                <form action="{{ route('pasajero.cancelarViaje') }}"
                      method="POST"
                      onsubmit="return confirm('¿Seguro que quieres cancelar el viaje?')">
                    @csrf
                    <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                    <button type="submit" class="btn btn-rojo btn-ancho">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                        Cancelar viaje
                    </button>
                </form>
            </div>
 
        </div>
    </div>
</div>
 
@endsection