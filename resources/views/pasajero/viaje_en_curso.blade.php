@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <h1 class="titulo-pagina">Viaje en curso</h1>
    <p class="subtitulo-pagina">Estás en camino — mantén la página abierta</p>
 
    <div class="viaje-grid">
 
        {{-- Mapa --}}
        <div class="mapa-viaje">
            <div class="eta-caja">
                <div class="eta-numero">{{ $eta ?? '—' }}</div>
                <div class="eta-unidad">min restantes</div>
            </div>
        </div>
 
        {{-- Panel lateral --}}
        <div class="panel-viaje">
 
            {{-- Conductor --}}
            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:12px;">Conductor</p>
                <div class="conductor-fila">
                    <div class="avatar">{{ $iniciales ?? '—' }}</div>
                    <div>
                        <div class="conductor-nombre">{{ $conductor['nombre'] ?? '—' }}</div>
                        <div class="conductor-dato">
                            ★ {{ number_format($conductor['calificacion'] ?? 0, 1) }}
                            · {{ $conductor['modelo'] ?? '—' }}
                        </div>
                    </div>
                    <div class="placa">{{ $conductor['placa'] ?? '—' }}</div>
                </div>
 
                <hr class="separador">
 
                <div class="fila-dato">
                    <span>Origen</span>
                    <strong>{{ $viaje['origen'] ?? '—' }}</strong>
                </div>
                <div class="fila-dato">
                    <span>Destino</span>
                    <strong>{{ $viaje['destino'] ?? '—' }}</strong>
                </div>
                <div class="fila-dato">
                    <span>Tarifa</span>
                    <strong style="color:var(--p-verde-dark); font-size:17px;">
                        S/ {{ $viaje['tarifa'] ?? '0.00' }}
                    </strong>
                </div>
                <div class="fila-dato" style="margin:0;">
                    <span>Pago</span>
                    <strong>{{ ucfirst($viaje['metodo_pago'] ?? 'efectivo') }}</strong>
                </div>
            </div>
 
            {{-- Timeline --}}
            <div class="tarjeta">
                <p class="campo-label" style="margin-bottom:12px;">Estado del viaje</p>
                <div class="timeline">
                    @foreach($pasos ?? [] as $i => $paso)
                        <div class="paso {{ $paso['estado'] }}">
                            <div class="paso-icono">
                                @if($paso['estado'] === 'hecho') ✓
                                @else {{ $i + 1 }}
                                @endif
                            </div>
                            <div>
                                <div class="paso-titulo">{{ $paso['titulo'] }}</div>
                                <div class="paso-sub">{{ $paso['sub'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
 
                <hr class="separador">
 
                <form action="{{ route('pasajero.cancelarViaje') }}"
                      method="POST"
                      onsubmit="return confirm('¿Seguro que quieres cancelar el viaje?')">
                    @csrf
                    <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
                    <button type="submit" class="btn btn-rojo btn-ancho">Cancelar viaje</button>
                </form>
            </div>
 
        </div>
    </div>
</div>

@endsection