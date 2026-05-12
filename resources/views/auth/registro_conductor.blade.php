<main class="registro">
    <h1>Registro conductor</h1>
 
    <form action="{{ route('proc_regist_conductor') }}"
          method="POST"
          class="registro-conductor"
          autocomplete="on"
          novalidate>
 
        @csrf
 
        {{-- Paso 1: Cuenta --}}
        <div class="paso-form active" id="paso-1">
            <h2>Cuenta</h2>
 
            <input required type="text"     name="nombre"             placeholder="Nombre"
                   value="{{ old('nombre') }}">
            <input required type="text"     name="apellidos"          placeholder="Apellidos"
                   value="{{ old('apellidos') }}">
            <input required type="text"     name="dni"                placeholder="DNI"
                   value="{{ old('dni') }}">
            <input required type="text"     name="telefono"           placeholder="Teléfono"
                   value="{{ old('telefono') }}">
            <input required type="email"    name="email"              placeholder="Correo"
                   value="{{ old('email') }}">
            <input required type="password" name="password"           placeholder="Contraseña">
            <input required type="password" name="password_confirmation" placeholder="Confirmar contraseña">
            <input         type="text"     name="numero_licencia"    placeholder="Licencia Ej.: LIC-123456"
                   value="{{ old('numero_licencia') }}">
 
            <div class="botones btn-1">
                <button id="siguienteBtn" type="button">Siguiente</button>
            </div>
        </div>
 
        {{-- Paso 2: Vehículo --}}
        <div class="paso-form" id="paso-2">
            <h2>Vehículo</h2>
 
            <div class="grid-vehiculo">
                <input required type="text" name="placa"    placeholder="Ej.: ABC-1234"
                       value="{{ old('placa') }}">
                <input type="text" name="marca"             placeholder="Marca"
                       value="{{ old('marca') }}">
                <input type="text" name="modelo"            placeholder="Modelo"
                       value="{{ old('modelo') }}">
                <input type="text" name="year"              placeholder="Año"
                       value="{{ old('year') }}">
                <input type="text" name="color"             placeholder="Color"
                       value="{{ old('color') }}">
                <input type="text" name="numero_soat"       placeholder="Ej.: SOAT-ABC-1234"
                       value="{{ old('numero_soat') }}">
            </div>
 
            <div class="botones">
                <button type="button" id="atrasBtn">Atrás</button>
                <button type="submit" id="confirmar">Confirmar</button>
            </div>
        </div>
 
    </form>
 
    {{-- Errores del servidor --}}
    @if ($errors->any())
        <div class="form-errors" id="form-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
 
</main>
 
<script>
document.addEventListener('DOMContentLoaded', function () {
    const siguiente  = document.getElementById('siguienteBtn');
    const atras      = document.getElementById('atrasBtn');
    const paso1      = document.getElementById('paso-1');
    const paso2      = document.getElementById('paso-2');
    const formErrors = document.getElementById('form-errors');
 
    function mostrarErrores(lista) {
        if (!formErrors) return;
        formErrors.style.display = 'block';
        formErrors.innerHTML = '<ul>' + lista.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        formErrors.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
 
    if (siguiente) {
        siguiente.addEventListener('click', function () {
            const nombre   = document.querySelector('input[name="nombre"]').value.trim();
            const dni      = document.querySelector('input[name="dni"]').value.trim();
            const telefono = document.querySelector('input[name="telefono"]').value.trim();
            const correo   = document.querySelector('input[name="email"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            const confirmar= document.querySelector('input[name="password_confirmation"]').value;
            const licencia = document.querySelector('input[name="numero_licencia"]').value.trim();
 
            const errores = [];
            if (!nombre)           errores.push('El nombre es obligatorio.');
            if (!dni)              errores.push('El DNI es obligatorio.');
            if (!telefono)         errores.push('El teléfono es obligatorio.');
            if (!correo)           errores.push('El correo es obligatorio.');
            if (password.length < 8) errores.push('La contraseña debe tener al menos 8 caracteres.');
            if (password !== confirmar) errores.push('Las contraseñas no coinciden.');
            if (!licencia)         errores.push('El número de licencia es obligatorio.');
 
            if (errores.length > 0) {
                mostrarErrores(errores);
                return;
            }
 
            if (formErrors) {
                formErrors.style.display = 'none';
                formErrors.innerHTML = '';
            }
 
            paso1.classList.remove('active');
            paso2.classList.add('active');
            paso2.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
 
    if (atras) {
        atras.addEventListener('click', function () {
            paso2.classList.remove('active');
            paso1.classList.add('active');
            paso1.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
});
</script>