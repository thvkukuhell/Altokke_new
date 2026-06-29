document.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.getElementById('buscarHistorialConductor');
    const lista = document.getElementById('historialConductorLista');
    const estado = document.getElementById('historialConductorBusquedaEstado');
    const contenedor = document.querySelector('.pagina-conductor-historial');

    if (!inputBuscar || !lista || !contenedor) {
        return;
    }

    const urlBuscar = contenedor.dataset.urlBuscar;
    const filtroActual = contenedor.dataset.filtroActual || 'todos';
    let timer = null;
    let controlador = null;

    inputBuscar.addEventListener('input', () => {
        clearTimeout(timer);
        pintarEstado('Escribiendo...');

        timer = setTimeout(() => {
            buscarViajes(inputBuscar.value.trim());
        }, 400);
    });

    async function buscarViajes(texto) {
        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();

        try {
            pintarEstado('Buscando viajes...');

            const parametros = new URLSearchParams({
                q: texto,
                filtro: filtroActual,
            });

            const respuesta = await fetch(`${urlBuscar}?${parametros.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controlador.signal,
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo obtener el historial del conductor.');
            }

            const resultado = await respuesta.json();
            pintarViajes(resultado.data || []);
            pintarEstado(`${resultado.total || 0} resultado(s) encontrado(s).`);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            pintarMensaje('Ocurrió un error al buscar viajes. Inténtalo nuevamente.');
            pintarEstado('Error en la búsqueda.');
        }
    }

    function pintarViajes(viajes) {
        lista.textContent = '';

        if (!viajes.length) {
            pintarMensaje('No se encontraron viajes con ese texto.');
            return;
        }

        viajes.forEach((viaje) => {
            lista.appendChild(crearTarjetaViaje(viaje));
        });
    }

    function crearTarjetaViaje(viaje) {
        const tarjeta = document.createElement('article');
        tarjeta.classList.add('conductor-viaje-card');

        const borde = document.createElement('div');
        borde.classList.add('viaje-borde', viaje.borde_clase || 'borde-dorado');

        const contenido = document.createElement('div');
        contenido.classList.add('conductor-viaje-contenido');

        const izquierda = document.createElement('div');
        izquierda.classList.add('conductor-viaje-info');
        izquierda.appendChild(crearRuta(viaje));
        izquierda.appendChild(crearMeta(viaje));
        izquierda.appendChild(crearBadge(viaje));

        const derecha = document.createElement('div');
        derecha.classList.add('conductor-viaje-resumen');
        derecha.appendChild(crearPrecio(viaje.precio));
        derecha.appendChild(crearDetalleCorto('👤', viaje.pasajero || 'Pasajero'));

        if (Number(viaje.calificacion || 0) > 0) {
            derecha.appendChild(crearEstrellas(viaje.calificacion));
        }

        contenido.appendChild(izquierda);
        contenido.appendChild(derecha);
        tarjeta.appendChild(borde);
        tarjeta.appendChild(contenido);

        return tarjeta;
    }

    function crearRuta(viaje) {
        const ruta = document.createElement('div');
        ruta.classList.add('conductor-ruta');

        ruta.appendChild(crearPuntoRuta('verde', viaje.origen || '—'));
        ruta.appendChild(crearPuntoRuta('rojo', viaje.destino || '—'));

        return ruta;
    }

    function crearPuntoRuta(color, texto) {
        const fila = document.createElement('div');
        fila.classList.add('conductor-ruta-fila');

        const punto = document.createElement('span');
        punto.classList.add('dot', color);

        const direccion = document.createElement('span');
        direccion.classList.add('conductor-direccion');
        direccion.textContent = texto;

        fila.appendChild(punto);
        fila.appendChild(direccion);

        return fila;
    }

    function crearMeta(viaje) {
        const meta = document.createElement('div');
        meta.classList.add('conductor-viaje-meta');

        meta.appendChild(crearDetalleCorto('📅', viaje.fecha || '—'));
        meta.appendChild(crearDetalleCorto('📏', viaje.distancia || '—'));
        meta.appendChild(crearDetalleCorto('⏱️', viaje.tiempo || '—'));
        meta.appendChild(crearDetalleCorto('💳', viaje.metodo_pago || '—'));
        meta.appendChild(crearDetalleCorto('🛺', viaje.tipo_servicio || '—'));

        return meta;
    }

    function crearDetalleCorto(icono, texto) {
        const item = document.createElement('span');
        item.classList.add('meta-chip');
        item.textContent = `${icono} ${texto}`;
        return item;
    }

    function crearBadge(viaje) {
        const contenedor = document.createElement('div');
        contenedor.classList.add('badge-estado-container');

        const badge = document.createElement('span');
        badge.classList.add('badge', viaje.badge_clase || 'badge-gris');
        badge.textContent = viaje.estado_texto || 'Pendiente';

        contenedor.appendChild(badge);
        return contenedor;
    }

    function crearPrecio(precio) {
        const precioContenedor = document.createElement('div');
        precioContenedor.classList.add('conductor-viaje-precio');
        precioContenedor.textContent = `S/ ${Number(precio || 0).toFixed(2)}`;
        return precioContenedor;
    }

    function crearEstrellas(calificacion) {
        const estrellas = document.createElement('div');
        estrellas.classList.add('conductor-viaje-estrellas');

        const cantidad = Math.max(0, Math.min(5, Number(calificacion || 0)));
        estrellas.textContent = '★'.repeat(cantidad) + '☆'.repeat(5 - cantidad);

        return estrellas;
    }

    function pintarMensaje(mensaje) {
        lista.textContent = '';

        const vacio = document.createElement('div');
        vacio.classList.add('conductor-estado-vacio');

        const icono = document.createElement('div');
        icono.classList.add('conductor-estado-vacio-icono');
        icono.textContent = '🔎';

        const texto = document.createElement('p');
        texto.textContent = mensaje;

        vacio.appendChild(icono);
        vacio.appendChild(texto);
        lista.appendChild(vacio);
    }

    function pintarEstado(mensaje) {
        if (estado) {
            estado.textContent = mensaje;
        }
    }
});
