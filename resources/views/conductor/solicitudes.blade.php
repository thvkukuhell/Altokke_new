@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            <div class="conductor-page-header">
                <div>
                    <h1>Solicitudes Pendientes</h1>
                    <p>Viajes disponibles cerca de tu ubicación</p>
                </div>
            </div>
            
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            @if(!$puedeTomarViajes)
                <div class="tarjeta conductor-aviso">
                    <strong>Antes de aceptar viajes</strong>
                    <p>
                        Tu cuenta debe estar verificada y tu billetera debe tener saldo para cubrir la comisión de Altokke.
                    </p>
                    <a href="{{ route('conductor.billetera') }}" class="btn btn-verde btn-sm" style="margin-top:10px;">
                        Recargar saldo
                    </a>
                </div>
            @endif
 
            <div class="solicitudes-lista">
                @forelse($solicitudes as $s)
                    <article class="solicitud-card">
                        <div class="solicitud-borde"></div>

                        <div class="solicitud-cuerpo">
                            <div class="solicitud-info">
                                <div class="solicitud-ruta">
                                    <div class="solicitud-ruta-fila">
                                        <span class="dot verde"></span>
                                        <span class="solicitud-direccion">{{ $s->origen_texto }}</span>
                                    </div>
                                    <div class="solicitud-ruta-fila">
                                        <span class="dot rojo"></span>
                                        <span class="solicitud-direccion">{{ $s->destino_texto }}</span>
                                    </div>
                                </div>

                                <div class="solicitud-meta">
                                    <span class="meta-chip">👤 {{ $s->pasajero->user->nombre_completo ?? 'Pasajero' }}</span>
                                    <span class="meta-chip">💳 {{ ucfirst($s->metodo_pago) }}</span>
                                    <span class="meta-chip">🛺 {{ ucfirst($s->tipo_servicio) }}</span>
                                    <span class="meta-chip">📅 {{ optional($s->fecha_solicitud)->format('d/m/Y H:i') ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="solicitud-acciones">
                                <div class="solicitud-precio">
                                    S/ {{ number_format($s->tarifa_estimada, 2) }}
                                </div>

                                <form method="POST" action="{{ route('conductor.aceptarViaje') }}">
                                    @csrf
                                    <input type="hidden" name="id_viaje" value="{{ $s->id_viaje }}">
                                    <button type="submit" class="btn btn-verde btn-sm" {{ !$puedeTomarViajes ? 'disabled' : '' }}>
                                        Aceptar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty 
                    <div class="conductor-estado-vacio">
                        <div class="conductor-estado-vacio-icono">📭</div>
                        <p>No hay solicitudes pendientes en este momento.</p>
                    </div>
                @endforelse
            </div>
 
        </div>
    </div>
</div>

@endsection
