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

        event(new ViajeActualizado([
            'id_pasajero' => $viaje->id_pasajero,
            'estado'      => 'en_curso',
            'id_viaje'    => $viaje->id_viaje
        ]));
    }
}