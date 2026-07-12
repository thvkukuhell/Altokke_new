export async function altokkeFetchJson(url, options = {}) {
    const timeoutMs = options.timeoutMs ?? 10000;
    const controller = options.controller ?? new AbortController();
    const timer = window.setTimeout(() => controller.abort(), timeoutMs);
    const method = (options.method || 'GET').toUpperCase();
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {}),
    };

    if (!['GET', 'HEAD'].includes(method) && !headers['X-CSRF-TOKEN']) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) headers['X-CSRF-TOKEN'] = token;
    }

    try {
        const response = await fetch(url, {
            ...options,
            method,
            headers,
            signal: controller.signal,
        });
        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await response.json() : null;

        if (!response.ok) {
            const error = new Error(data?.mensaje || data?.message || messageForStatus(response.status));
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    } finally {
        window.clearTimeout(timer);
    }
}

export function messageForStatus(status) {
    return {
        401: 'Debes iniciar sesión nuevamente.',
        403: 'No tienes permiso para realizar esta acción.',
        404: 'El recurso solicitado no existe.',
        419: 'La sesión expiró. Actualiza la página.',
        422: 'Revisa los datos enviados.',
        429: 'Demasiadas solicitudes. Intenta nuevamente en unos segundos.',
        500: 'No se pudo completar la operación.',
    }[status] || 'No se pudo completar la operación.';
}
