<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasajeros', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pasajero')->primary();
            $table->enum('metodo_pago_preferido', ['efectivo','yape','plin'])
                  ->default('efectivo');
            $table->timestamps();
 
            $table->foreign('id_pasajero')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasajeros');
    }
};
