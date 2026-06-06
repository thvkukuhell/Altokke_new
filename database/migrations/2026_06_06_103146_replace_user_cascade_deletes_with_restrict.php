<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasajeros', function(Blueprint $table) {
            $table->dropForeign(['id_pasajero']);
            $table->foreign('id_pasajero')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });

        Schema::table('conductores', function(Blueprint $table) {
            $table->dropForeign(['id_conductor']);
            $table->foreign('id_conductor')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });

        Schema::table('viajes', function (Blueprint $table) {
            $table->dropForeign(['id_pasajero']);
            $table->foreign('id_pasajero')
                ->references('id_pasajero')
                ->on('pasajeros')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->dropForeign(['id_pasajero']);
            $table->foreign('id_pasajero')
                ->references('id_pasajero')
                ->on('pasajeros')
                ->cascadeOnDelete();
        });

        Schema::table('conductores', function (Blueprint $table) {
            $table->dropForeign(['id_conductor']);
            $table->foreign('id_conductor')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();
        });

        Schema::table('pasajeros', function (Blueprint $table) {
            $table->dropForeign(['id_pasajero']);
            $table->foreign('id_pasajero')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();
        });
    }
};
