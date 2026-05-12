<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recarga_saldo', function (Blueprint $table) {
            $table->id('id_recarga');
            $table->unsignedBigInteger('id_conductor');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_recarga', ['yape','plin','efectivo']);
            $table->string('referencia', 150)->nullable();
            $table->string('comprobante_url', 500)->nullable();
            $table->enum('estado_recarga', ['pendiente','aprobada','rechazada'])
                  ->default('pendiente');
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->timestamps();
 
            $table->foreign('id_conductor')
                  ->references('id_conductor')
                  ->on('conductores')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recarga_saldo');
    }
};
