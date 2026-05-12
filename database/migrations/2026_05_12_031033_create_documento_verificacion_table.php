<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_verificacion', function (Blueprint $table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_conductor');
            $table->enum('tipo_documento', [
                'dni','tarjeta_propiedad',
                'licencia_conducir','soat'
            ]);
            $table->text('url_archivo');
            $table->enum('estado_documento', ['pendiente','aprobado','rechazado'])
                  ->default('pendiente');
            $table->dateTime('fecha_subida')->useCurrent();
            $table->dateTime('fecha_revision')->nullable();
            $table->timestamps();
 
            $table->foreign('id_conductor')
                  ->references('id_conductor')
                  ->on('conductores')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_verificacion');
    }
};
