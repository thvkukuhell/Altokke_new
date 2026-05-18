<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
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

    public function broadcastOn(): Channel
    {
        return new Channel('viaje.' . $this->viajeId);
    }

    public function broadcastWith(): array 
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
