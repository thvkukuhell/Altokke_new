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

class ViajeCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Viaje $viaje) {}

    // Canal donde escuchan los conductores
    public function broadcastOn(): Channel
    {
        return new Channel('conductores');
    }

    // Solo manda estos datos al frontend
    public function broadcastWith(): array
    {
        return [
            'id' => $this->viaje->id_viaje,
            'origen' => $this->viaje->origen_texto,
            'destino' => $this->viaje->destino_texto,
            'tarifa' => $this->viaje->tarifa_estimada,
            'metodo' => $this->viaje->metodo_pago,
        ];
    }
}
