<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pasajero;
use App\Models\Conductor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pasajero de pruebas (María)
        $userPasajero = User::create([
            'nombre_completo' => 'María Altamirano',
            'email'           => 'pasajero@test.com',
            'contrasena_hash' => Hash::make('password123'),
            'tipo_usuario'    => 'pasajero',
            'activo'          => 1,
        ]);

        Pasajero::create([
            'id_pasajero'           => $userPasajero->id_usuario,
            'metodo_pago_preferido' => 'efectivo',
        ]);

        // 2. Conductor de pruebas (Juan Carlos)
        $userConductor = User::create([
            'nombre_completo' => 'Juan Carlos Flores',
            'email'           => 'conductor@test.com',
            'contrasena_hash' => Hash::make('password123'),
            'tipo_usuario'    => 'conductor',
            'activo'          => 1,
        ]);

        // 3. Conductor mapeado de forma idéntica a tu archivo de migración
        Conductor::create([
            'id_conductor'           => $userConductor->id_usuario,
            'licencia_numero'        => 'Q71234567',
            'estado_conductor'       => 'activo', // Cumple con tu ENUM ('activo','inactivo','en_verificacion')
            'saldo_disponible'       => 50.00,
            'calificacion_promedio'  => 4.8,
            'total_viajes'           => 12,
            'verificado_dni'         => 1,
            'fecha_verificacion_dni' => now(),
        ]);
    }
}