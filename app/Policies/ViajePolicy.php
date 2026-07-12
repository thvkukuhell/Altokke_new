<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Viaje;

class ViajePolicy
{
    public function view(User $user, Viaje $viaje): bool
    {
        return $this->isPasajeroOwner($user, $viaje)
            || $this->isAssignedConductor($user, $viaje)
            || $this->accept($user, $viaje);
    }

    public function accept(User $user, Viaje $viaje): bool
    {
        return $user->tipo_usuario === 'conductor'
            && $viaje->estado_viaje === 'buscando'
            && $viaje->id_conductor === null;
    }

    public function updateLocation(User $user, Viaje $viaje): bool
    {
        return $this->isAssignedConductor($user, $viaje)
            && in_array($viaje->estado_viaje, ['aceptado', 'recogiendo', 'en_curso'], true);
    }

    public function startPickup(User $user, Viaje $viaje): bool
    {
        return $this->isAssignedConductor($user, $viaje)
            && $viaje->estado_viaje === 'aceptado';
    }

    public function startTrip(User $user, Viaje $viaje): bool
    {
        return $this->isAssignedConductor($user, $viaje)
            && $viaje->estado_viaje === 'recogiendo';
    }

    public function complete(User $user, Viaje $viaje): bool
    {
        return $this->isAssignedConductor($user, $viaje)
            && $viaje->estado_viaje === 'en_curso';
    }

    public function cancel(User $user, Viaje $viaje): bool
    {
        return ($this->isPasajeroOwner($user, $viaje)
                && in_array($viaje->estado_viaje, ['buscando', 'aceptado', 'recogiendo'], true))
            || ($this->isAssignedConductor($user, $viaje)
                && in_array($viaje->estado_viaje, ['aceptado', 'recogiendo'], true));
    }

    public function rate(User $user, Viaje $viaje): bool
    {
        return $this->isPasajeroOwner($user, $viaje)
            && $viaje->estado_viaje === 'completado'
            && $viaje->id_conductor !== null;
    }

    public function downloadReport(User $user, Viaje $viaje): bool
    {
        return $viaje->estado_viaje === 'completado'
            && ($this->isPasajeroOwner($user, $viaje) || $this->isAssignedConductor($user, $viaje));
    }

    private function isPasajeroOwner(User $user, Viaje $viaje): bool
    {
        return $user->tipo_usuario === 'pasajero'
            && (int) $viaje->id_pasajero === (int) $user->id_usuario;
    }

    private function isAssignedConductor(User $user, Viaje $viaje): bool
    {
        return $user->tipo_usuario === 'conductor'
            && (int) $viaje->id_conductor === (int) $user->id_usuario;
    }
}
