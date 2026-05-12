<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_viaje_temporal', function (Blueprint $table) {
            $table->id('id_solicitud_temp');
            $table->unsignedBigInteger('id_pasajero');
            $table->text('origen_texto')->nullable();
            $table->text('destino_texto')->nullable();
            $table->decimal('tarifa_estimada', 10, 2);
            $table->enum('metodo_pago', ['efectivo','yape','plin'])->default('efectivo');
            $table->dateTime('fecha_solicitud_temp')->useCurrent();
            $table->enum('estado_busqueda', ['buscando','encontrado','cancelado'])
                  ->default('buscando');
            $table->timestamps();
 
            $table->foreign('id_pasajero')
                  ->references('id_pasajero')
                  ->on('pasajeros')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_viaje_temporal');
    }
};
