@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif
 
            <div class="conductor-dashboard-header">
                <div>
                    <h1>!Bienvenido, {{ $conductor->user->nombre_completo ?? 'Conductor' }}!</h1>
                    <p>Administra tus solicitudes, ganancias y viajes desde tu panel.</p>
                </div>
                <span class="conductor-estado-pill">
                    {{ $viajeActivo ? '🛺 En viaje' : '🟢 Disponible'}}
                </span>
            </div>
 
            <div class="conductor-stats-grid">
                <div class="conductor-stat-card">
                    <div class="conductor-stat-icon">💵</div>
                    <h3>Ganancias totales</h3>
                    <p class="conductor-stat-valor">S/ {{ number_format($ganancias->total ?? 0, 2) }}</p>
                    <span>{{ (int)($ganancias->total_viajes ?? 0) }} viajes completados</span>
                </div>

                <div class="conductor-stat-card">
                    <div class="conductor-stat-icon">⭐</div>
                    <h3>Calificación promedio</h3>
                    <p class="conductor-stat-valor">{{ number_format($conductor->calificacion_promedio ?? 0, 1) }}/5</p>
                    <span>{{ $conductor->licencia_numero ?? 'Licencia pendiente' }}</span>
                </div>

                <div class="conductor-stat-card">
                    <div class="conductor-stat-icon">📍</div>
                    <h3>Estado actual</h3>
                    <p class="conductor-stat-valor">{{ $viajeActivo ? 'Ocupado' : 'Libre' }}</p>
                    <span>{{ $viajeActivo ? 'Tienes un viaje activo' : 'Puedes revisar solicitudes' }}</span>
                </div>
            </div>
 
            @if($viajeActivo)
                <div class="conductor-accion-card activo">
                    <h2>Tienes un viaje activo</h2>
                    <p>Continúa el seguimiento del pasajero y actualiza el estado del viaje.</p>
                    <a href="{{ route('conductor.viaje_activo') }}" class="btn btn-verde">
                        Ir a viaje activo
                    </a>
                </div>
            @else
                <div class="conductor-accion-card">
                    <h2>No hay viajes activos</h2>
                    <p>Revisa las solicitudes pendientes y acepta un nuevo servicio cuando estés listo.</p>
                    <a href="{{ route('conductor.solicitudes') }}" class="btn btn-verde">
                        Ver solicitudes pendientes
                    </a>
                </div>
            @endif
 
        </div>
    </div>
</div>

@endsection