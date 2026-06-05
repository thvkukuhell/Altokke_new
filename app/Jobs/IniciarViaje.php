<?php

namespace App\Jobs;

use App\Models\Viaje;
use App\Events\ViajeActualizado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IniciarViaje implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $viajeId) {}

    public function handle(): void
    {
        $viaje = Viaje::find($this->viajeId);
        if (!$viaje) return;

        $viaje->update([
            'estado_viaje' => 'en_curso'
        ]);

        event(new ViajeActualizado(
            (int) $viaje->id_pasajero,
            'en_curso',
            (int) $viaje->id_viaje
        ));
    }
}