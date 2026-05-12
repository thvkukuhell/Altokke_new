<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id('id_comision');
            $table->unsignedBigInteger('id_viaje')->unique();
            $table->unsignedBigInteger('id_conductor');
            $table->decimal('monto_comision', 10, 2);
            $table->date('fecha_descuento');
            $table->timestamps();
 
            $table->foreign('id_viaje')
                  ->references('id_viaje')
                  ->on('viajes')
                  ->cascadeOnDelete();
 
            $table->foreign('id_conductor')
                  ->references('id_conductor')
                  ->on('conductores')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones');
    }
};
