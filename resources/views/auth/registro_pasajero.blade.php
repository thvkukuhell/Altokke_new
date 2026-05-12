<main>
    <img src="{{ asset('assets/img/login_client_icon.png') }}"
         alt="Imagen pasajero"
         id="img-pasajero">
 
    <form action="{{ route('proc_regist_pasajero') }}"
          method="POST"
          class="registro-pasajero"
          autocomplete="on"
          novalidate>
 
        @csrf
 
        <h1>Crea tu cuenta de pasajero</h1>
        <p>En menos de 1 minuto y gratis</p>
 
        <div class="inputs">
 
            <input required
                   type="text"
                   name="nombre"
                   placeholder="Nombre"
                   value="{{ old('nombre') }}">
 
            <input required
                   type="text"
                   name="apellidos"
                   placeholder="Apellidos"
                   value="{{ old('apellidos') }}">
 
            <input required
                   type="text"
                   name="dni"
                   minlength="8"
                   placeholder="DNI"
                   value="{{ old('dni') }}">
 
            <input required
                   type="text"
                   name="telefono"
                   placeholder="Teléfono"
                   value="{{ old('telefono') }}">
 
            <input required
                   type="email"
                   name="email"
                   id="email"
                   placeholder="ejemplo@dominio.com"
                   value="{{ old('email') }}">
 
            <input required
                   type="password"
                   name="password"
                   placeholder="Contraseña">
 
            <input required
                   type="password"
                   name="password_confirmation"
                   placeholder="Confirmar contraseña">
 
        </div>
 
        <button type="submit">Registrarme como pasajero</button>
 
    </form>
 
    {{-- Errores --}}
    @if ($errors->any())
        <div class="form-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
 
</main>