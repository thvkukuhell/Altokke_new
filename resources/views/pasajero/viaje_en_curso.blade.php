@extends('layouts.main')
@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@include('mapa.partials.leaflet_helpers')

<div class="pagina-pasajero">
    <div class="solicitar-grid">
        <div class="mapa-decorativo">
            <div id="mapa-en-curso"></div>
            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-pasajero">-- min</div>
                        <div class="eta-unidad">Llegada</div>
                    </div>
                    <div class="eta-unidad" id="distancia-pasajero">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-pasajero">Calculando ruta</div>
                <div class="eta-detalle" id="detalle-ruta-pasajero">Conectando con el mapa</div>
            </div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">🛺</span>
                <span id="estado-texto">Conductor en camino...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in">+</button>
                <button class="mapa-boton-zoom" id="zoom-out">−</button>
            </div>
        </div>

        <div class="panel-solicitud" style="display:flex; flex-direction:column; gap:16px;">
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

            <div class="tarifa-caja">
                <div class="tarifa-numero">S/ {{ number_format($viaje['tarifa'] ?? 0, 2) }}</div>
                <div class="tarifa-right">
                    <div class="tarifa-label">Tarifa estimada</div>
                    <div class="tarifa-detalle" id="tarifa-detalle-curso">{{ ucfirst($viaje['metodo_pago'] ?? 'efectivo') }}</div>
                </div>
            </div>

            <div class="mapa-resumen-ruta">
                <div class="dato-ruta">
                    <span>Distancia</span>
                    <strong id="panel-distancia-pasajero">-- km</strong>
                </div>
                <div class="dato-ruta">
                    <span>ETA</span>
                    <strong id="panel-tiempo-pasajero">-- min</strong>
                </div>
            </div>

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

            <div id="seccion-calificar" style="display:none;">
                <a href="{{ route('pasajero.calificar', $viaje['id'] ?? 0) }}"
                   class="btn btn-verde btn-ancho"
                   style="font-size:15px; padding:14px;">
                    ⭐ Calificar conductor
                </a>
            </div>

            @if(in_array($viaje['estado'] ?? '', ['aceptado', 'recogiendo'], true))
            {{-- 8J_BOTON_CANCELAR_PASAJERO -> luego ir a controlador cancelar --}}
            <form method="POST" action="{{ route('pasajero.cancelarViaje') }}" id="form-cancelar">
                @csrf
                <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                <button type="submit" class="btn btn-outline btn-ancho">✕ Cancelar viaje</button>
            </form>
            @endif
        </div>
    </div>
</div>

<div id="datos-viaje-en-curso"
     data-viaje-id="{{ $viaje['id'] ?? '' }}"
     data-estado-inicial="{{ $viaje['estado'] ?? 'aceptado' }}"
     data-estado-url="{{ ($viaje['id'] ?? 0) ? route('api.internal.viajes.show', $viaje['id']) : '' }}"
     data-calificar-url="{{ ($viaje['id'] ?? 0) ? route('pasajero.calificar', $viaje['id']) : '' }}"
     data-historial-url="{{ route('pasajero.historial') }}"
     data-origen-lat="{{ $viaje['origen_lat'] ?? '' }}"
     data-origen-lng="{{ $viaje['origen_lng'] ?? '' }}"
     data-destino-lat="{{ $viaje['destino_lat'] ?? '' }}"
     data-destino-lng="{{ $viaje['destino_lng'] ?? '' }}"
     data-conductor-lat="{{ $conductor['lat'] ?? '' }}"
     data-conductor-lng="{{ $conductor['lng'] ?? '' }}"
     data-pasajero-id="{{ auth()->id() }}"
     hidden></div>

@vite(['resources/js/pasajero/viaje_en_curso.js'])

@endsection
