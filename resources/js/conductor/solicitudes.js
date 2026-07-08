document.addEventListener('DOMContentLoaded', function () {
    const lista = document.getElementById('solicitudes-lista');
    const estadoTexto = document.getElementById('solicitudes-ajax-texto');

    if (!lista || !estadoTexto) {
        return;
    }

    if (lista.dataset.pollingSolicitudesActivo === '1') {
        return;
    }
    lista.dataset.pollingSolicitudesActivo = '1';

    const INTERVALO_SOLICITUDES_MS = 7000;
    const endpoint = lista.dataset.endpoint;
    const aceptarUrl = lista.dataset.aceptarUrl;
    const csrfToken = lista.dataset.csrfToken;
    const puedeTomarInicial = lista.dataset.puedeTomar === '1';
    let cargando = false;
    let pollingSolicitudes = null;

    function crearElemento(etiqueta, clase, texto = '') {
        const elemento = document.createElement(etiqueta);
        elemento.className = clase;
        elemento.textContent = texto;
        return elemento;
    }

    function pintarEstado(mensaje, tipo = 'normal') {
        estadoTexto.textContent = mensaje;
        estadoTexto.closest('.ajax-estado')?.setAttribute('data-tipo', tipo);
    }

    function pintarVacio(mensaje) {
        const tarjeta = crearElemento('div', 'tarjeta estado-vacio');
        tarjeta.appendChild(crearElemento('p', '', mensaje));
        lista.replaceChildren(tarjeta);
    }

    function tarjetaSolicitud(solicitud, puedeTomar) {
        const tarjeta = crearElemento('div', 'tarjeta solicitud-card');
        tarjeta.dataset.viajeId = String(solicitud.id ?? '');

        const cuerpo = crearElemento('div', 'viaje-cuerpo solicitud-cuerpo');
        const informacion = crearElemento('div', 'solicitud-info');
        informacion.appendChild(crearElemento(
            'div',
            'viaje-ruta solicitud-ruta',
            `${solicitud.origen || 'Sin origen'} -> ${solicitud.destino || 'Sin destino'}`
        ));
        informacion.appendChild(crearElemento(
            'div',
            'viaje-meta',
            `Pasajero: ${solicitud.pasajero || 'Pasajero'} | Pago: ${solicitud.metodo_pago || '-'} | ${solicitud.tipo_servicio || '-'}`
        ));
        informacion.appendChild(crearElemento(
            'div',
            'viaje-meta solicitud-tiempo',
            `${solicitud.distancia || '-'} | ${solicitud.tiempo || '-'} | ${solicitud.fecha || '-'}`
        ));

        const acciones = crearElemento('div', 'solicitud-acciones');
        acciones.appendChild(crearElemento('div', 'solicitud-tarifa', `S/ ${solicitud.tarifa || '0.00'}`));

        if (puedeTomar) {
            const formulario = document.createElement('form');
            formulario.method = 'POST';
            formulario.action = aceptarUrl;

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrfToken;

            const viajeId = document.createElement('input');
            viajeId.type = 'hidden';
            viajeId.name = 'id_viaje';
            viajeId.value = String(solicitud.id ?? '');

            formulario.append(token, viajeId, crearElemento('button', 'btn btn-verde btn-sm', 'Aceptar'));
            acciones.appendChild(formulario);
        } else {
            acciones.appendChild(crearElemento(
                'span',
                'solicitud-bloqueada',
                'Billetera o verificacion pendiente'
            ));
        }

        cuerpo.append(informacion, acciones);
        tarjeta.appendChild(cuerpo);

        return tarjeta;
    }

    async function cargarSolicitudes() {
        if (cargando || document.hidden) return;
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
                if (respuesta.status === 429) {
                    pintarEstado('Demasiadas consultas. Se esperara antes de volver a actualizar.', 'cargando');
                    return;
                }

                if ([401, 403].includes(respuesta.status) && pollingSolicitudes) {
                    window.clearInterval(pollingSolicitudes);
                    pollingSolicitudes = null;
                }
                throw new Error('No se pudo consultar el servidor');
            }

            const data = await respuesta.json();
            if (!data.ok) {
                throw new Error(data.message || 'Respuesta no valida');
            }

            const solicitudes = Array.isArray(data.data?.solicitudes)
                ? data.data.solicitudes
                : Array.isArray(data.solicitudes)
                    ? data.solicitudes
                    : [];
            const puedeTomar = data.data?.puede_tomar_viajes ?? data.puede_tomar_viajes ?? puedeTomarInicial;

            if (!solicitudes.length) {
                pintarVacio('No hay solicitudes pendientes en este momento.');
            } else {
                lista.replaceChildren();
                solicitudes.forEach((solicitud) => {
                    lista.appendChild(tarjetaSolicitud(solicitud, puedeTomar));
                });
            }

            pintarEstado(`Solicitudes actualizadas: ${solicitudes.length}`, 'ok');
        } catch (error) {
            pintarEstado('No se pudo actualizar. Se mostrara la ultima lista disponible.', 'error');
        } finally {
            cargando = false;
        }
    }

    function detenerPollingSolicitudes() {
        if (!pollingSolicitudes) return;
        window.clearInterval(pollingSolicitudes);
        pollingSolicitudes = null;
    }

    function iniciarPollingSolicitudes() {
        if (!endpoint || pollingSolicitudes || document.hidden) return;
        cargarSolicitudes();
        pollingSolicitudes = window.setInterval(cargarSolicitudes, INTERVALO_SOLICITUDES_MS);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            detenerPollingSolicitudes();
            pintarEstado('Actualizacion pausada mientras la pestana esta oculta.', 'normal');
            return;
        }

        iniciarPollingSolicitudes();
    });

    window.addEventListener('beforeunload', detenerPollingSolicitudes);
    iniciarPollingSolicitudes();
});
