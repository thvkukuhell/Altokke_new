document.addEventListener('DOMContentLoaded', function () {
    const paso1 = document.getElementById('paso-1');
    const paso2 = document.getElementById('paso-2');
    const formErrors = document.getElementById('form-errors');
    const step1 = document.getElementById('step-indicator-1');
    const step2 = document.getElementById('step-indicator-2');
    const siguienteBtn = document.getElementById('siguienteBtn');
    const atrasBtn = document.getElementById('atrasBtn');

    if (!paso1 || !paso2 || !formErrors || !step1 || !step2 || !siguienteBtn || !atrasBtn) {
        return;
    }

    function mostrarErrores(lista) {
        formErrors.style.display = 'block';
        formErrors.replaceChildren();

        const listado = document.createElement('ul');
        lista.forEach(function (mensaje) {
            const item = document.createElement('li');
            item.textContent = mensaje;
            listado.appendChild(item);
        });

        formErrors.appendChild(listado);
        formErrors.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    siguienteBtn.addEventListener('click', function () {
        const nombre = document.querySelector('[name="nombre"]').value.trim();
        const dni = document.querySelector('[name="dni"]').value.trim();
        const telefono = document.querySelector('[name="telefono"]').value.trim();
        const correo = document.querySelector('[name="email"]').value.trim();
        const password = document.querySelector('[name="password"]').value;
        const confirmar = document.querySelector('[name="password_confirmation"]').value;
        const licencia = document.querySelector('[name="numero_licencia"]').value.trim();

        const errores = [];
        if (!nombre) errores.push('El nombre es obligatorio.');
        if (!dni) errores.push('El DNI es obligatorio.');
        if (!telefono) errores.push('El telefono es obligatorio.');
        if (!correo) errores.push('El correo es obligatorio.');
        if (password.length < 8) errores.push('La contrasena debe tener al menos 8 caracteres.');
        if (password !== confirmar) errores.push('Las contrasenas no coinciden.');
        if (!licencia) errores.push('El numero de licencia es obligatorio.');

        if (errores.length > 0) {
            mostrarErrores(errores);
            return;
        }

        formErrors.style.display = 'none';
        formErrors.replaceChildren();
        paso1.classList.remove('active');
        paso2.classList.add('active');
        step1.classList.remove('activo');
        step2.classList.add('activo');
        paso2.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    atrasBtn.addEventListener('click', function () {
        paso2.classList.remove('active');
        paso1.classList.add('active');
        step2.classList.remove('activo');
        step1.classList.add('activo');
    });
});
