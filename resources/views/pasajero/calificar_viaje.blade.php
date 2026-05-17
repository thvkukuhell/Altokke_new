@extends('layouts.main')
@section('content')

<div class="pagina-pasajero">
    <div class="calificar-wrap">
 
        <div class="calificar-header">
            <div class="check-icono">
                <svg width="32" height="32" viewBox="0 0 20 20" fill="none"
                     stroke="#16a34a" stroke-width="2.5">
                    <path d="M4 10l4 4 8-8"/>
                </svg>
            </div>
            <h1 class="titulo-pagina">¡Llegaste!</h1>
            <p class="subtitulo-pagina" style="margin-bottom:0;">
                Califica tu experiencia con el conductor
            </p>
        </div>
 
        {{-- Resumen --}}
        <div class="resumen-viaje">
            <div class="resumen-ruta">
                <strong>{{ $viaje['origen'] ?? '—' }}</strong>
                <span>→ {{ $viaje['destino'] ?? '—' }}</span>
                <span style="display:block; font-size:11px; margin-top:3px; color:var(--gray-lite);">Viaje completado</span>
            </div>
            <div class="resumen-precio">S/ {{ $viaje['tarifa'] ?? '0.00' }}</div>
        </div>
 
        {{-- Conductor --}}
        <div class="conductor-card">
            <div class="avatar">{{ $iniciales ?? '—' }}</div>
            <div>
                <div class="conductor-nombre">{{ $conductor['nombre'] ?? '—' }}</div>
                <div class="conductor-dato">
                    ★ {{ number_format($conductor['calificacion'] ?? 0, 1) }}
                    · Placa: {{ $conductor['placa'] ?? '—' }}
                </div>
            </div>
        </div>
 
        <form method="POST" action="{{ route('pasajero.enviarCalificacion') }}">
            @csrf
            <input type="hidden" name="viaje_id"     value="{{ $viaje['id'] ?? 0 }}">
            <input type="hidden" name="conductor_id" value="{{ $conductor['id'] ?? 0 }}">
 
            {{-- Estrellas --}}
            <div class="estrellas-grupo">
                <p class="estrellas-titulo">¿Cómo fue tu viaje?</p>
                <div class="estrellas-input">
                    @foreach(range(5, 1) as $n)
                        <input type="radio" name="estrellas" id="e{{ $n }}" value="{{ $n }}"
                               {{ $n === 5 ? 'required' : '' }}>
                        <label for="e{{ $n }}">★</label>
                    @endforeach
                </div>
            </div>
 
            {{-- Comentario --}}
            <div class="campo-grupo">
                <label class="campo-label" for="comentario">Comentario (opcional)</label>
                <textarea name="comentario"
                          id="comentario"
                          class="campo-input"
                          rows="3"
                          placeholder="¿Algo que quieras destacar del viaje?"
                          style="resize:none;"></textarea>
            </div>
 
            <button type="submit" class="btn btn-verde btn-ancho">
                Enviar calificación
            </button>
 
            <a href="{{ route('pasajero.historial') }}"
               class="btn btn-outline btn-ancho"
               style="margin-top:10px; display:flex;">
                Saltar calificación
            </a>
 
        </form>
    </div>
</div>
 
@endsection