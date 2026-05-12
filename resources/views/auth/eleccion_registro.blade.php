<main>
    <div class="crear-cuenta">
        <h1>Crea tu cuenta</h1>
        <p>Elige cómo quieres usar la plataforma</p>
    </div>
 
    <section class="tarjetas-rol">
 
        <a href="{{ route('registro_pasajero') }}" class="tarjeta-rol">
            <h2>Soy pasajero</h2>
            <p>Quiero solicitar viajes</p>
            <span>Registrarme como pasajero</span>
        </a>
 
        <a href="{{ route('registro_conductor') }}" class="tarjeta-rol">
            <h2>Soy conductor</h2>
            <p>Quiero ganar con mi vehículo</p>
            <span>Registrarme como conductor</span>
        </a>
 
    </section>
</main>