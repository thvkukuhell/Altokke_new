@extends('layouts.main')
@section('content')

<div class="pagina-conductor">
    <div class="perfil-layout">

        @include('conductor.partials.sidebar')

        <div class="perfil-contenido">
            <div class="billetera-header">
                <div>
                    <h1>Mi billetera</h1>
                    <p>Controla tu saldo, recargas y comisiones descontadas.</p>
                </div>
            </div>

            @if(session('mensaje')) 
                <div class="alert alert-success">{{ session('mensaje') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="billetera-saldo-card">
                <p class="billetera-saldo-label">Saldo disponible para comisiones</p>
                <p class="billetera-saldo-monto">S/ {{ number_format($conductor->saldo_disponible ?? 0, 2) }}</p>
                <small>Comisión actual de Altokke: {{ number_format($porcentajeComision, 0) }}% por viaje completado.</small>
            </div>

            <div class="billetera-grid">
                <div class="billetera-card">
                    <h3>Recargar saldo</h3>
                    <form method="POST" action="{{ route('conductor.recargarSaldo') }}" class="billetera-form">
                        @csrf
                        <div class="perfil-grid">
                            <div class="auth-campo">
                                <label>Monto</label>
                                <input type="number" name="monto" min="5" max="500" step="0.50" value="{{ old('monto', 10) }}" required>
                            </div>
                            <div class="auth-campo">
                                <label>Método</label>
                                <select name="metodo_recarga" required>
                                    <option value="yape">Yape QR</option>
                                    <option value="plin">Plin QR</option>
                                    <option value="efectivo">Efectivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="auth-campo">
                            <label>Referencia o código de operación</label>
                            <input type="text" name="referencia" value="{{ old('referencia') }}" placeholder="Ej: YAPE-123456">
                        </div>
                        <button type="submit" class="btn btn-verde btn-retirar">
                            Simular recarga aprobada
                        </button>
                    </form>
                </div>

                <div class="billetera-card">
                    <h3>Resumen</h3>
                    <div class="billetera-resumen-grid">
                        <div class="billetera-resumen-item">
                            <div class="perfil-campo-label">Ganancias brutas</div>
                            <div class="perfil-campo-valor">S/ {{ number_format($ganancias->total ?? 0, 2) }}</div>
                        </div>
                        <div class="billetera-resumen-item">
                            <div class="perfil-campo-label">Viajes completados</div>
                            <div class="perfil-campo-valor">{{ $ganancias->total_viajes ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="billetera-grid">
                <div class="billetera-card">
                    <h3>Últimas recargas</h3>
                    @forelse($recargas as $recarga)
                        <div class="billetera-fila">
                            <span>{{ ucfirst($recarga->metodo_recarga) }} - {{ ucfirst($recarga->estado_recarga) }}</span>
                            <strong>S/ {{ number_format($recarga->monto, 2) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted">Aún no registras recargas.</p>
                    @endforelse
                </div>
                
                <div class="billetera-card">
                    <h3>Comisiones descontadas</h3>
                    @forelse($comisiones as $comision)
                        <div class="billetera-fila">
                            <span>Viaje #{{ $comision->id_viaje }}</span>
                            <strong>S/ {{ number_format($comision->monto_comision, 2) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted">Aún no hay comisiones descontadas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
