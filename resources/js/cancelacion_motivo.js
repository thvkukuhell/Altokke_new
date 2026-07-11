document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modal-motivo-cancelacion');
    const openButtons = document.querySelectorAll('[data-open-cancel-modal]');
    const closeButtons = document.querySelectorAll('[data-close-cancel-modal]');
    const motivoSelect = document.getElementById('motivo_cancelacion');
    const otroContenedor = document.getElementById('modal-cancelacion-otro-contenedor');
    const viajeIdInput = document.getElementById('modal-cancelacion-viaje-id');

    if (!modal) {
        return;
    }

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.right = '0';
    modal.style.bottom = '0';
    modal.style.zIndex = '999999';

    function abrirModal(viajeId) {
        if (viajeId && viajeIdInput) {
            viajeIdInput.value = viajeId;
        }
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', function () {
            abrirModal(button.dataset.viajeId || '');
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', function () {
            cerrarModal();
        });
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    if (motivoSelect && otroContenedor) {
        motivoSelect.addEventListener('change', function () {
            if (motivoSelect.value === 'otro') {
                otroContenedor.classList.remove('hidden');
            } else {
                otroContenedor.classList.add('hidden');
            }
        });
        motivoSelect.dispatchEvent(new Event('change'));
    }

    if (modal.dataset.showModal === '1') {
        abrirModal(viajeIdInput?.value || '');
    }
});
