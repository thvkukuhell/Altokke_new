@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            <h1 class="titulo-pagina">Solicitudes Pendientes</h1>
            <p class="subtitulo-pagina">Viajes disponibles cerca de tu ubicación</p>
 
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            @if(!$puedeTomarViajes)
                <div class="tarjeta" style="border-left:4px solid #f59e0b; margin-bottom:16px;">
                    <strong>Antes de aceptar viajes</strong>
                    <p style="margin:6px 0 0; color:var(--gray);">
                        Tu cuenta debe estar verificada y tu billetera debe tener saldo para cubrir la comisión de Altokke.
                    </p>
                    <a href="{{ route('conductor.billetera') }}" class="btn btn-verde btn-sm" style="margin-top:10px;">
                        Recargar saldo
                    </a>
                </div>
            @endif
 
            @if($solicitudes->isEmpty())
                <div class="tarjeta estado-vacio">
                    <p>No hay solicitudes pendientes en este momento.</p>
                </div>
            @else
                @foreach($solicitudes as $s)
                    <div class="tarjeta" style="margin-bottom:12px;">
                        <div class="viaje-cuerpo" style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
 
                            <div>
                                <div class="viaje-ruta" style="font-size:15px; font-weight:700; margin-bottom:6px;">
                                    {{ $s->origen_texto }}
                                    <span style="color:var(--gray-lite); font-weight:400;">→</span>
                                    {{ $s->destino_texto }}
                                </div>
                                <div class="viaje-meta">
                                    👤 {{ $s->pasajero->user->nombre_completo ?? 'Pasajero' }}
                                    · 💳 {{ ucfirst($s->metodo_pago) }}
                                    · {{ $s->tipo_servicio }}
                                </div>
                            </div>
 
                            <div style="display:flex; align-items:center; gap:16px; flex-shrink:0;">
                                <div style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--p-verde-dark);">
                                    S/ {{ number_format($s->tarifa_estimada, 2) }}
                                </div>
                                <form method="POST" action="{{ route('conductor.aceptarViaje') }}">
                                    @csrf
                                    <input type="hidden" name="id_viaje" value="{{ $s->id_viaje }}">
                                    <button type="submit" class="btn btn-verde btn-sm">
                                        Aceptar
                                    </button>
                                </form>
                            </div>
 
                        </div>
                    </div>
                @endforeach
            @endif
 
        </div>
    </div>
</div>

@endsection
