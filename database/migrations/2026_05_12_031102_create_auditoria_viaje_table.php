<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_viaje', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('id_viaje');
            $table->string('campo_modificado', 200)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->dateTime('fecha_cambio')->useCurrent();
            $table->timestamps();
 
            $table->foreign('id_viaje')
                  ->references('id_viaje')
                  ->on('viajes')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_viaje');
    }
};
