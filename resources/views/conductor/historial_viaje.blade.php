@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">

        @include('conductor.partials.sidebar')

        <div class="perfil-contenido">
            <div
                class="pagina-conductor-historial"
                data-url-buscar="{{ route('api.conductor.historial.buscar') }}"
                data-filtro-actual="todos"
            >
                <div class="conductor-historial-header">
                    <div>
                        <h1>Historial de viajes</h1>
                        <p>Consulta tus viajes terminados y busca sin recargar la página</p>
                    </div>
                </div>

                <div class="conductor-historial-resumen">
                    <div class="conductor-resumen-card">
                        <p class="conductor-resumen-labek">Ganancias totales</p>
                        <p class="conductor-resumen-valor verde">S/ {{ number_format($ganancias->total ?? 0, 2) }}</p>
                    </div>
                    <div class="conductor-resumen-card">
                        <p class="conductor-resumen-labek">Viajes completados</p>
                        <p class="conductor-resumen-valor verde">{{ (int)($ganancias->total_viajes ?? 0) }}</p>
                    </div>
                    <div class="conductor-resumen-card">
                        <p class="conductor-resumen-labek">Ganancias de hoy</p>
                        <p class="conductor-resumen-valor verde">S/ {{ number_format($ganancias->hoy ?? 0, 2) }}</p>
                    </div>
                </div>

                <div class="busqueda-conductor-contenedor">
                    <label for="buscarHistorialConductor" class="busqueda-conductor-label">Buscar en mi historial</label>
                    <input 
                        type="search"
                        id="buscarHistorialConductor"
                        class="busqueda-conductor-input"
                        placeholder="Buscar por origen, destino, pasajero, pago o estado..."
                        autocomplete="off"
                    >
                    <p id="historialConductorBusquedaEstado" class="busqueda-conductor-estado">
                        Escribe para buscar sin recargar la página
                    </p>
                </div>

                <div id="historialConductorLista" class="conductor-historial-lista">
                    @forelse($historial as $v)
                        @php
                            $estado = $v['estado_viaje'] ?? 'pendiente';
                            $badgeClase = match($estado) {
                                'completado' => 'badge-verde',
                                'cancelado' => 'badge-rojo',
                                default => 'badge-gris',
                            };
                            $estadoTexto = ucfirst(str_replace('_', ' ', $estado));
                            $puntuacion = (int) data_get($v, 'calificacion.puntuacion', $v['puntuacion'] ?? 0);
                            $pasajeroNombre = trim(
                                data_get($v, 'pasajero.user.nombre_completo', '') . ' ' .
                                data_get($v, 'pasajero.user.apellidos', '')
                            );
                        @endphp

                        <article class="conductor-viaje-card">
                            <div class="viaje-borde {{ $v['borde_clase'] ?? 'borde-verde' }}"></div>

                            <div class="conductor-viaje-contenido">
                                <div class="conductor-viaje-info">
                                    <div class="conductor-ruta">
                                        <div class="conductor-ruta-fila">
                                            <span class="dot verde"></span>
                                            <span class="conductor-direccion">{{ $v['origen_texto'] ?? '—' }}</span>
                                        </div>
                                        <div class="conductor-ruta-fila">
                                            <span class="dot rojo"></span>
                                            <span class="conductor-direccion">{{ $v['destino_texto'] ?? '—' }}</span>
                                        </div>
                                    </div>

                                    <div class="conductor-viaje-meta">
                                        <span class="meta-chip">
                                            📅 {{ isset($v['fecha_fin']) && $v['fecha_fin'] ? \Carbon\Carbon::parse($v['fecha_fin'])->format('d/m/Y H:i') : '—' }}
                                        </span>
                                        <span class="meta-chip">📏 {{ !empty($v['distancia_km']) ? $v['distancia_km'] . ' km' : '—' }}</span>
                                        <span class="meta-chip">⏱️ {{ !empty($v['tiempo_estimado_min']) ? $v['tiempo_estimado_min'] . ' min' : '—' }}</span>
                                        <span class="meta-chip">💳 {{ ucfirst($v['metodo_pago'] ?? '—') }}</span>
                                        <span class="meta-chip">🛺 {{ ucfirst($v['tipo_servicio'] ?? '—') }}</span>
                                    </div>

                                    <div class="badge-estado-container">
                                        <span class="badge {{ $badgeClase }}">{{ $estadoTexto }}</span>
                                    </div>
                                </div>

                                <div class="conductor-viaje-resumen">
                                    <div class="conductor-viaje-precio">
                                        S/ {{ number_format($v['tarifa_final'] ?? $v['tarifa_estimada'] ?? 0, 2) }}
                                    </div>
                                    <span class="meta-chip">👤 {{ $pasajeroNombre !== '' ? $pasajeroNombre : 'Pasajero' }}</span>
                                    @if($puntuacion > 0)
                                        <div class="conductor-viaje-estrellas">
                                            {{ str_repeat('★', $puntuacion) }}{{ str_repeat('☆', 5 - $puntuacion) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="conductor-estado-vacio">
                            <div class="conductor-estado-vacio-icono">🛺</div>
                            <p>Aún no tienes viajes completados o cancelados.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection