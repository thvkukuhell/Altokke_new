@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <h1 class="titulo-pagina">Viaje en Curso</h1>
    <p class="subtitulo-pagina">Estás llevando al pasajero — mantén la página abierta</p>

    <div class="viaje-grid">
        <div class="mapa-viaje">
            <div id="mapa-leaflet-conductor"></div>

            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-conductor">-- min</div>
                        <div class="eta-unidad">ETA</div>
                    </div>
                    <div class="eta-unidad" id="distancia-conductor">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-conductor">GPS activo</div>
                <div class="eta-detalle" id="detalle-ruta-conductor">Calculando ruta</div>
            </div>
        </div>

        {{-- Cargamos las librerias de Leaflet --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @include('mapa.partials.leaflet_helpers')

        <div id="datos-viaje-activo-conductor"
             data-viaje-id="{{ $viaje->id_viaje ?? '' }}"
             data-estado-viaje="{{ $viaje->estado_viaje ?? 'aceptado' }}"
             data-conductor-lat="{{ $conductor->lat_actual ?? '' }}"
             data-conductor-lng="{{ $conductor->lng_actual ?? '' }}"
             data-origen-lat="{{ $viaje->lat_origen ?? '' }}"
             data-origen-lng="{{ $viaje->lng_origen ?? '' }}"
             data-destino-lat="{{ $viaje->lat_destino ?? '' }}"
             data-destino-lng="{{ $viaje->lng_destino ?? '' }}"
             data-ubicacion-url="{{ route('conductor.ubicacion') }}"
             data-csrf-token="{{ csrf_token() }}"
             hidden></div>

        <div class="panel-viaje">
            @if(session('mensaje'))
            <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            @if($viaje)
            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:12px;">Pasajero</p>
                <div class="conductor-fila">
                    <div class="avatar">
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

            <div class="mapa-resumen-ruta">
                <div class="dato-ruta">
                    <span>Distancia</span>
                    <strong id="panel-distancia-conductor">-- km</strong>
                </div>
                <div class="dato-ruta">
                    <span>Tiempo</span>
                    <strong id="panel-tiempo-conductor">-- min</strong>
                </div>
            </div>

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
            @else
            <form method="POST" action="{{ route('conductor.completarViaje') }}" style="margin-top:16px;">
                @csrf
                <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                <button type="submit" class="btn btn-verde btn-ancho">
                    Completar viaje
                </button>
            </form>
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

@vite(['resources/js/conductor/viaje_activo.js'])

@endsection
