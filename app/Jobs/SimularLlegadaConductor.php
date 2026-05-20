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
        $this->viaje->refresh();

        $this->viaje->update([
            'estado_viaje' => 'recogiendo'
        ]);

        event(new ViajeActualizado([
            'id_pasajero' => $this->viaje->id_pasajero,
            'estado'      => 'recogiendo',
            'id_viaje'    => $this->viaje->id_viaje
        ]));

        dispatch(new IniciarViaje($this->viaje->id_viaje))
            ->delay(now()->addSeconds(8));
    }
}