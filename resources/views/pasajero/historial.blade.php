@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <h1 class="titulo-pagina">Mis viajes</h1>
    <p class="subtitulo-pagina">Historial completo de tus viajes en mototaxi</p>
 
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
            <p>No tienes viajes en este periodo.</p>
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
                            <span style="color:var(--gray-lite); font-weight:400;">→</span>
                            {{ $v['destino'] }}
                        </div>
                        <div class="viaje-meta">
                            {{ $v['fecha'] }}
                            · {{ $v['distancia'] }}
                            · {{ $v['tiempo'] }}
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