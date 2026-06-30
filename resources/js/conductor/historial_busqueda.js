document.addEventListener('DOMContentLoaded', function () {
    const pagina = document.querySelector('.pagina-conductor-historial');
    const inputBusqueda = document.getElementById('buscarHistorialConductor');
    const estadoBusqueda = document.getElementById('historialConductorBusquedaEstado');
    const resultadosBusqueda = document.getElementById('historialConductorLista');
    const contenidoInicial = document.getElementById('historial-conductor-contenido-inicial');

    if (!pagina || !inputBusqueda || !estadoBusqueda || !resultadosBusqueda || !contenidoInicial) {
        return;
    }

    const urlBusqueda = pagina.dataset.urlBuscar;

    function agregarTexto(contenedor, etiqueta, clase, texto) {
        const elemento = document.createElement(etiqueta);
        if (clase) elemento.className = clase;
        elemento.textContent = texto;
        contenedor.appendChild(elemento);
        return elemento;
    }

    function bordeClase(estado) {
        if (estado === 'completado') return 'borde-verde';
        if (estado === 'cancelado') return 'borde-rojo';
        return 'borde-dorado';
    }

    function badgeClase(estado) {
        if (estado === 'completado') return 'badge-verde';
        if (estado === 'cancelado') return 'badge-rojo';
        return 'badge-gris';
    }

    function crearTarjetaViaje(viaje) {
        const tarjeta = document.createElement('div');
        tarjeta.className = 'viaje-item';

        const borde = document.createElement('div');
        borde.className = `viaje-borde ${bordeClase(viaje.estado)}`;
        tarjeta.appendChild(borde);

        const cuerpo = agregarTexto(tarjeta, 'div', 'viaje-cuerpo', '');

        const izquierda = document.createElement('div');
        cuerpo.appendChild(izquierda);

        agregarTexto(izquierda, 'div', 'viaje-ruta', `${viaje.origen || 'Sin origen'} → ${viaje.destino || 'Sin destino'}`);
        agregarTexto(izquierda, 'div', 'viaje-meta', `Pasajero: ${viaje.pasajero?.nombre || 'Sin asignar'} - ${viaje.fecha || 'Sin fecha'}`);

        const badgeContenedor = agregarTexto(izquierda, 'div', 'viaje-badge', '');
        agregarTexto(badgeContenedor, 'span', `badge ${badgeClase(viaje.estado)}`, viaje.estado_label || viaje.estado || '');

        const derecha = agregarTexto(cuerpo, 'div', 'viaje-derecha', '');
        agregarTexto(derecha, 'div', 'viaje-precio', `S/ ${Number(viaje.precio || 0).toFixed(2)}`);

        const calificacion = Number(viaje.calificacion || 0);
        if (calificacion > 0) {
            const estrellas = '★'.repeat(calificacion) + '☆'.repeat(5 - calificacion);
            agregarTexto(derecha, 'div', 'viaje-estrellas', estrellas);
        }

        if (viaje.estado === 'completado' && viaje.comprobante_url) {
            const enlace = agregarTexto(derecha, 'a', 'btn btn-outline btn-sm', 'Descargar PDF');
            enlace.href = viaje.comprobante_url;
        }

        return tarjeta;
    }

    function mostrarViajes(viajes) {
        resultadosBusqueda.replaceChildren();

        if (viajes.length === 0) {
            agregarTexto(resultadosBusqueda, 'p', 'historial-vacio', 'No se encontraron viajes con ese texto.');
            return;
        }

        viajes.forEach((viaje) => {
            resultadosBusqueda.appendChild(crearTarjetaViaje(viaje));
        });
    }

    let controlador = null;

    async function buscarViajes(texto) {
        if (controlador) {
            controlador.abort();
        }
        controlador = new AbortController();

        try {
            estadoBusqueda.textContent = 'Buscando viajes...';

            const response = await fetch(`${urlBusqueda}?texto=${encodeURIComponent(texto)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controlador.signal,
            });

            if (!response.ok) {
                throw new Error('No se pudo buscar en el historial');
            }

            const respuesta = await response.json();
            const viajes = Array.isArray(respuesta.data) ? respuesta.data : [];

            mostrarViajes(viajes);
            estadoBusqueda.textContent = `${viajes.length} viaje(s) encontrado(s)`;
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            resultadosBusqueda.replaceChildren();
            estadoBusqueda.textContent = error.message;
        }
    }

    let tiempoBusqueda = null;

    inputBusqueda.addEventListener('input', function () {
        clearTimeout(tiempoBusqueda);
        const texto = inputBusqueda.value.trim();

        if (texto === '') {
            resultadosBusqueda.replaceChildren();
            resultadosBusqueda.hidden = true;
            contenidoInicial.hidden = false;
            estadoBusqueda.textContent = '';
            return;
        }

        contenidoInicial.hidden = true;
        resultadosBusqueda.hidden = false;

        tiempoBusqueda = setTimeout(function () {
            buscarViajes(texto);
        }, 400);
    });
});
