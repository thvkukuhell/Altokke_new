<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soporte_tickets', function (Blueprint $table) {
            $table->id('id_ticket');
            $table->unsignedBigInteger('id_usuario');
            $table->string('asunto', 200)->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estado_ticket', ['abierto','en_proceso','cerrado'])
                  ->default('abierto');
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->dateTime('fecha_cierre')->nullable();
            $table->timestamps();
 
            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soporte_tickets');
    }
};
