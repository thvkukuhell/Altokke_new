@extends('layouts.main')
@section('content')

@if ($errors->any())
    <div class="alerta-errores">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
 
<div class="pagina-pasajero">
 
    <div class="solicitar-grid">
 
        {{-- Mapa decorativo --}}
        <div class="mapa-decorativo">
            <div class="mapa-calle mapa-calle-h1"></div>
            <div class="mapa-calle mapa-calle-h2"></div>
            <div class="mapa-calle mapa-calle-v1"></div>
            <div class="mapa-calle mapa-calle-v2"></div>
 
            <svg class="mapa-ruta-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path class="mapa-ruta-path" d="M22 70 Q22 45 50 45 Q78 45 78 18" />
            </svg>
 
            <div class="mapa-pin pin-origen">
                <div class="circulo" style="background:var(--p-verde-mid);"></div>
                <div class="mapa-pin-etiqueta">Tu ubicación</div>
            </div>
            <div class="mapa-pin pin-destino">
                <div class="circulo" style="background:var(--p-rojo);"></div>
                <div class="mapa-pin-etiqueta">Destino</div>
            </div>
 
            <div class="mapa-etiqueta"> Bagua, Amazonas</div>
        </div>
 
        {{-- Panel formulario --}}
        <div class="panel-solicitud">
            <p class="panel-solicitud-titulo">¿A dónde vamos?</p>
            <p class="panel-solicitud-sub">Ingresa tu origen y destino para solicitar tu mototaxi</p>
 
            <form action="{{ route('pasajero.crearViaje') }}" method="POST">
                @csrf
 
                {{-- Ruta --}}
                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text"
                               name="origen"
                               value="{{ old('origen') }}"
                               placeholder="¿Dónde estás?"
                               required
                               autocomplete="off">
                    </div>
                    <div class="ruta-fila">
                        <div class="punto punto-rojo"></div>
                        <input type="text"
                               name="destino"
                               value="{{ old('destino') }}"
                               placeholder="¿A dónde vas?"
                               required
                               autocomplete="off">
                    </div>
                </div>
 
                {{-- Tipo de servicio --}}
                <div class="campo-grupo">
                    <label class="campo-label">Tipo de servicio</label>
                    <div class="servicio-chips">
                        <label class="servicio-chip seleccionado" id="chip-normal">
                            <input type="radio" name="tipo_servicio" value="normal" checked>
                            <span class="servicio-chip-icono">🛺</span>
                            <span class="servicio-chip-nombre">Normal</span>
                            <span class="servicio-chip-precio">Desde S/ 3</span>
                        </label>
                        <label class="servicio-chip" id="chip-express">
                            <input type="radio" name="tipo_servicio" value="express">
                            <span class="servicio-chip-icono">⚡</span>
                            <span class="servicio-chip-nombre">Express</span>
                            <span class="servicio-chip-precio">Desde S/ 5</span>
                        </label>
                    </div>
                </div>
 
                {{-- Método de pago --}}
                <div class="campo-grupo">
                    <label class="campo-label">Método de pago</label>
                    <div class="pago-opciones">
                        <label class="pago-opcion activo">
                            <input type="radio" name="metodo_pago" value="efectivo" checked>
                            Efectivo
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="yape">
                            Yape
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="plin">
                            Plin
                        </label>
                    </div>
                </div>
 
                {{-- Tarifa estimada --}}
                <div class="tarifa-caja">
                    <div>
                        <div class="tarifa-numero">S/ 3.00</div>
                    </div>
                    <div class="tarifa-right">
                        <div class="tarifa-label">Tarifa estimada</div>
                        <div class="tarifa-detalle">~1.5 km · 5 min</div>
                    </div>
                </div>
 
                <button type="submit" class="btn btn-verde btn-ancho">
                    Solicitar mototaxi
                </button>
 
            </form>
        </div>
    </div>
</div>
 
<script>
    // Chips de servicio
    document.querySelectorAll('.servicio-chip input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.servicio-chip').forEach(c => c.classList.remove('seleccionado'));
            radio.closest('.servicio-chip').classList.add('seleccionado');
        });
    });
 
    // Opciones de pago
    document.querySelectorAll('.pago-opcion input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.pago-opcion').forEach(o => o.classList.remove('activo'));
            radio.closest('.pago-opcion').classList.add('activo');
        });
    });
</script>
 
@endsection