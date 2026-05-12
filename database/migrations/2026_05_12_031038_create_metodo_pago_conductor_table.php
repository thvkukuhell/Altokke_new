<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodo_pago_conductor', function (Blueprint $table) {
            $table->id('id_metodo_pago');
            $table->unsignedBigInteger('id_conductor');
            $table->enum('tipo', ['yape','plin']);
            $table->string('numero_cuenta', 150);
            $table->timestamps();
 
            $table->foreign('id_conductor')
                  ->references('id_conductor')
                  ->on('conductores')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodo_pago_conductor');
    }
};
