@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <h1 class="titulo-pagina">Viaje en curso</h1>
    <p class="subtitulo-pagina">Estás en camino — mantén la página abierta</p>
 
    <div class="viaje-grid">
 
        {{-- ── Mapa ── --}}
        <div class="mapa-viaje">
            <div id="mapa-leaflet" style="width:100%; height:100%; min-height: 350px; border-radius:16px;"></div>

            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Coordenadas iniciales (Bagua)
                    const LAT_BAGUA = -5.6763;
                    const LNG_BAGUA = -78.5311;
                    const VIAJE_ID = "{{ $viaje['id'] ?? 0 }}";

                    // Inicializar Mapa
                    const mapa = L.map('mapa-leaflet').setView([LAT_BAGUA, LNG_BAGUA], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(mapa);

                    // Marcador del conductor
                    let marcadorConductor = L.marker([LAT_BAGUA, LNG_BAGUA], {
                        icon: L.divIcon({
                            html: '<div style="font-size:28px; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));">🏍️</div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        })
                    }).addTo(mapa).bindPopup('<strong>Tu conductor</strong><br>En camino').openPopup();

                    // 1. ESCUCHAR MOVIMIENTO DEL CONDUCTOR (Canal Público del Viaje)
                    if (window.Echo && VIAJE_ID > 0) {
                        console.log(`Escuchando movimiento en canal: viaje.${VIAJE_ID}`);
                        
                        window.Echo.channel(`viaje.${VIAJE_ID}`)
                            .listen('ConductorMovido', (data) => {
                                console.log('Posición del conductor actualizada:', data);
                                if (data.lat && data.lng) {
                                    const nuevaPos = [data.lat, data.lng];
                                    marcadorConductor.setLatLng(nuevaPos);
                                    mapa.panTo(nuevaPos);
                                }
                            });

                        // 2. ESCUCHAR CAMBIOS DE ESTADO (Terminado/Cancelado por Conductor)
                        // Usamos el canal privado del pasajero que ya tienes configurado
                        window.Echo.private(`pasajero.{{ auth()->id() }}`)
                            .listen('ViajeActualizado', (data) => {
                                console.log('El estado del viaje cambió:', data);
                                
                                // Si el conductor completó el viaje de forma exitosa
                                if (data.estado === 'completado' || data.estado === 'finalizado') {
                                    window.location.href = `/pasajero/calificar/${VIAJE_ID}`;
                                }
                                // Si el conductor se vio obligado a cancelarlo en el camino
                                else if (data.estado === 'cancelado') {
                                    alert('El conductor ha tenido que cancelar el viaje.');
                                    window.location.href = '/pasajero/home';
                                }
                            });
                    }

                    // Ajustar mapa si la pantalla cambia de tamaño
                    window.addEventListener('resize', () => {
                        setTimeout(() => mapa.invalidateSize(), 100);
                    });
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