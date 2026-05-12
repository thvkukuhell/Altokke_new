<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->unsignedBigInteger('id_conductor')->unique();
            $table->string('placa', 30)->unique();
            $table->string('marca', 80)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->string('color', 40)->nullable();
            $table->integer('anio')->nullable();
            $table->string('numero_soat', 80)->nullable();
            $table->tinyInteger('verificado_placa')->default(0);
            $table->dateTime('fecha_verificacion_placa')->nullable();
            $table->timestamps();
 
            $table->foreign('id_conductor')
                  ->references('id_conductor')
                  ->on('conductores')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
