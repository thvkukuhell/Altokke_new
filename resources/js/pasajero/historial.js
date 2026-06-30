document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-comprobante').forEach((boton) => {
        boton.addEventListener('click', () => {
            const url = boton.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });
    });

    const paginaHistorial = document.querySelector('.pagina-pasajero-historial');
    const inputBusqueda = document.getElementById('buscar-viajes');
    const estadoBusqueda = document.getElementById('estado-busqueda');
    const resultadosBusqueda = document.getElementById('resultados-busqueda');
    const contenidoInicial = document.getElementById('historial-contenido-inicial');

    if (!paginaHistorial || !inputBusqueda || !estadoBusqueda || !resultadosBusqueda || !contenidoInicial) {
        return;
    }

    const urlBusqueda = paginaHistorial.dataset.urlBusqueda;

    function agregarTexto(contenedor, etiqueta, clase, texto) {
        const elemento = document.createElement(etiqueta);
        elemento.className = clase;
        elemento.textContent = texto;
        contenedor.appendChild(elemento);
        return elemento;
    }

    function crearRuta(contenedor, etiqueta, texto) {
        const ruta = agregarTexto(contenedor, 'div', 'viaje-ruta', '');
        agregarTexto(ruta, 'span', 'viaje-ruta-label', etiqueta);
        agregarTexto(ruta, 'p', 'viaje-ruta-texto', texto || 'Sin informacion');
    }

    function agregarDato(contenedor, texto, claseExtra = '') {
        agregarTexto(
            contenedor,
            'span',
            `viaje-meta-item ${claseExtra}`.trim(),
            texto
        );
    }

    function crearTarjetaViaje(viaje) {
        const tarjeta = document.createElement('article');
        tarjeta.className = `tarjeta-viaje-item tarjeta-viaje-item--${viaje.estado || ''}`;

        const cuerpo = agregarTexto(tarjeta, 'div', 'viaje-cuerpo', '');
        const principal = agregarTexto(cuerpo, 'div', 'viaje-main', '');
        const parteSuperior = agregarTexto(principal, 'div', 'viaje-main-top', '');
        const rutas = agregarTexto(parteSuperior, 'div', 'viaje-rutas', '');
        crearRuta(rutas, 'Origen', viaje.origen);
        crearRuta(rutas, 'Destino', viaje.destino);

        const estadoContenedor = agregarTexto(parteSuperior, 'div', 'viaje-state-pill', '');
        agregarTexto(estadoContenedor, 'span', 'badge', viaje.estado_label || viaje.estado || '');

        const datos = agregarTexto(principal, 'div', 'viaje-meta', '');
        agregarDato(datos, `Fecha ${viaje.fecha || 'Sin fecha'}`);
        agregarDato(datos, `Conductor: ${viaje.conductor?.nombre || 'Sin asignar'}`);
        agregarDato(datos, `Pago: ${viaje.metodo_pago || 'Sin especificar'}`);

        if (Number(viaje.calificacion) > 0) {
            agregarDato(datos, `Calificacion ${viaje.calificacion}/5`, 'viaje-meta-item-rating');
        }

        const resumen = agregarTexto(cuerpo, 'div', 'viaje-summary', '');
        const precio = agregarTexto(resumen, 'div', 'viaje-price-block', '');
        agregarTexto(precio, 'span', 'viaje-precio-label', viaje.precio_label || 'Tarifa');
        agregarTexto(precio, 'div', 'viaje-precio', `S/ ${Number(viaje.precio || 0).toFixed(2)}`);

        if (viaje.comprobante_url) {
            const enlace = agregarTexto(resumen, 'a', 'btn btn-outline btn-comprobante', 'Descargar PDF');
            enlace.href = viaje.comprobante_url;
        }

        return tarjeta;
    }

    function mostrarViajes(viajes) {
        resultadosBusqueda.replaceChildren();

        if (viajes.length === 0) {
            const vacio = agregarTexto(resultadosBusqueda, 'div', 'historial-vacio', '');
            agregarTexto(vacio, 'p', '', 'No se encontraron viajes con ese texto.');
            return;
        }

        viajes.forEach((viaje) => {
            resultadosBusqueda.appendChild(crearTarjetaViaje(viaje));
        });
    }

    async function buscarViajes(texto) {
        try {
            estadoBusqueda.textContent = 'Buscando viajes...';

            const response = await fetch(`${urlBusqueda}?texto=${encodeURIComponent(texto)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('No se pudo buscar en el historial');
            }

            const respuesta = await response.json();
            const viajes = Array.isArray(respuesta.data) ? respuesta.data : [];

            mostrarViajes(viajes);
            estadoBusqueda.textContent = `${viajes.length} viaje(s) encontrado(s)`;
        } catch (error) {
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
