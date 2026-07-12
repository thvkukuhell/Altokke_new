@extends('layouts.main')
@section('content')

<div class="pagina-pasajero-historial"
     data-url-busqueda="{{ route('api.internal.pasajero.historial') }}"
     data-filtro-actual="{{ $filtro }}">
    <div class="historial-header">
        <div class="header-textos">
            <h1 class="titulo-pagina">Mis viajes</h1>
            <p class="subtitulo-pagina">Revisa tus rutas, estados y pagos en un solo lugar.</p>
        </div>

        <div class="historial-acciones">
            <a href="{{ route('pasajero.solicitarViaje') }}" class="btn btn-verde historial-accion-principal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M12 5v14m-7-7h14"/>
                </svg>
                Nuevo viaje
            </a>
            <a href="{{ route('pasajero.historial.csv') }}" class="btn btn-outline historial-accion-secundaria">
                Exportar CSV
            </a>
        </div>
    </div>

    <div class="historial-busqueda">
        <label for="buscar-viajes">Buscar en mis viajes</label>
        <input
            type="search"
            id="buscar-viajes"
            placeholder="Origen, destino, estado o conductor"
            autocomplete="off"
        >
        <p id="estado-busqueda" class="historial-busqueda-estado" role="status"></p>
    </div>

    <div class="filtros-contenedor">
        @foreach($filtros as $val => $label)
            <a href="{{ route('pasajero.historial', ['filtro' => $val]) }}"
               class="filtro-item-btn {{ $filtro === $val ? 'activo' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div id="historial-contenido-inicial">
    @if($viajes->count() === 0)
        <div class="historial-vacio">
            <div class="estado-vacio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M7 16h10" stroke-linecap="round"/>
                    <path d="M9 16l1.6-4.5A2 2 0 0 1 12.49 10H16a2 2 0 0 1 1.89 1.37L19 16" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="8" cy="18" r="1.6"/>
                    <circle cx="18" cy="18" r="1.6"/>
                    <path d="M4 13.5h2.5l1-2.5h2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p>Aun no tienes viajes en este periodo. Cuando solicites uno, aparecera aqui con su estado y detalles.</p>
            <a href="{{ route('pasajero.solicitarViaje') }}" class="btn btn-verde btn-ancho-mobile">
                Solicitar un viaje
            </a>
        </div>
    @else
        <div class="historial-lista">
            @foreach($viajes as $v)
                <article class="tarjeta-viaje-item tarjeta-viaje-item--{{ $v['estado_viaje'] }}">
                    <div class="viaje-cuerpo">
                        <div class="viaje-main">
                            <div class="viaje-main-top">
                                <div class="viaje-rutas">
                                    <div class="viaje-ruta">
                                        <span class="viaje-ruta-label">Origen</span>
                                        <p class="viaje-ruta-texto">{{ $v['origen'] }}</p>
                                    </div>
                                    <div class="viaje-ruta">
                                        <span class="viaje-ruta-label">Destino</span>
                                        <p class="viaje-ruta-texto">{{ $v['destino'] }}</p>
                                    </div>
                                </div>

                                <div class="viaje-state-pill">
                                    <span class="badge {{ $v['badge_clase'] ?? 'badge-gris' }}">
                                        {{ $v['estado_texto'] ?? 'Pendiente' }}
                                    </span>
                                </div>
                            </div>

                            <div class="viaje-meta">
                                <span class="viaje-meta-item">Fecha {{ $v['fecha'] }}</span>
                                <span class="viaje-meta-item">Distancia {{ $v['distancia'] }}</span>
                                <span class="viaje-meta-item">ETA {{ $v['tiempo'] }}</span>
                                <span class="viaje-meta-item">Conductor: {{ $v['conductor'] }}</span>
                                @if($v['metodo_pago'])
                                    <span class="viaje-meta-item">Pago: {{ $v['metodo_pago'] }}</span>
                                @endif
                                @if(($v['estado_viaje'] ?? '') === 'cancelado' && !empty($v['motivo_cancelacion']))
                                    <span class="viaje-meta-item viaje-meta-item-error">
                                        Motivo: 
                                        @switch($v['motivo_cancelacion'])
                                            @case('demora_conductor') El conductor está demorando demasiado. @break
                                            @case('pasajero_no_en_punto') El pasajero no se encuentra en el punto de recojo. @break
                                            @case('ubicacion_incorrecta') Se ingresó una ubicación incorrecta. @break
                                            @case('cambio_opinion') Se equivocó al marcar el destino de viaje. @break
                                            @case('problemas_vehiculo') Problemas con el vehículo. @break
                                            @case('otro') {{ $v['motivo_cancelacion_otro'] ?? 'Otro motivo.' }} @break
                                            @default {{ $v['motivo_cancelacion'] }}
                                        @endswitch
                                    </span>
                                @endif
                                @if($v['calificacion'] > 0)
                                    <span class="viaje-meta-item viaje-meta-item-rating">
                                        Calificacion {{ (int) $v['calificacion'] }}/5
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="viaje-summary">
                            <div class="viaje-price-block">
                                <span class="viaje-precio-label">{{ $v['precio_label'] }}</span>
                                <div class="viaje-precio">S/ {{ number_format((float) $v['precio'], 2) }}</div>
                            </div>

                            @if(($v['estado_viaje'] ?? '') === 'completado')
                                <button type="button" class="btn btn-outline btn-comprobante" data-url="{{ route('reportes.viajes.comprobante', $v['id']) }}">
                                    Descargar PDF
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="paginacion-historial">
            {{ $viajes->links() }}
        </div>
    @endif
    </div>

    <div id="resultados-busqueda" class="historial-lista" hidden></div>
</div>

@vite(['resources/js/pasajero/historial.js'])

@endsection
