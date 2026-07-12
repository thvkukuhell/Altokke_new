@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">

        @include('conductor.partials.sidebar')

        <div class="perfil-contenido">
            <div class="pagina-conductor-historial"
                data-url-busqueda="{{ route('api.internal.conductor.historial') }}"
                data-filtro-actual="todos">

                <h1 class="titulo-pagina">Historial de Viajes</h1>
                
                <div style="margin-bottom:16px;">
                    <a href="{{ route('conductor.historial.csv') }}" class="btn btn-verde">
                        Exportar CSV
                    </a>
                </div>

                <div class="tarjeta" style="margin-bottom:20px;">
                    <div style="display:flex; gap:32px; flex-wrap:wrap;">
                        <div>
                            <p class="perfil-campo-label">Ganancias totales</p>
                            <p
                                style="font-family:var(--font-display); font-size:28px; font-weight:800; color:var(--p-verde-dark); letter-spacing:-1px;">
                                S/ {{ number_format($ganancias->total ?? 0, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="perfil-campo-label">Viajes completados</p>
                            <p
                                style="font-family:var(--font-display); font-size:28px; font-weight:800; letter-spacing:-1px;">
                                {{ (int)($ganancias->total_viajes ?? 0) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="historial-busqueda" style="margin-bottom:20px;">
                    <label for="buscarHistorialConductor">Buscar en mi historial</label>
                    <input
                        type="search"
                        id="buscarHistorialConductor"
                        placeholder="Origen, destino, pasajero, pago o estado"
                        autocomplete="off"
                    >
                    <p id="historialConductorBusquedaEstado" class="historial-busqueda-estado" role="status"></p>
                </div>

                <div class="filtros-contenedor" style="margin-bottom:20px;">
                    <button type="button" class="filtro-item-btn filtro-conductor-btn activo" data-filtro="todos">
                        Todos
                    </button>

                    <button type="button" class="filtro-item-btn filtro-conductor-btn" data-filtro="hoy">
                        Hoy
                    </button>

                    <button type="button" class="filtro-item-btn filtro-conductor-btn" data-filtro="semana">
                        Semana
                    </button>

                    <button type="button" class="filtro-item-btn filtro-conductor-btn" data-filtro="mes">
                        Mes
                    </button>
                </div>

                <div id="historialConductorContenidoInicial">
                    @forelse($historial as $v)
                        <div class="viaje-item">
                            <div class="viaje-borde {{ $v['borde_clase'] ?? 'borde-verde' }}"></div>

                            <div class="viaje-cuerpo">
                                <div>
                                    <div class="viaje-ruta">
                                        {{ $v['origen_texto'] ?? $v['origen'] ?? '—' }}
                                        <span style="color:var(--gray-lite); font-weight:400;">→</span>
                                        {{ $v['destino_texto'] ?? $v['destino'] ?? '—' }}
                                    </div>

                                    <div class="viaje-meta">
                                        {{ isset($v['fecha_fin']) && $v['fecha_fin']
                                            ? \Carbon\Carbon::parse($v['fecha_fin'])->format('d/m/Y H:i')
                                            : ($v['fecha'] ?? '—') }}
                                    </div>

                                    @if(($v['estado_viaje'] ?? $v['estado']) === 'cancelado' && !empty($v['motivo_cancelacion']))
                                        <div class="viaje-meta" style="margin-top:6px; color:#b91c1c; font-weight:700;">
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
                                        </div>
                                    @endif

                                    <div style="margin-top:5px;">
                                        <span class="badge {{ $v['badge_clase'] ?? 'badge-gris' }}">
                                            {{ $v['estado_texto'] ?? 'Pendiente' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="viaje-derecha">
                                    <div class="viaje-precio">
                                        S/ {{ number_format($v['tarifa_final'] ?? $v['tarifa_estimada'] ?? $v['precio'] ?? 0, 2) }}
                                    </div>

                                    @if(!empty($v['puntuacion']) || !empty($v['calificacion']))
                                        @php
                                            $puntos = (int)($v['puntuacion'] ?? $v['calificacion'] ?? 0);
                                        @endphp
                                        <div class="viaje-estrellas">
                                            {{ str_repeat('★', $puntos) }}{{ str_repeat('☆', 5 - $puntos) }}
                                        </div>
                                    @endif

                                    @if(($v['estado_viaje'] ?? $v['estado'] ?? '') === 'completado')
                                        <a href="{{ route('reportes.viajes.comprobante', $v['id_viaje'] ?? $v['id']) }}"
                                            class="btn btn-outline btn-sm"
                                            style="margin-top:10px;">
                                            Descargar PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="tarjeta estado-vacio">
                            <p>Aún no tienes viajes completados.</p>
                        </div>
                    @endforelse

                    @if(method_exists($historial, 'links'))
                        <div class="paginacion-historial">
                            {{ $historial->links() }}
                        </div>
                    @endif
                </div>

                <div id="historialConductorLista" class="historial-lista" hidden></div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/conductor/historial_busqueda.js'])

@endsection
