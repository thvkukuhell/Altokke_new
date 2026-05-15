<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pasajero;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'nombre_completo' => 'María Altamirano',
            'email'           => 'pasajero@test.com',
            'contrasena_hash' => Hash::make('password123'),
            'tipo_usuario'    => 'pasajero',
            'activo'          => 1,
        ]);

        // Crear también el registro en tabla pasajeros
        Pasajero::create([
            'id_pasajero'          => $user->id_usuario,
            'metodo_pago_preferido' => 'efectivo',
        ]);
    }
}
