<div class="pagina-conductor">
    <div class="perfil-layout">
 
        @include('conductor.partials.sidebar')
 
        <div class="perfil-contenido">
 
            @if(session('mensaje'))
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif
 
            <h1 class="titulo-pagina">
                ¡Bienvenido, {{ $conductor->user->nombre_completo ?? 'Conductor' }}!
            </h1>
 
            {{-- Resumen de stats --}}
            <div class="tarjeta resumen-grid" style="margin-bottom:20px;">
                <div class="resumen-item">
                    <h3>Ganancias totales</h3>
                    <p class="valor">S/ {{ number_format($ganancias->total ?? 0, 2) }}</p>
                    <span>{{ (int)($ganancias->total_viajes ?? 0) }} viajes completados</span>
                </div>
                <div class="resumen-item">
                    <h3>Calificación promedio</h3>
                    <p class="valor">{{ number_format($conductor->calificacion_promedio ?? 0, 1) }}/5</p>
                    <span>{{ $conductor->licencia_numero ?? 'Licencia pendiente' }}</span>
                </div>
                <div class="resumen-item">
                    <h3>Estado</h3>
                    <p class="valor">{{ $viajeActivo ? 'En viaje' : 'Disponible' }}</p>
                    <span>{{ $viajeActivo ? 'Tienes un viaje activo' : 'Revisa solicitudes pendientes' }}</span>
                </div>
            </div>
 
            @if($viajeActivo)
                <div class="alert alert-info">
                    <p>Tienes un viaje activo en curso.</p>
                    <a href="{{ route('conductor.viajeActivo') }}" class="btn btn-verde">
                        Ir a viaje activo
                    </a>
                </div>
            @else
                <div class="alert alert-success">
                    <p>No hay viajes activos. Puedes revisar nuevas solicitudes.</p>
                    <a href="{{ route('conductor.solicitudes') }}" class="btn btn-verde">
                        Solicitudes pendientes
                    </a>
                </div>
            @endif
 
        </div>
    </div>
</div>