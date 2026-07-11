export function iniciarCerrarSesion() {
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('.js-cerrar-sesion')?.addEventListener('click', function (evento) {
            evento.preventDefault();
            if (!window.confirm('¿Cerrar sesión?')) return;
            const formulario = document.getElementById(this.dataset.formId);
            if (formulario) formulario.submit();
        });
    });
}
