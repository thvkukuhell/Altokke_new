<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_tarifas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_servicio', ['normal', 'express']);
            $table->decimal('tarifa_base', 8, 2)->default(3.00);
            $table->decimal('precio_por_km', 8, 2)->default(1.50);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_tarifas');
    }
};
