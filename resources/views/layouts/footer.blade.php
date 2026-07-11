<footer class="pie-pagina">

    <div class="contenedor-footer">

        <!-- Marca / descripción -->
        <div class="columna-footer">
            <h2 class="titulo-footer">Altokke</h2>
            <p class="descripcion-footer">
                Plataforma de transporte que conecta pasajeros y conductores de forma rápida, segura y eficiente.
            </p>
        </div>

        <!-- Enlaces -->
        <div class="columna-footer">
            <h3 class="subtitulo-footer">Enlaces</h3>
            <ul class="lista-footer">
                {{-- Inicio: redirige según tipo de usuario autenticado --}}
                <li>
                    @if(auth()->check())
                        @if(auth()->user()->tipo_usuario === 'conductor')
                            <a href="{{ route('conductor.dashboard') }}" class="enlace-footer">Inicio</a>
                        @elseif(auth()->user()->tipo_usuario === 'pasajero')
                            <a href="{{ route('pasajero.solicitarViaje') }}" class="enlace-footer">Inicio</a>
                        @else
                            <a href="{{ route('inicio') }}" class="enlace-footer">Inicio</a>
                        @endif
                    @else
                        <a href="{{ route('inicio') }}" class="enlace-footer">Inicio</a>
                    @endif
                </li>

                <li><a href="{{ route('servicios') }}" class="enlace-footer">Servicios</a></li>
                <li><a href="{{ route('contacto') }}" class="enlace-footer">Contacto</a></li>
                <li><a href="{{ route('ayuda') }}" class="enlace-footer">Ayuda</a></li>
            </ul>
        </div>

        <div class="columna-footer">
            <h3 class="subtitulo-footer">Soporte</h3>
            <p class="texto-footer">Email: <a href="mailto:{{ config('app.support_email') }}" class="enlace-footer" target="_blank" rel="noopener">{{ config('app.support_email') }}</a></p>
            <p class="texto-footer">Teléfono: <a href="tel:{{ config('app.support_phone') }}" class="enlace-footer">{{ config('app.support_phone') }}</a></p>
        </div>

    </div>

    <!-- Línea inferior -->
    <div class="barra-inferior">
        <p class="copy-footer">© 2026 Altokke - Todos los derechos reservados</p>
    </div>

</footer>