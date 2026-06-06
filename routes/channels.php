<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id_usuario === (int) $id;
});

Broadcast::channel('pasajero.{id}', function ($user, $id) {
    return $user->tipo_usuario === 'pasajero'
        && (int) $user->id_usuario === (int) $id;
});

Broadcast::channel('viaje.{id}', function ($user, $id) {
    return \App\Models\Viaje::where('id_viaje', $id)
        ->where(function ($query) use ($user) {
            $query->where('id_pasajero', $user->id_usuario)
                ->orWhere('id_conductor', $user->id_usuario);
        })
        ->exists();
});
