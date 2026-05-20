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
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        // Se transmite por el canal privado del pasajero correspondiente
        $idPasajero = $this->data['id_pasajero'] ?? $this->data['pasajero_id'] ?? 0;
        return [
            new PrivateChannel('pasajero.' . $idPasajero),
        ];
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}