<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->unsignedBigInteger('id_usuario');
            $table->string('titulo', 200)->nullable();
            $table->text('mensaje')->nullable();
            $table->tinyInteger('leida')->default(0);
            $table->dateTime('fecha_notificacion')->useCurrent();
            $table->timestamps();
 
            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
