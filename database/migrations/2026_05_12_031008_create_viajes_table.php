<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viajes', function (Blueprint $table) {
            $table->id('id_viaje');
            $table->unsignedBigInteger('id_pasajero');
            $table->unsignedBigInteger('id_conductor')->nullable();
            $table->text('origen_texto')->nullable();
            $table->text('destino_texto')->nullable();
            $table->decimal('lat_origen', 10, 7)->nullable();
            $table->decimal('lng_origen', 10, 7)->nullable();
            $table->decimal('lat_destino', 10, 7)->nullable();
            $table->decimal('lng_destino', 10, 7)->nullable();
            $table->decimal('tarifa_estimada', 10, 2);
            $table->decimal('tarifa_final', 10, 2)->nullable();
            $table->enum('tipo_servicio', ['normal','express'])->default('normal');
            $table->enum('metodo_pago', ['efectivo','yape','plin'])->default('efectivo');
            $table->enum('estado_viaje', [
                'buscando','aceptado','recogiendo',
                'en_curso','completado','cancelado'
            ])->default('buscando');
            $table->decimal('distancia_km', 7, 2)->nullable();
            $table->integer('tiempo_estimado_min')->nullable();
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->tinyInteger('compartido')->default(0);
            $table->timestamps();
 
            $table->foreign('id_pasajero')
                  ->references('id_pasajero')
                  ->on('pasajeros')
                  ->cascadeOnDelete();
 
            $table->foreign('id_conductor')
                  ->references('id_conductor')
                  ->on('conductores')
                  ->nullOnDelete();
 
            $table->index(['estado_viaje', 'fecha_solicitud'], 'idx_viaje_estado_fecha');
            $table->index(['id_pasajero',  'estado_viaje'],    'idx_viaje_pasajero_estado');
            $table->index(['id_conductor', 'estado_viaje'],    'idx_viaje_conductor_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viajes');
    }
};
