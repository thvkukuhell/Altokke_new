@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <div class="solicitar-grid">
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-buscando">-- min</div>
                        <div class="eta-unidad">Ruta</div>
                    </div>
                    <div class="eta-unidad" id="distancia-buscando">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-buscando">Ruta estimada</div>
                <div class="eta-detalle" id="detalle-ruta-buscando">Buscando conductor cercano</div>
            </div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🔍</span>
                <span id="ubicacion-texto">Buscando la mototaxi más cercana en Bagua...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in" title="Acercar">+</button>
                <button class="mapa-boton-zoom" id="zoom-out" title="Alejar">−</button>
            </div>
        </div>

        <div class="buscando-centro" style="margin-top: 0;">
            <div class="icono-buscando">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M5 17H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11l4 4v4" />
                    <circle cx="7" cy="17" r="2" />
                    <circle cx="17" cy="17" r="2" />
                    <path d="M9 17h6" />
                </svg>
            </div>

            <h2 class="buscando-titulo" id="buscando-titulo">Buscando mototaxi cercano...</h2>
            <span class="buscando-tiempo buscando-tiempo-ajax" id="buscando-estado-badge">Estado: buscando</span>
            <span class="buscando-tiempo">⏱ Menos de 2 minutos</span>
            <p class="buscando-desc">
                Te conectamos con el conductor más cercano disponible en Bagua
            </p>

            <div class="ajax-estado buscando-ajax" id="buscando-ajax-estado" role="status">
                <span class="ajax-punto"></span>
                <span id="buscando-ajax-texto">Consultando estado cada 7 segundos</span>
            </div>

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
@include('mapa.partials.leaflet_helpers')

<div id="datos-buscando-conductor"
     data-origen-lat="{{ $viaje['origen_lat'] ?? '' }}"
     data-origen-lng="{{ $viaje['origen_lng'] ?? '' }}"
     data-destino-lat="{{ $viaje['destino_lat'] ?? '' }}"
     data-destino-lng="{{ $viaje['destino_lng'] ?? '' }}"
     data-viaje-id="{{ $viaje['id'] ?? '' }}"
     data-estado-url="{{ ($viaje['id'] ?? 0) ? route('api.internal.viajes.show', $viaje['id']) : '' }}"
     data-pasajero-id="{{ auth()->id() }}"
     hidden></div>

@vite(['resources/js/pasajero/buscando_conductor.js'])

@endsection
