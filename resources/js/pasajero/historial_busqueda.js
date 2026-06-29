document.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.getElementById('buscarHistorialViaje');
    const lista = document.getElementById('historialLista');
    const estado = document.getElementById('historialBusquedaEstado');
    const contenedor = document.querySelector('.pagina-pasajero-historial');

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
                throw new Error('No se pudo obtener el historial.');
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

        viajes.forEach((viaje)=> {
            lista.appendChild(crearTarjetaViaje(viaje));
        });
    }

    function crearTarjetaViaje(viaje) {
        const enlace = document.createElement('a');
        enlace.href = '#';
        enlace.classList.add('tarjeta-viaje-item');

        const borde = document.createElement('div');
        borde.classList.add('viaje-borde', viaje.borde_clase || 'borde-dorado');

        const cuerpo = document.createElement('div');
        cuerpo.classList.add('viaje-cuerpo');

        const izquierda = document.createElement('div');
        izquierda.classList.add('viaje-detalles-izquierda');
        izquierda.appendChild(crearRuta(viaje));
        izquierda.appendChild(crearMeta(viaje));
        izquierda.appendChild(crearBadge(viaje));

        const derecha = document.createElement('div');
        derecha.classList.add('viaje-detalles-derecha');
        derecha.appendChild(crearPrecio(viaje.precio));
        derecha.appendChild(crearConductor(viaje.conductor));

        if (viaje.calificacion > 0) {
            derecha.appendChild(crearEstrellas(viaje.calificacion));
        }

        cuerpo.appendChild(izquierda);
        cuerpo.appendChild(derecha);
        enlace.appendChild(borde);
        enlace.appendChild(cuerpo);

        return enlace;
    }

    function crearRuta(viaje) {
        const contenedorRuta = document.createElement('div');
        contenedorRuta.classList.add('puntos-ruta-contenedor');

        contenedorRuta.appendChild(crearLineaRuta('verde', viaje.origen || '-'));
        contenedorRuta.appendChild(crearLineaRuta('rojo', viaje.destino || '-'));

        return contenedorRuta;
    }

    function crearLineaRuta(color, texto) {
        const linea = document.createElement('div');
        linea.classList.add('ruta-linea-punto');

        const punto = document.createElement('span');
        punto.classList.add('dot', color);

        const direccion = document.createElement('span');
        direccion.classList.add('direccion-texto-item');
        direccion.textContent = texto;

        linea.appendChild(punto);
        linea.appendChild(direccion);

        return linea;
    }

    function crearMeta(viaje) {
        const meta = document.createElement('div');
        meta.classList.add('viaje-meta-info');

        const fecha = document.createElement('span');
        fecha.textContent = `📅 ${viaje.fecha || '—'}`;

        const separadorUno = document.createElement('span');
        separadorUno.classList.add('separador');
        separadorUno.textContent = '•';

        const distancia = document.createElement('span');
        distancia.textContent = `📏 ${viaje.distancia || '—'}`;

        const separadorDos = document.createElement('span');
        separadorDos.classList.add('separador');
        separadorDos.textContent = '•';

        const tiempo = document.createElement('span');
        tiempo.textContent = `⏱️ ${viaje.tiempo || '—'}`;

        meta.appendChild(fecha);
        meta.appendChild(separadorUno);
        meta.appendChild(distancia);
        meta.appendChild(separadorDos);
        meta.appendChild(tiempo);

        return meta;
    }

    function crearBadge(viaje) {
        const contenedorBadge = document.createElement('div');
        contenedorBadge.classList.add('badge-estado-container');

        const badge = document.createElement('span');
        badge.classList.add('badge', viaje.badge_clase || 'badge-gris');
        badge.textContent = viaje.estado_texto || 'Pendiente';

        contenedorBadge.appendChild(badge);
        return contenedorBadge;
    }

    function crearPrecio(precio) {
        const precioContenedor = document.createElement('div');
        precioContenedor.classList.add('viaje-precio');
        precioContenedor.textContent = `S/ ${Number(precio || 0).toFixed(2)}`;
        return precioContenedor;
    }

    function crearConductor(nombreConductor) {
        const conductor = document.createElement('div');
        conductor.classList.add('viaje-conductor-info');

        const icono = document.createElement('span');
        icono.classList.add('conductor-avatar-icon');
        icono.textContent = '👤';

        const nombre = document.createElement('span');
        nombre.classList.add('conductor-nombre');
        nombre.textContent = nombreConductor || '—';

        conductor.appendChild(icono);
        conductor.appendChild(nombre);

        return conductor;
    }

    function crearEstrellas(calificacion) {
        const estrellas = document.createElement('div');
        estrellas.classList.add('viaje-estrellas-puntuacion');

        const cantidad = Number(calificacion || 0);
        estrellas.textContent = '★'.repeat(cantidad) + '☆'.repeat(5 - cantidad);

        return estrellas;
    }

    function pintarMensaje(mensaje) {
        lista.textContent = '';

        const vacio = document.createElement('div');
        vacio.classList.add('historia-vacio');

        const icono = document.createElement('div');
        icono.classList.add('estado-vacio-icono');
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
})