document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.querySelector('.pagina-conductor-historial');
    const inputBuscar = document.getElementById('buscarHistorialConductor');
    const estado = document.getElementById('historialConductorBusquedaEstado');
    const contenidoInicial = document.getElementById('historialConductorContenidoInicial');
    const listaResultados = document.getElementById('historialConductorLista');

    if (!contenedor || !inputBuscar || !estado || !contenidoInicial || !listaResultados) {
        console.error('[historial] Faltan elementos del DOM.')
        return;
    }

    const urlBuscar = contenedor.dataset.urlBusqueda;
    let filtroActual = 'todos';
    
    let timer = null;
    let controlador = null;

    inputBuscar.addEventListener('input', () => {
        clearTimeout(timer);
        
        const texto = inputBuscar.value.trim();

        if (texto === '' && filtroActual === 'todos') {
            if (controlador) { controlador.abort(); controlador=null;}
            mostrarContenidoInicial();
            pintarEstado('');
            return;
        }

        pintarEstado('Escribiendo...');

        timer = setTimeout(() => {
            buscarViajes(texto);
        }, 400);
    });

    const botonesFiltro = document.querySelectorAll('.filtro-conductor-btn');

    botonesFiltro.forEach((boton) => {
        boton.addEventListener('click', () => {
            clearTimeout(timer);
            if (controlador) {controlador.abort(); controlador=null;}

            botonesFiltro.forEach((b) => b.classList.remove('activo'));
            boton.classList.add('activo');

            filtroActual = boton.dataset.filtro || 'todos';

            const texto = inputBuscar.value.trim();

            if (filtroActual === 'todos' && texto === '') {
                mostrarContenidoInicial();
                pintarEstado('');
                return;
            }

            pintarEstado(`Filtrando por ${filtroActual}...`);
            buscarViajes(texto);
        });
    });

    async function buscarViajes(texto) {
        if (controlador) {
            controlador.abort();
            controlador = null;
        }

        controlador = new AbortController();

        try {
            pintarEstado('Buscando viajes...');

            const parametros = new URLSearchParams({
                texto: texto,
                filtro: filtroActual,
            });
            const url        = `${urlBuscar}?${parametros.toString()}`;

            const respuesta = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controlador.signal,
            });

            if (!respuesta.ok) {
                throw new Error(`Error HTTP ${respuesta.status}`);
            }

            const resultado = await respuesta.json();

            const viajes = Array.isArray(resultado.data)
                ? resultado.data
                : [];

            pintarViajes(viajes);
            pintarEstado(`${resultado.total ?? viajes.length} resultado(s) encontrado(s).`);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('[historial] Error en fetch:', error);
            pintarMensaje('Ocurrió un error al buscar viajes. Inténtalo nuevamente.');
            pintarEstado('Error en la búsqueda.');
        } finally {
            controlador = null;
        }
    }

    function mostrarContenidoInicial() {
        contenidoInicial.hidden = false;
        listaResultados.hidden = true;
        listaResultados.textContent = '';
    }

    function mostrarResultados() {
        contenidoInicial.hidden = true;
        listaResultados.hidden = false;
    }

    function pintarViajes(viajes) {
        listaResultados.textContent = '';
        mostrarResultados();

        if (!viajes.length) {
            pintarMensaje('No se encontraron viajes con ese texto.');
            return;
        }

        viajes.forEach((viaje) => {
            listaResultados.appendChild(crearTarjetaViaje(viaje));
        });
    }

    function crearTarjetaViaje(viaje) {
        const item = document.createElement('div');
        item.classList.add('viaje-item');

        const borde = document.createElement('div');
        borde.classList.add('viaje-borde', viaje.borde_clase || obtenerClaseBorde(viaje.estado || viaje.estado_viaje));

        const cuerpo = document.createElement('div');
        cuerpo.classList.add('viaje-cuerpo');

        const izquierda = document.createElement('div');

        const ruta = document.createElement('div');
        ruta.classList.add('viaje-ruta');

        const origen = obtenerTexto(viaje.origen, viaje.origen_texto, '—');
        const destino = obtenerTexto(viaje.destino, viaje.destino_texto, '—');

        const textoRutaOrigen = document.createElement('span');
        textoRutaOrigen.textContent = origen;

        const flecha = document.createElement('span');
        flecha.style.color = 'var(--gray-lite)';
        flecha.style.fontWeight = '400';
        flecha.textContent = ' → ';

        const textoRutaDestino = document.createElement('span');
        textoRutaDestino.textContent = destino;

        ruta.appendChild(textoRutaOrigen);
        ruta.appendChild(flecha);
        ruta.appendChild(textoRutaDestino);

        const meta = document.createElement('div');
        meta.classList.add('viaje-meta');

        const pasajero = obtenerNombrePersona(viaje.pasajero, 'Pasajero');
        const fecha = obtenerTexto(viaje.fecha, '—');
        const distancia = viaje.distancia || formatearDistancia(viaje.distancia_km);
        const tiempo = viaje.tiempo || formatearTiempo(viaje.tiempo_estimado_min);
        const pago = obtenerTexto(viaje.metodo_pago, '—');
        const servicio = obtenerTexto(viaje.tipo_servicio, '—');

        meta.textContent = `${fecha} · ${distancia} · ${tiempo} · ${pago} · ${servicio} · Pasajero: ${pasajero}`;

        const badgeContenedor = document.createElement('div');
        badgeContenedor.style.marginTop = '5px';

        const badge = document.createElement('span');
        badge.classList.add('badge', viaje.badge_clase || obtenerClaseBadge(viaje.estado || viaje.estado_viaje));
        badge.textContent = viaje.estado_texto || viaje.estado_label || formatearEstado(viaje.estado || viaje.estado_viaje || 'pendiente');

        badgeContenedor.appendChild(badge);

        izquierda.appendChild(ruta);
        izquierda.appendChild(meta);
        izquierda.appendChild(badgeContenedor);

        const derecha = document.createElement('div');
        derecha.classList.add('viaje-derecha');

        const precio = document.createElement('div');
        precio.classList.add('viaje-precio');
        precio.textContent = `S/ ${Number(viaje.precio || viaje.tarifa_final || viaje.tarifa_estimada || 0).toFixed(2)}`;

        derecha.appendChild(precio);

        const calificacion = Number(viaje.calificacion || viaje.puntuacion || 0);

        if (calificacion > 0) {
            const estrellas = document.createElement('div');
            estrellas.classList.add('viaje-estrellas');
            estrellas.textContent = '★'.repeat(calificacion) + '☆'.repeat(5 - calificacion);
            derecha.appendChild(estrellas);
        }

        const estadoViaje = viaje.estado || viaje.estado_viaje;

        if (estadoViaje === 'completado' && viaje.comprobante_url) {
            const enlace = document.createElement('a');
            enlace.href = viaje.comprobante_url;
            enlace.classList.add('btn', 'btn-outline', 'btn-sm');
            enlace.style.marginTop = '10px';
            enlace.textContent = 'Descargar PDF';
            derecha.appendChild(enlace);
        }

        cuerpo.appendChild(izquierda);
        cuerpo.appendChild(derecha);

        item.appendChild(borde);
        item.appendChild(cuerpo);

        return item;
    }

    function pintarMensaje(mensaje) {
        listaResultados.textContent = '';
        mostrarResultados();

        const vacio = document.createElement('div');
        vacio.classList.add('tarjeta', 'estado-vacio');

        const texto = document.createElement('p');
        texto.textContent = mensaje;

        vacio.appendChild(texto);
        listaResultados.appendChild(vacio);
    }

    function pintarEstado(mensaje) {
        estado.textContent = mensaje;
    }

    function obtenerTexto(...valores) {
        for (const valor of valores) {
            if (valor !== null && valor !== undefined && String(valor).trim() !== '') {
                return String(valor);
            }
        }

        return '—';
    }

    function obtenerNombrePersona(valor, textoPorDefecto) {
        if (!valor) {
            return textoPorDefecto;
        }

        if (typeof valor === 'string') {
            return valor;
        }

        if (typeof valor === 'object' && valor.nombre) {
            return valor.nombre;
        }

        return textoPorDefecto;
    }

    function formatearDistancia(valor) {
        if (!valor) {
            return '—';
        }

        return `${valor} km`;
    }

    function formatearTiempo(valor) {
        if (!valor) {
            return '—';
        }

        return `${valor} min`;
    }

    function formatearEstado(estado) {
        return String(estado)
            .replaceAll('_', ' ')
            .replace(/^\w/, letra => letra.toUpperCase());
    }

    function obtenerClaseBadge(estado) {
        if (estado === 'completado') {
            return 'badge-verde';
        }

        if (estado === 'cancelado') {
            return 'badge-rojo';
        }

        return 'badge-gris';
    }

    function obtenerClaseBorde(estado) {
        if (estado === 'completado') {
            return 'borde-verde';
        }

        if (estado === 'cancelado') {
            return 'borde-rojo';
        }

        return 'borde-dorado';
    }
});
