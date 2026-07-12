@extends('layouts.main')

@section('content')
<section class="info-page info-page--contact">
    <div class="info-page__inner">
        <div class="contact-layout">
            <div class="contact-panel">
                <span class="info-page__eyebrow">Contacto</span>
                <h1>Cuéntanos qué pasó con tu viaje</h1>
                <p>
                    Usa este canal para consultas, reclamos, sugerencias o reportes. El mensaje queda registrado y el equipo puede revisarlo sin perder el contexto.
                </p>

                <div class="contact-methods" aria-label="Canales de contacto">
                    <article class="contact-method">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 6h16v12H4z" />
                                <path d="m4 7 8 6 8-6" />
                            </svg>
                        </span>
                        <div>
                            <h2>Correo oficial</h2>
                            <a href="mailto:{{ config('app.support_email') }}">{{ config('app.support_email') }}</a>
                        </div>
                    </article>

                    <article class="contact-method">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M7 4h10v16H7z" />
                                <path d="M11 17h2" stroke-linecap="round" />
                            </svg>
                        </span>
                        <div>
                            <h2>Teléfono</h2>
                            <a href="tel:{{ config('app.support_phone') }}">{{ config('app.support_phone') }}</a>
                        </div>
                    </article>

                    <article class="contact-method">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="8" />
                                <path d="M12 8v4l2.5 2" stroke-linecap="round" />
                            </svg>
                        </span>
                        <div>
                            <h2>Atención</h2>
                            <p>Lunes a domingo, de 08:00 a 21:00.</p>
                        </div>
                    </article>
                </div>
            </div>

            <div class="contact-form-card">
                <h2>Enviar una solicitud</h2>
                <p class="contact-form-card__intro">Completa los datos y describe el caso con claridad.</p>

                @if(session('success'))
                    <div class="alerto-exito">{{ session('success') }}</div>
                @endif

                <form action="{{ route('ayuda.enviar') }}" method="POST" class="info-form">
                    @csrf

                    <div class="auth-campo">
                        <label for="nombre">Nombre completo</label>
                        <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" autocomplete="name" required>
                        @error('nombre')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-campo">
                        <label for="correo">Correo electrónico</label>
                        <input id="correo" type="email" name="correo" value="{{ old('correo') }}" autocomplete="email" required>
                        @error('correo')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-campo">
                        <label for="asunto">Asunto</label>
                        <input id="asunto" type="text" name="asunto" value="{{ old('asunto') }}" required>
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
                        <textarea id="descripcion" name="descripcion" rows="5" required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="btn-auth">Enviar solicitud</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
