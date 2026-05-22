<?php

namespace App\Jobs;

use App\Models\Viaje;
use App\Events\ViajeActualizado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SimularLlegadaConductor implements ShouldQueue
{
    use Queueable;

    public Viaje $viaje;

    public function __construct(Viaje $viaje)
    {
        $this->viaje = $viaje;
    }

    public function handle(): void
    {
        // Refrescar para tener el estado actual
        $this->viaje->refresh();

        // Paso 1 — Cambiar a 'recogiendo' (conductor llegó al pasajero)
        $this->viaje->update(['estado_viaje' => 'recogiendo']);

        event(new ViajeActualizado(
            (int) $this->viaje->id_pasajero,
            'recogiendo',
            (int) $this->viaje->id_viaje
        ));

        // Esperar 8 segundos y luego pasar a 'en_curso'
        sleep(8);

        $this->viaje->refresh();
        $this->viaje->update(['estado_viaje' => 'en_curso']);

        event(new ViajeActualizado(
            (int) $this->viaje->id_pasajero,
            'en_curso',
            (int) $this->viaje->id_viaje
        ));
    }
}