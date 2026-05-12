<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id('id_calificacion');
            $table->unsignedBigInteger('id_viaje')->unique();
            $table->decimal('puntuacion', 2, 1);
            $table->text('comentario')->nullable();
            $table->dateTime('fecha_calificacion')->useCurrent();
            $table->timestamps();
 
            $table->foreign('id_viaje')
                  ->references('id_viaje')
                  ->on('viajes')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
