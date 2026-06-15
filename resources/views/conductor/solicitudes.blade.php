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

            <div class="ajax-estado" id="solicitudes-ajax-estado" role="status">
                <span class="ajax-punto"></span>
                <span id="solicitudes-ajax-texto">Actualizando solicitudes cada 8 segundos</span>
            </div>

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
 
            <div id="solicitudes-lista" data-puede-tomar="{{ $puedeTomarViajes ? '1' : '0' }}">
                @if($solicitudes->isEmpty())
                    <div class="tarjeta estado-vacio">
                        <p>No hay solicitudes pendientes en este momento.</p>
                    </div>
                @else
                    @foreach($solicitudes as $s)
                    <div class="tarjeta solicitud-card">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lista = document.getElementById('solicitudes-lista');
    const estadoTexto = document.getElementById('solicitudes-ajax-texto');
    const endpoint = @json(route('api.internal.conductor.solicitudes'));
    const aceptarUrl = @json(route('conductor.aceptarViaje'));
    const csrfToken = @json(csrf_token());

    if (!lista || !estadoTexto) return;

    const puedeTomarInicial = lista.dataset.puedeTomar === '1';
    let cargando = false;

    function textoSeguro(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function pintarEstado(mensaje, tipo = 'normal') {
        estadoTexto.textContent = mensaje;
        estadoTexto.closest('.ajax-estado')?.setAttribute('data-tipo', tipo);
    }

    function pintarVacio(mensaje) {
        lista.innerHTML = `
            <div class="tarjeta estado-vacio">
                <p>${textoSeguro(mensaje)}</p>
            </div>
        `;
    }

    function tarjetaSolicitud(solicitud, puedeTomar) {
        const boton = puedeTomar
            ? `<form method="POST" action="${aceptarUrl}">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="id_viaje" value="${textoSeguro(solicitud.id)}">
                    <button type="submit" class="btn btn-verde btn-sm">Aceptar</button>
                </form>`
            : `<span class="solicitud-bloqueada">Billetera o verificacion pendiente</span>`;

        return `
            <div class="tarjeta solicitud-card" data-viaje-id="${textoSeguro(solicitud.id)}">
                <div class="viaje-cuerpo solicitud-cuerpo">
                    <div class="solicitud-info">
                        <div class="viaje-ruta solicitud-ruta">
                            ${textoSeguro(solicitud.origen)}
                            <span class="solicitud-flecha">-></span>
                            ${textoSeguro(solicitud.destino)}
                        </div>
                        <div class="viaje-meta">
                            Pasajero: ${textoSeguro(solicitud.pasajero)}
                            | Pago: ${textoSeguro(solicitud.metodo_pago)}
                            | ${textoSeguro(solicitud.tipo_servicio)}
                        </div>
                        <div class="viaje-meta solicitud-tiempo">
                            ${textoSeguro(solicitud.distancia)} | ${textoSeguro(solicitud.tiempo)} | ${textoSeguro(solicitud.fecha)}
                        </div>
                    </div>
                    <div class="solicitud-acciones">
                        <div class="solicitud-tarifa">S/ ${textoSeguro(solicitud.tarifa)}</div>
                        ${boton}
                    </div>
                </div>
            </div>
        `;
    }

    async function cargarSolicitudes() {
        if (cargando) return;
        cargando = true;
        pintarEstado('Consultando solicitudes...', 'cargando');

        try {
            const respuesta = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo consultar el servidor');
            }

            const data = await respuesta.json();
            if (!data.ok) {
                throw new Error(data.message || 'Respuesta no valida');
            }

            const solicitudes = Array.isArray(data.data?.solicitudes) ? data.data.solicitudes : [];
            const puedeTomar = puedeTomarInicial;

            if (!solicitudes.length) {
                pintarVacio('No hay solicitudes pendientes en este momento.');
            } else {
                lista.innerHTML = solicitudes
                    .map((solicitud) => tarjetaSolicitud(solicitud, puedeTomar))
                    .join('');
            }

            pintarEstado(`Solicitudes actualizadas: ${solicitudes.length}`, 'ok');
        } catch (error) {
            pintarEstado('No se pudo actualizar. Se mostrara la ultima lista disponible.', 'error');
        } finally {
            cargando = false;
        }
    }

    cargarSolicitudes();
    window.setInterval(cargarSolicitudes, 8000);
});
</script>

@endsection
