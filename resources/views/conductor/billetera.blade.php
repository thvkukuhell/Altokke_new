@extends('layouts.main')
@section('contenido')

<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            <h1 class="titulo-pagina">Mi Billetera</h1>
            <p class="subtitulo-pagina">Tus ganancias y movimientos</p>
 
            {{-- Resumen --}}
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
 
                <div class="tarjeta" style="text-align:center;">
                    <p class="perfil-campo-label">Ganancias hoy</p>
                    <p style="font-family:var(--font-display); font-size:32px; font-weight:800; color:var(--p-verde-dark); letter-spacing:-1.5px;">
                        S/ {{ number_format($ganancias->hoy ?? 0, 2) }}
                    </p>
                </div>
 
                <div class="tarjeta" style="text-align:center;">
                    <p class="perfil-campo-label">Esta semana</p>
                    <p style="font-family:var(--font-display); font-size:32px; font-weight:800; letter-spacing:-1.5px;">
                        S/ {{ number_format($ganancias->semana ?? 0, 2) }}
                    </p>
                </div>
 
                <div class="tarjeta" style="text-align:center;">
                    <p class="perfil-campo-label">Total acumulado</p>
                    <p style="font-family:var(--font-display); font-size:32px; font-weight:800; letter-spacing:-1.5px;">
                        S/ {{ number_format($ganancias->total ?? 0, 2) }}
                    </p>
                </div>
 
            </div>
 
            {{-- Últimos viajes --}}
            <div class="tarjeta">
                <div class="perfil-encabezado">
                    <h2>Últimos viajes pagados</h2>
                </div>
 
                @if($ultimosViajes->isEmpty())
                    <p style="color:var(--gray); text-align:center; padding:24px 0;">
                        Aún no tienes viajes completados.
                    </p>
                @else
                    @foreach($ultimosViajes as $v)
                        <div class="fila-dato" style="padding:10px 0; border-bottom:1px solid var(--border);">
                            <div>
                                <div style="font-weight:600; font-size:14px;">
                                    {{ $v->origen_texto }} → {{ $v->destino_texto }}
                                </div>
                                <div style="font-size:12px; color:var(--gray); margin-top:2px;">
                                    {{ $v->fecha_fin ? \Carbon\Carbon::parse($v->fecha_fin)->format('d/m/Y H:i') : '—' }}
                                    · {{ $v->pasajero->user->nombre_completo ?? 'Pasajero' }}
                                </div>
                            </div>
                            <strong style="color:var(--p-verde-dark); font-size:15px; white-space:nowrap;">
                                + S/ {{ number_format($v->tarifa_final ?? $v->tarifa_estimada, 2) }}
                            </strong>
                        </div>
                    @endforeach
                @endif
            </div>
 
            <p style="color:var(--gray-lite); text-align:center; margin-top:20px; font-size:13px;">
                Retiros y recargas próximamente.
            </p>
 
        </div>
    </div>
</div>

@endsection