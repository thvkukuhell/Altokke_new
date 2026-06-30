export function iniciarCerrarSesion() {
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('.js-cerrar-sesion')?.addEventListener('click', function (evento) {
            evento.preventDefault();
            const formulario = document.getElementById(this.dataset.formId);
            if (formulario) formulario.submit();
        });
    });
}
