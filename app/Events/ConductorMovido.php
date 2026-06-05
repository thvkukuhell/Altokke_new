<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConductorMovido implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $viajeId,
        public float $lat,
        public float $lng
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('viaje.' . $this->viajeId);
    }

    public function broadcastAs(): string 
    {
        return 'UbicacionConductorActualizada';
    }

    public function broadcastWith(): array 
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
