<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViajeActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int    $pasajeroId,
        public string $estado,
        public int    $viajeId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('pasajero.' . $this->pasajeroId);
    }

    public function broadcastAs(): string 
    {
        return 'ViajeActualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'estado'   => $this->estado,
            'viaje_id' => $this->viajeId,
        ];
    }
}