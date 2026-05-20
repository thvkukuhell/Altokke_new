@extends('layouts.main')
@section('content')

<div class="pagina-pasajero-historial">
    
    <div class="historial-header">
        <div class="header-textos">
            <h1 class="titulo-pagina">Mis viajes</h1>
            <p class="subtitulo-pagina">Historial completo de tus viajes en mototaxi</p>
        </div>
        <a href="{{ route('pasajero.solicitarViaje') }}" class="btn-nuevo-viaje">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 5v14m-7-7h14"/>
            </svg>
            Nuevo viaje
        </a>
    </div>
 
    <div class="filtros-contenedor">
        @foreach($filtros as $val => $label)
            <a href="{{ route('pasajero.historial', ['filtro' => $val]) }}"
               class="filtro-item-btn {{ $filtro === $val ? 'activo' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
 
    @if($viajes->isEmpty())
        <div class="historial-vacio">
            <div class="estado-vacio-icono">🛺</div>
            <p>Aún no tienes viajes en este periodo.<br>¡Solicita tu primer mototaxi ahora!</p>
            <a href="{{ route('pasajero.solicitarViaje') }}" class="btn-nuevo-viaje">
                Solicitar un viaje
            </a>
        </div>
    @else
        <div class="historial-lista">
            @foreach($viajes as $v)
                <a href="{{ route('pasajero.historial') }}" class="tarjeta-viaje-item">
                    
                    <div class="viaje-borde {{ $v['borde_clase'] }}"></div>
     
                    <div class="viaje-cuerpo">
                        <div class="viaje-detalles-izquierda">
                            <div class="puntos-ruta-contenedor">
                                <div class="ruta-linea-punto">
                                    <span class="dot verde"></span>
                                    <span class="direccion-texto-item">{{ $v['origen'] }}</span>
                                </div>
                                <div class="ruta-linea-punto">
                                    <span class="dot rojo"></span>
                                    <span class="direccion-texto-item">{{ $v['destino'] }}</span>
                                </div>
                            </div>
                            
                            <div class="viaje-meta-info">
                                <span>📅 {{ $v['fecha'] }}</span>
                                <span class="separador">•</span>
                                <span>📏 {{ $v['distancia'] }}</span>
                                <span class="separador">•</span>
                                <span>⏱️ {{ $v['tiempo'] }}</span>
                            </div>
                            
                            <div class="badge-estado-container">
                                {!! $v['badge_estado'] !!}
                            </div>
                        </div>
         
                        <div class="viaje-detalles-derecha">
                            <div class="viaje-precio">S/ {{ number_format($v['precio'], 2) }}</div>
                            <div class="viaje-conductor-info">
                                <span class="conductor-avatar-icon">👤</span>
                                <span class="conductor-nombre">{{ $v['conductor'] }}</span>
                            </div>
                            @if($v['calificacion'] > 0)
                                <div class="viaje-estrellas-puntuacion">
                                    {{ str_repeat('★', (int)$v['calificacion']) }}{{ str_repeat('☆', 5 - (int)$v['calificacion']) }}
                                </div>
                            @endif
                        </div>
                    </div>
     
                </a>
            @endforeach
        </div>
    @endif
 
</div>
 
@endsection