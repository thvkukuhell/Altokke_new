<?php

namespace App\Events;

use App\Models\Viaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViajeAceptado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Viaje $viaje) {}

    // Canal privado - solo el pasajero dueño del viaje escucha
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('pasajero.'.$this->viaje->id_pasajero);
    }

    public function broadcastWith(): array 
    {
        $conductor = $this->viaje->conductor;
        $vehiculo = $conductor?->vehiculo;

        return [
            'viaje_id' => $this->viaje->id_viaje,
            'conductor_nombre' => $conductor?->user?->nombre_completo ?? '-',
            'conductor_placa' => $vehiculo?->placa ?? '-',
            'conductor_modelo' => ($vehiculo?->marca ?? '') . ' ' . ($vehiculo?->modelo ?? ''),
            'calificacion' => $conductor?->calificacion_promedio ?? 0,
        ];
    }
}
