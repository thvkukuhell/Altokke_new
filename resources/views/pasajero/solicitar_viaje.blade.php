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

{{-- Librerias de Leaflet necesarias --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@include('mapa.partials.leaflet_helpers')

<div class="pagina-pasajero">
    <div class="solicitar-grid">

        {{-- MAPA --}}
        <div class="mapa-decorativo">
            <div id="mapa-solicitud-pasajero"></div>
            <div class="mapa-panel-eta">
                <div class="eta-superior">
                    <div>
                        <div class="eta-numero" id="eta-solicitud">-- min</div>
                        <div class="eta-unidad">Ruta</div>
                    </div>
                    <div class="eta-unidad" id="distancia-solicitud">-- km</div>
                </div>
                <div class="eta-estado" id="estado-ruta-solicitud">GPS activo</div>
                <div class="eta-detalle" id="detalle-ruta-solicitud">Elige tu destino</div>
            </div>
            <div class="mapa-etiqueta">
                <span class="mapa-etiqueta-icono">📍</span>
                <span id="ubicacion-texto">Obteniendo tu ubicación...</span>
            </div>
            <div class="mapa-controles">
                <button class="mapa-boton-zoom" id="zoom-in" title="Acercar">+</button>
                <button class="mapa-boton-zoom" id="zoom-out" title="Alejar">−</button>
                <button class="mapa-boton-milocation" id="mi-ubicacion" title="Mi ubicación">🎯</button>
            </div>
        </div>

        {{-- Panel formulario --}}
        <div class="panel-solicitud">
            <p class="panel-solicitud-titulo">¿A dónde vamos?</p>
            <p class="panel-solicitud-sub">Ingresa tu origen y destino para solicitar tu mototaxi</p>

            <form action="{{ route('pasajero.crearViaje') }}" method="POST" id="form-solicitar-viaje">
                @csrf

                <div class="ruta-selector">
                    <div class="ruta-fila">
                        <div class="punto punto-verde"></div>
                        <input type="text" name="origen" id="origen-input" placeholder="Origen" required>
                        <input type="hidden" name="origen_lat" id="origen-lat">
                        <input type="hidden" name="origen_lng" id="origen-lng">
                        <input type="hidden" name="destino_lat" id="destino-lat">
                        <input type="hidden" name="destino_lng" id="destino-lng">
                        <input type="hidden" name="tarifa_estimada" id="tarifa-hidden">
                        <input type="hidden" name="distancia_km" id="distancia-hidden">
                        <input type="hidden" name="tiempo_min" id="tiempo-hidden">
                    </div>
                    <div class="ruta-fila">
                        <div class="punto punto-rojo"></div>
                        <input type="text" name="destino" id="destino-input" placeholder="¿A dónde vas?" required>
                    </div>
                </div>

                <div class="campo-grupo">
                    <label class="campo-label">Tipo de servicio</label>
                    <div class="servicio-chips">
                        <label class="servicio-chip seleccionado">
                            <input type="radio" name="tipo_servicio" value="normal" checked>
                            <span>🛺</span>
                            <span>Normal</span>
                            <span>Desde S/3</span>
                        </label>
                        <label class="servicio-chip">
                            <input type="radio" name="tipo_servicio" value="express">
                            <span>⚡</span>
                            <span>Express</span>
                            <span>Desde S/5</span>
                        </label>
                    </div>
                </div>

                <div class="campo-grupo">
                    <label class="campo-label">Método de pago</label>
                    <div class="pago-opciones">
                        <label class="pago-opcion activo">
                            <input type="radio" name="metodo_pago" value="efectivo" checked> Efectivo
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="yape"> Yape
                        </label>
                        <label class="pago-opcion">
                            <input type="radio" name="metodo_pago" value="plin"> Plin
                        </label>
                    </div>
                </div>

                <div class="tarifa-caja">
                    <div class="tarifa-numero" id="tarifa-numero">S/ 3.00</div>
                    <div class="tarifa-right">
                        <div class="tarifa-label">Tarifa estimada</div>
                        <div class="tarifa-detalle" id="tarifa-detalle">~0 km · 0 min</div>
                    </div>
                </div>

                <div class="mapa-form-error" id="mapa-form-error" hidden></div>
                <button type="submit" class="btn btn-verde btn-ancho">Solicitar mototaxi</button>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/pasajero/solicitar_viaje.js'])

@endsection
