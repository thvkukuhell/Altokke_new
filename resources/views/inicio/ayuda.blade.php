@extends('layouts.main')

@section('content')
<section class="seccion ayuda-seccion">
    <div class="seccion-inner">
        <div class="seccion-cabecera">
            <span class="seccion-chip">Ayuda</span>
            <h1 class="seccion-titulo">Formulario de ayuda y reclamos</h1>
            <p class="seccion-sub">Escribe tu consulta, reclamo, sugerencia o reporte. Te responderemos lo antes posible.</p>
        </div>

        <div class="formulario-contacto">
            @if(session('success'))
                <div class="alerto-exito">{{ session('success') }}</div>
            @endif

            <form action="{{ route('ayuda.enviar') }}" method="POST">
                @csrf

                <div class="auth-campo">
                    <label for="nombre">Nombre completo</label>
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
                        <option value="" disabled {{ old('tipo_solicitud') ? '' : 'selected' }}>Selecciona un tipo</option>
                        <option value="consulta" {{ old('tipo_solicitud') === 'consulta' ? 'selected' : '' }}>Consulta</option>
                        <option value="reclamo" {{ old('tipo_solicitud') === 'reclamo' ? 'selected' : '' }}>Reclamo</option>
                        <option value="sugerencia" {{ old('tipo_solicitud') === 'sugerencia' ? 'selected' : '' }}>Sugerencia</option>
                        <option value="reporte" {{ old('tipo_solicitud') === 'reporte' ? 'selected' : '' }}>Reporte de problema</option>
                    </select>
                    @error('tipo_solicitud')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="auth-campo">
                    <label for="descripcion">Describe tu consulta o reclamo</label>
                    <textarea id="descripcion" name="descripcion" rows="6" placeholder="Escribe aquí tu mensaje" required>{{ old('descripcion') }}</textarea>
                    @error('descripcion')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn-auth">Enviar solicitud</button>
            </form>
        </div>

        <div class="ayuda-info">
            <p>También puedes escribirnos directamente al correo oficial: <a href="mailto:{{ config('app.support_email') }}">{{ config('app.support_email') }}</a>.</p>
        </div>
    </div>
</section>
@endsection
