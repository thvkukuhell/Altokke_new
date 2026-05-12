<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{   
    // se ejecuta cuando haces: php artisan migrate
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario'); // autoincrement PK
            $table->string('nombre_completo', 150);
            $table->string('apellidos', 150)->nullable();
            $table->string('dni', 20)->unique()->nullable();
            $table->string('telefono', 30)->unique()->nullable();
            $table->string('email', 150)->unique()->nullable();
            $table->string('contrasena_hash', 255);
            $table->enum('tipo_usuario', ['pasajero', 'conductor']);
            $table->tinyInteger('activo')->default(1);
            $table->rememberToken();
            $table->timestamps(); // añade created_at y updated_at solos
        });
    }

    // se ejecuta cuando haces php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('usuarios'); // deshace el up()
    }
};
