<div class="pagina-pasajero">
    <div class="buscando-centro">
 
        <div class="icono-buscando">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5">
                <path d="M5 17H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11l4 4v4"/>
                <circle cx="7" cy="17" r="2"/>
                <circle cx="17" cy="17" r="2"/>
                <path d="M9 17h6"/>
            </svg>
        </div>
 
        <h2 class="buscando-titulo">Buscando mototaxi cercano...</h2>
        <p class="buscando-tiempo">Menos de 2 minutos</p>
        <p class="buscando-desc">
            Te conectamos con el conductor más cercano disponible en Bagua
        </p>
 
        <div class="progreso-wrap">
            <div class="progreso-barra"></div>
        </div>
 
        <div class="tarjeta-viaje">
            <div class="fila-dato">
                <span>Origen</span>
                <strong>{{ $viaje['origen'] ?? '—' }}</strong>
            </div>
            <hr class="separador">
            <div class="fila-dato">
                <span>Destino</span>
                <strong>{{ $viaje['destino'] ?? '—' }}</strong>
            </div>
            <hr class="separador">
            <div class="fila-dato" style="margin:0;">
                <span>Tarifa estimada</span>
                <strong style="font-size:17px; color:var(--p-verde-dark);">
                    S/ {{ $viaje['tarifa'] ?? '0.00' }}
                </strong>
            </div>
        </div>
 
        <p class="nota-cancelar">Si cancelas ahora no se te cobrará nada.</p>
 
        <form method="POST" action="{{ route('pasajero.cancelarViaje') }}">
            @csrf
            <input type="hidden" name="viaje_id" value="{{ $viaje['id'] ?? 0 }}">
            <button type="submit" class="btn btn-outline">✕ Cancelar solicitud</button>
        </form>
 
    </div>
</div>