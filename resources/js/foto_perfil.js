function obtenerMensajeDeError(datos, estadoHttp) {
    if (datos?.errors) {
        return Object.values(datos.errors)
            .flat()
            .join(' ');
    }

    return datos?.message || `Error HTTP ${estadoHttp}`;
}

function iniciarCargaAsincronaDeFoto() {
    const formulario = document.querySelector(
        '[data-profile-photo-form]'
    );

    if (!formulario) {
        return;
    }

    if (formulario.dataset.fetchReady === 'true') {
        return;
    }

    formulario.dataset.fetchReady = 'true';

    const input = formulario.querySelector(
        '[data-profile-photo-input]'
    );

    const boton = formulario.querySelector(
        '[data-profile-photo-button]'
    );

    const estado = document.querySelector(
        '[data-profile-photo-status]'
    );

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const archivo = input?.files?.[0];

        if (!archivo) {
            estado.textContent = 'Seleccione una fotografía.';
            return;
        }

        const tiposPermitidos = [
            'image/jpeg',
            'image/png',
        ];

        if (!tiposPermitidos.includes(archivo.type)) {
            estado.textContent =
                'Solo se permiten imágenes JPG o PNG.';
            return;
        }

        if (archivo.size > 2 * 1024 * 1024) {
            estado.textContent =
                'La imagen no debe superar los 2 MB.';
            return;
        }

        const textoOriginal = boton.textContent;

        boton.disabled = true;
        boton.textContent = 'Subiendo...';
        estado.textContent = 'Procesando fotografía...';

        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',

                body: new FormData(formulario),

                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },

                credentials: 'same-origin',
            });

            const datos = await respuesta
                .json()
                .catch(() => ({
                    ok: false,
                    message:
                        'El servidor no devolvió una respuesta JSON válida.',
                }));

            if (!respuesta.ok || !datos.ok) {
                throw new Error(
                    obtenerMensajeDeError(
                        datos,
                        respuesta.status
                    )
                );
            }

            const fotoUrl = datos.data?.foto_url;

            if (!fotoUrl) {
                throw new Error(
                    'La respuesta no contiene la URL de la fotografía.'
                );
            }
            const imagenes = Array.from(
                document.querySelectorAll(
                    '[data-profile-photo-image]'
                )
            );

            const placeholders = Array.from(
                document.querySelectorAll(
                    '[data-profile-photo-placeholder]'
                )
            );

            if (imagenes.length === 0) {
                throw new Error(
                    'No se encontró ningún avatar para actualizar.'
                );
            }

            const nuevaUrl = new URL(
                fotoUrl,
                window.location.origin
            );

            nuevaUrl.searchParams.set(
                'v',
                Date.now().toString()
            );

            const urlFinal = nuevaUrl.toString();

            const resultados = await Promise.all(
                imagenes.map((imagen) => {
                    return new Promise((resolve) => {
                        const imagenCargada = () => {
                            imagen.hidden = false;

                            imagen.style.removeProperty('display');

                            resolve(true);
                        };

                        const imagenConError = () => {
                            imagen.hidden = true;
                            imagen.style.display = 'none';

                            console.error(
                                'No se pudo cargar el avatar:',
                                urlFinal
                            );

                            resolve(false);
                        };

                        imagen.addEventListener(
                            'load',
                            imagenCargada,
                            { once: true }
                        );

                        imagen.addEventListener(
                            'error',
                            imagenConError,
                            { once: true }
                        );

                        imagen.src = urlFinal;
                    });
                })
            );

            const algunaImagenCargo = resultados.some(
                (resultado) => resultado === true
            );

            if (!algunaImagenCargo) {
                throw new Error(
                    'La foto se guardó, pero no pudo mostrarse en la interfaz.'
                );
            }

            placeholders.forEach((placeholder) => {
                placeholder.hidden = true;
                placeholder.style.display = 'none';
            });

            estado.textContent =
                datos.message ||
                'Foto de perfil actualizada correctamente.';

            formulario.reset();
        } catch (error) {
            console.error(
                'Error al actualizar la fotografía:',
                error
            );

            estado.textContent =
                error instanceof Error
                    ? error.message
                    : 'No se pudo actualizar la fotografía.';
        } finally {
            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        iniciarCargaAsincronaDeFoto,
        { once: true }
    );
} else {
    iniciarCargaAsincronaDeFoto();
}