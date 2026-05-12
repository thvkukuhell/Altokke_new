<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->unsignedBigInteger('id_conductor')->primary();
            $table->string('licencia_numero', 80)->nullable();
            $table->enum('estado_conductor', ['activo','inactivo','en_verificacion'])
                  ->default('en_verificacion');
            $table->decimal('saldo_disponible', 10, 2)->default(0.00);
            $table->decimal('calificacion_promedio', 2, 1)->default(0.0);
            $table->integer('total_viajes')->default(0);
            $table->tinyInteger('verificado_dni')->default(0);
            $table->dateTime('fecha_verificacion_dni')->nullable();
            $table->timestamps();
 
            $table->foreign('id_conductor')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductores');
    }
};
