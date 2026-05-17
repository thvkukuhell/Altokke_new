@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
 
    <div class="historial-header">
        <div>
            <h1 class="titulo-pagina">Mis viajes</h1>
            <p class="subtitulo-pagina">Historial completo de tus viajes en mototaxi</p>
        </div>
        <a href="{{ route('pasajero.solicitarViaje') }}" class="btn btn-verde">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 5v14m-7-7h14"/>
            </svg>
            Nuevo viaje
        </a>
    </div>
 
    {{-- Filtros --}}
    <div class="filtros">
        @foreach($filtros as $val => $label)
            <a href="{{ route('pasajero.historial', ['filtro' => $val]) }}"
               class="filtro-btn {{ $filtro === $val ? 'activo' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
 
    {{-- Lista --}}
    @if($viajes->isEmpty())
        <div class="tarjeta estado-vacio">
            <div class="estado-vacio-icono">🛺</div>
            <p>Aún no tienes viajes en este periodo.<br>¡Solicita tu primer mototaxi ahora!</p>
            <a href="{{ route('pasajero.solicitarViaje') }}" class="btn btn-verde">
                Solicitar un viaje
            </a>
        </div>
    @else
        @foreach($viajes as $v)
            <a href="{{ route('pasajero.historial') }}" class="viaje-item">
 
                <div class="viaje-borde {{ $v['borde_clase'] }}"></div>
 
                <div class="viaje-cuerpo">
                    <div>
                        <div class="viaje-ruta">
                            {{ $v['origen'] }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:0.4; flex-shrink:0;">
                                <path d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                            {{ $v['destino'] }}
                        </div>
                        <div class="viaje-meta">
                            {{ $v['fecha'] }} · {{ $v['distancia'] }} · {{ $v['tiempo'] }}
                        </div>
                        <div style="margin-top:6px;">
                            {!! $v['badge_estado'] !!}
                        </div>
                    </div>
 
                    <div class="viaje-derecha">
                        <div class="viaje-precio">S/ {{ $v['precio'] }}</div>
                        <div class="viaje-conductor">{{ $v['conductor'] }}</div>
                        @if($v['calificacion'] > 0)
                            <div class="viaje-estrellas">
                                {{ str_repeat('★', (int)$v['calificacion']) }}{{ str_repeat('☆', 5 - (int)$v['calificacion']) }}
                            </div>
                        @endif
                    </div>
                </div>
 
            </a>
        @endforeach
    @endif
 
</div>
 
@endsection
