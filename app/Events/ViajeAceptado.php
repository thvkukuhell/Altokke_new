<?php

namespace App\Events;

use App\Models\Viaje;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class ViajeAceptado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Viaje $viaje) {}

    // Canal privado del pasajero
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('pasajero.' . $this->viaje->id_pasajero);
    }

    // Nombre del evento que escucha Laravel Echo
    public function broadcastAs(): string
    {
        return 'ViajeAceptado';
    }

    // Datos que recibe el frontend
    public function broadcastWith(): array
    {
        $conductor = $this->viaje->conductor;
        $vehiculo = $conductor?->vehiculo;

        return [
            'viaje_id' => $this->viaje->id_viaje,
            'conductor_nombre' => $conductor?->user?->nombre_completo ?? '-',
            'conductor_placa' => $vehiculo?->placa ?? '-',
            'conductor_modelo' => trim(($vehiculo?->marca ?? '') . ' ' . ($vehiculo?->modelo ?? '')),
            'calificacion' => $conductor?->calificacion_promedio ?? 0,
        ];
    }
}