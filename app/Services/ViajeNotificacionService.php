<?php

namespace App\Services;

use App\Mail\ViajeCompletadoMail;
use App\Models\Viaje;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ViajeNotificacionService
{
    public function enviarResumenCompletado(Viaje $viaje): void
    {
        try {
            $viaje->loadMissing('pasajero.user', 'conductor.user');
            $email = $viaje->pasajero->user->email ?? null;

            if (! $email) {
                return;
            }

            Mail::to($email)->send(new ViajeCompletadoMail($viaje));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar resumen de viaje completado', [
                'viaje_id' => $viaje->id_viaje,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
