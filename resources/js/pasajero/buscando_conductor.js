import '../mapa/leaflet_helpers';

let mapa;
let lineaRuta;
let pollingEstado = null;
let consultandoEstado = false;

document.addEventListener('DOMContentLoaded', function () {
    const datos = document.getElementById('datos-buscando-conductor');
    if (!datos) return;

    const origenReal = AltokkeMapa.puntoValido(datos.dataset.origenLat, datos.dataset.origenLng);
    const destinoReal = AltokkeMapa.puntoValido(datos.dataset.destinoLat, datos.dataset.destinoLng);
    const origen = origenReal || AltokkeMapa.puntoSeguro(
        datos.dataset.origenLat,
        datos.dataset.origenLng,
        AltokkeMapa.BAGUA
    );
    const destino = destinoReal || AltokkeMapa.puntoSeguro(
        datos.dataset.destinoLat,
        datos.dataset.destinoLng,
        AltokkeMapa.CAJARURO
    );
    const viajeId = datos.dataset.viajeId;
    const estadoViajeUrl = datos.dataset.estadoUrl;
    const pasajeroId = datos.dataset.pasajeroId;

    function pintarEstadoAjax(mensaje, tipo = 'normal') {
        const estado = document.getElementById('buscando-ajax-estado');
        const texto = document.getElementById('buscando-ajax-texto');
        if (!estado || !texto) return;

        estado.setAttribute('data-tipo', tipo);
        texto.textContent = mensaje;
    }

    function detenerPollingEstado() {
        if (!pollingEstado) return;
        window.clearInterval(pollingEstado);
        pollingEstado = null;
    }

    function mostrarErrorViaje(mensaje) {
        detenerPollingEstado();

        const titulo = document.getElementById('buscando-titulo');
        const descripcion = document.querySelector('.buscando-desc');
        const botonCancelar = document.querySelector('#form-cancelar button');

        if (titulo) titulo.textContent = 'Viaje no disponible';
        if (descripcion) descripcion.textContent = mensaje;
        if (botonCancelar) botonCancelar.disabled = true;
        pintarEstadoAjax(mensaje, 'error');
    }

    function actualizarPanelEstado(viaje, conductor = null) {
        const titulo = document.getElementById('buscando-titulo');
        const badge = document.getElementById('buscando-estado-badge');
        const descripcion = document.querySelector('.buscando-desc');

        if (badge && viaje?.estado_label) {
            badge.textContent = `Estado: ${viaje.estado_label}`;
        }

        if (!titulo || !descripcion || !viaje?.estado) return;

        const textos = {
            buscando: {
                titulo: 'Buscando mototaxi cercano...',
                descripcion: 'Te conectamos con el conductor mas cercano disponible en Bagua',
            },
            aceptado: {
                titulo: 'Conductor asignado',
                descripcion: conductor?.nombre
                    ? `${conductor.nombre} acepto tu solicitud. Abriendo el viaje en curso...`
                    : 'Un conductor acepto tu solicitud. Abriendo el viaje en curso...',
            },
            recogiendo: {
                titulo: 'Conductor en camino',
                descripcion: 'El conductor va hacia tu punto de origen.',
            },
            en_curso: {
                titulo: 'Viaje en curso',
                descripcion: 'Tu viaje ya esta activo.',
            },
            completado: {
                titulo: 'Viaje completado',
                descripcion: 'El viaje finalizo correctamente.',
            },
            cancelado: {
                titulo: 'Solicitud cancelada',
                descripcion: 'La solicitud fue cancelada.',
            },
            expirado: {
                titulo: 'Solicitud expirada',
                descripcion: 'No se encontro conductor disponible para este viaje.',
            },
        };

        const contenido = textos[viaje.estado] || textos.buscando;
        titulo.textContent = contenido.titulo;
        descripcion.textContent = contenido.descripcion;
    }

    async function consultarEstadoViaje() {
        if (!estadoViajeUrl || consultandoEstado) return;
        consultandoEstado = true;
        pintarEstadoAjax('Consultando estado...', 'cargando');

        try {
            const respuesta = await fetch(estadoViajeUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!respuesta.ok) {
                mostrarErrorViaje('El viaje no existe o no tienes permiso para consultarlo.');
                return;
            }

            const data = await respuesta.json();
            const viaje = data.viaje || data.data || null;
            if (!data.ok || !viaje) {
                mostrarErrorViaje(data.mensaje || data.message || 'La respuesta del viaje no es valida.');
                return;
            }

            actualizarPanelEstado(viaje, data.conductor || viaje.conductor);
            pintarEstadoAjax('Estado actualizado correctamente', 'ok');

            if (viaje.redirect_url) {
                detenerPollingEstado();
                window.setTimeout(() => {
                    window.location.href = viaje.redirect_url;
                }, 900);
                return;
            }

            if (['completado', 'cancelado', 'expirado'].includes(viaje.estado)) {
                detenerPollingEstado();
                pintarEstadoAjax('Consulta detenida: viaje finalizado', 'ok');
            }
        } catch (error) {
            mostrarErrorViaje('No se pudo consultar el viaje. Actualiza la pagina para intentar nuevamente.');
        } finally {
            consultandoEstado = false;
        }
    }

    function iniciarPollingEstado() {
        if (!estadoViajeUrl || pollingEstado) return;
        consultarEstadoViaje();
        pollingEstado = window.setInterval(consultarEstadoViaje, 7000);
    }

    async function pintarRutaBuscando() {
        const estado = document.getElementById('estado-ruta-buscando');
        const detalle = document.getElementById('detalle-ruta-buscando');

        if (!origenReal || !destinoReal) {
            if (estado) estado.textContent = 'Coordenadas pendientes';
            if (detalle) detalle.textContent = 'No se encontraron puntos validos para este viaje';
            AltokkeMapa.ajustarVista(mapa, [origen, destino]);
            return;
        }

        if (estado) estado.textContent = 'Calculando ruta';

        const ruta = await AltokkeMapa.consultarRuta(origen, destino);
        lineaRuta = AltokkeMapa.dibujarRuta(mapa, lineaRuta, ruta, {
            color: '#2d6a2d',
            weight: 6,
            opacity: 0.9,
        });

        if (lineaRuta) {
            AltokkeMapa.ajustarVista(mapa, [origen, destino], [50, 50]);
        }

        document.getElementById('eta-buscando').textContent = `${ruta.duracion_min || '--'} min`;
        document.getElementById('distancia-buscando').textContent = `${Number(ruta.distancia_km || 0).toFixed(1)} km`;
        if (estado) estado.textContent = ruta.ok ? 'Ruta estimada' : 'Sin ruta disponible';
        if (detalle) detalle.textContent = ruta.ok ? 'Ruta real calculada' : 'Usando linea simple entre puntos';
    }

    document.querySelectorAll('.buscando-tiempo:not(#buscando-estado-badge)')
        .forEach((elemento) => {
            elemento.style.display = 'none';
        });

    mapa = AltokkeMapa.crearMapa('mapa-solicitud-pasajero', origen, 15);
    if (!mapa) return;

    document.getElementById('zoom-in')?.addEventListener('click', () => mapa.zoomIn());
    document.getElementById('zoom-out')?.addEventListener('click', () => mapa.zoomOut());

    if (origenReal) AltokkeMapa.crearMarcador(mapa, origen, 'origen', 'O', 'Tu origen');
    if (destinoReal) AltokkeMapa.crearMarcador(mapa, destino, 'destino', 'D', 'Tu destino');

    pintarRutaBuscando();
    iniciarPollingEstado();

    if (window.Echo && pasajeroId) {
        window.Echo.private(`pasajero.${pasajeroId}`)
            .listen('.ViajeAceptado', (data) => {
                const id = data.viajeId || viajeId;
                if (!id) return;
                window.location.href = `/pasajero/enCurso/${id}`;
            });

        window.Echo.private(`pasajero.${pasajeroId}`)
            .listen('.ViajeActualizado', () => {
            });
    }

    window.addEventListener('beforeunload', detenerPollingEstado);
});
