@extends('layouts.main')
@section('content')

@if ($errors->any())
    <div style="background:#ffe5e5; padding:10px; border-radius:8px; margin-bottom:15px;">
        @foreach ($errors->all() as $error)
            <p style="color:#b00020; margin:0;">{{ $error }}</p>
        @endforeach
    </div>
@endif
 
<div class="pagina-pasajero">
    <h1 class="titulo-pagina">Solicitar viaje</h1>
    <p class="subtitulo-pagina">Ingresa tu origen y destino para solicitar tu mototaxi</p>
 
    <div class="solicitar-grid">
 
        {{-- Mapa decorativo --}}
        <div class="mapa-decorativo">
            <div class="mapa-pin pin-origen">
                <div class="circulo" style="background: var(--verde);"></div>
                <div class="mapa-pin-etiqueta">Tu ubicación</div>
            </div>
            <div class="mapa-pin pin-destino">
                <div class="circulo" style="background: var(--rojo);"></div>
                <div class="mapa-pin-etiqueta">Destino</div>
            </div>
            <div class="mapa-etiqueta">📍 Bagua, Amazonas</div>
        </div>
 
        {{-- Formulario --}}
        <div class="panel-solicitud">
            <h3>Detalles del viaje</h3>
 
            <form action="{{ route('pasajero.crearViaje') }}" method="POST">
                @csrf
 
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
 
                <div class="campo-grupo">
                    <label class="campo-label" for="tipo_servicio">Tipo de servicio</label>
                    <select class="campo-select" name="tipo_servicio" id="tipo_servicio">
                        <option value="normal">Normal</option>
                        <option value="express">Express</option>
                    </select>
                </div>
 
                <div class="campo-grupo">
                    <label class="campo-label" for="metodo_pago">Método de pago</label>
                    <select class="campo-select" name="metodo_pago" id="metodo_pago">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="yape">💜 Yape</option>
                        <option value="plin">💙 Plin</option>
                    </select>
                </div>
 
                <div class="tarifa-caja">
                    <div class="tarifa-numero">S/ 3.00</div>
                    <div class="tarifa-label">Tarifa estimada</div>
                </div>
 
                <button type="submit" class="btn btn-verde btn-ancho">
                    Solicitar mototaxi
                </button>
 
            </form>
        </div>
    </div>
</div>

@endsection