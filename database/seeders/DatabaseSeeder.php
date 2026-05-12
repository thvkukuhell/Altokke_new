<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'nombre_completo' => 'María Altamirano',
            'email' => 'pasajero@test.com',
            'contrasena_hash' => Hash::make('password123'),
            'tipo_usuario' => 'pasajero',
        ]);
    }
}
