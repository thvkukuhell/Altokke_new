@extends('layouts.main')
@section('contenido')

<div class="pagina-conductor">
    <h1 class="titulo-pagina">Viaje en Curso</h1>
    <p class="subtitulo-pagina">Estás llevando al pasajero — mantén la página abierta</p>
 
    <div class="viaje-grid">
 
        {{-- Mapa decorativo --}}
        <div class="mapa-viaje">
            <div class="eta-caja">
                <div class="eta-numero">4</div>
                <div class="eta-unidad">min restantes</div>
            </div>
            <div class="moto-punto">🏍️</div>
            <div class="mapa-pin-origen">
                <div class="dot-origen"></div>
                <div class="pin-tag">Origen</div>
            </div>
            <div class="mapa-pin-destino">
                <div class="dot-destino"></div>
                <div class="pin-tag">Destino</div>
            </div>
            <div class="mapa-etiqueta">📍 Bagua — En vivo</div>
        </div>
 
        {{-- Panel derecho --}}
        <div class="panel-viaje">
 
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif
 
            @if($viaje)
 
                <div class="tarjeta">
                    <p class="campo-label" style="margin-bottom:12px;">Pasajero</p>
                    <div class="conductor-fila">
                        <div class="avatar">
                            {{-- Iniciales del pasajero --}}
                            {{ strtoupper(substr($viaje->pasajero->user->nombre_completo ?? 'P', 0, 1)) }}
                        </div>
                        <div>
                            <div class="conductor-nombre">
                                {{ $viaje->pasajero->user->nombre_completo ?? 'Pasajero' }}
                            </div>
                            <div class="conductor-dato">
                                📱 {{ $viaje->pasajero->user->telefono ?? '—' }}
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
                        <strong style="color:var(--p-verde-dark); font-size:17px;">
                            S/ {{ number_format($viaje->tarifa_final ?? $viaje->tarifa_estimada, 2) }}
                        </strong>
                    </div>
                    <div class="fila-dato" style="margin:0;">
                        <span>Estado</span>
                        <strong>{{ ucfirst($viaje->estado_viaje ?? 'en_curso') }}</strong>
                    </div>
                </div>
 
                {{-- Completar viaje --}}
                <form method="POST" action="{{ route('conductor.completarViaje') }}" style="margin-top:16px;">
                    @csrf
                    <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                    <button type="submit" class="btn btn-verde btn-ancho">
                        ✅ Completar Viaje
                    </button>
                </form>
 
                {{-- Cancelar viaje --}}
                <form method="POST" action="{{ route('conductor.cancelarViaje') }}"
                      style="margin-top:10px;"
                      onsubmit="return confirm('¿Seguro que quieres cancelar este viaje?')">
                    @csrf
                    <input type="hidden" name="id_viaje" value="{{ $viaje->id_viaje }}">
                    <button type="submit" class="btn btn-rojo btn-ancho">
                        ✕ Cancelar viaje
                    </button>
                </form>
 
            @else
                <div class="tarjeta" style="text-align:center; padding:48px 24px;">
                    <div style="font-size:48px; margin-bottom:16px;">⏳</div>
                    <h2 style="font-family:var(--font-display); margin-bottom:8px;">En espera</h2>
                    <p style="color:var(--gray);">
                        Aún no hay un viaje activo. Aquí aparecerá cuando se asigne.
                    </p>
                    <a href="{{ route('conductor.solicitudes') }}"
                       class="btn btn-verde"
                       style="margin-top:20px;">
                        Ver solicitudes
                    </a>
                </div>
            @endif
 
        </div>
    </div>
</div>

@endsection