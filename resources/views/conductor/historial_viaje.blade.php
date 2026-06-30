@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">

        @include('conductor.partials.sidebar')

        <div class="perfil-contenido pagina-conductor-historial"
             data-url-buscar="{{ route('api.internal.conductor.historial') }}">

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

            <div class="historial-busqueda">
                <label for="buscarHistorialConductor">Buscar en mi historial</label>
                <input
                    type="search"
                    id="buscarHistorialConductor"
                    placeholder="Origen, destino, estado o pasajero"
                    autocomplete="off"
                >
                <p id="historialConductorBusquedaEstado" class="historial-busqueda-estado" role="status"></p>
            </div>

            <div id="historial-conductor-contenido-inicial">
            @if(empty($historial) || count($historial) === 0)
            <div class="tarjeta estado-vacio">
                <p>Aún no tienes viajes completados.</p>
            </div>
            @else
            @foreach($historial as $v)
            <div class="viaje-item">
                <div class="viaje-borde {{ $v['borde_clase'] ?? 'borde-verde' }}"></div>
                <div class="viaje-cuerpo">
                    <div>
                        <div class="viaje-ruta">
                            {{ $v['origen_texto'] ?? '—' }}
                            <span style="color:var(--gray-lite); font-weight:400;">→</span>
                            {{ $v['destino_texto'] ?? '—' }}
                        </div>
                        <div class="viaje-meta">
                            {{ isset($v['fecha_fin']) ? \Carbon\Carbon::parse($v['fecha_fin'])->format('d/m/Y H:i') : '—' }}
                        </div>
                        <div style="margin-top:5px;">
                            {!! $v['badge_estado'] ?? '' !!}
                        </div>
                    </div>
                    <div class="viaje-derecha">
                        <div class="viaje-precio">
                            S/ {{ number_format($v['tarifa_final'] ?? $v['tarifa_estimada'] ?? 0, 2) }}
                        </div>
                        @if(!empty($v['puntuacion']))
                        <div class="viaje-estrellas">
                            {{ str_repeat('★', (int)$v['puntuacion']) }}{{ str_repeat('☆', 5 - (int)$v['puntuacion']) }}
                        </div>
                        @endif
                        @if(($v['estado_viaje'] ?? '') === 'completado')
                        <a href="{{ route('reportes.viajes.comprobante', $v['id_viaje']) }}" class="btn btn-outline btn-sm" style="margin-top:10px;">
                            Descargar PDF
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            <div class="paginacion-historial">
                {{ $historial->links() }}
            </div>
            @endif
            </div>

            <div id="historialConductorLista" class="historial-conductor-resultados" hidden></div>

        </div>
    </div>
</div>

@vite(['resources/js/conductor/historial_busqueda.js'])

@endsection
