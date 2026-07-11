@extends('layouts.main')

@section('content')
<section class="seccion ayuda-seccion">
    <div class="seccion-inner">
        <div class="seccion-cabecera">
            <span class="seccion-chip">Ayuda</span>
            <h1 class="seccion-titulo">Envía tu consulta o reclamo</h1>
            <p class="seccion-sub">Completa el formulario y nos pondremos en contacto para resolver tu solicitud.</p>
        </div>

        <div class="formulario-contacto">
            @if(session('success'))
                <div class="alerto-exito">{{ session('success') }}</div>
            @endif

            <form action="{{ route('ayuda.enviar') }}" method="POST">
                @csrf

                <div class="auth-campo">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Tu nombre completo" required>
                    @error('nombre')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="auth-campo">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" type="email" name="correo" value="{{ old('correo') }}" placeholder="ejemplo@correo.com" required>
                    @error('correo')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="auth-campo">
                    <label for="asunto">Asunto</label>
                    <input id="asunto" type="text" name="asunto" value="{{ old('asunto') }}" placeholder="Tema de tu solicitud" required>
                    @error('asunto')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="auth-campo">
                    <label for="tipo_solicitud">Tipo de solicitud</label>
                    <select id="tipo_solicitud" name="tipo_solicitud" required>
                        <option value="" disabled selected>Selecciona un tipo</option>
                        <option value="consulta" {{ old('tipo_solicitud') === 'consulta' ? 'selected' : '' }}>Consulta</option>
                        <option value="reclamo" {{ old('tipo_solicitud') === 'reclamo' ? 'selected' : '' }}>Reclamo</option>
                        <option value="sugerencia" {{ old('tipo_solicitud') === 'sugerencia' ? 'selected' : '' }}>Sugerencia</option>
                        <option value="reporte" {{ old('tipo_solicitud') === 'reporte' ? 'selected' : '' }}>Reporte de problema</option>
                    </select>
                    @error('tipo_solicitud')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="auth-campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="6" placeholder="Explica tu consulta o reclamo" required>{{ old('descripcion') }}</textarea>
                    @error('descripcion')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn-auth">Enviar solicitud</button>
            </form>
        </div>
    </div>
</section>
@endsection
