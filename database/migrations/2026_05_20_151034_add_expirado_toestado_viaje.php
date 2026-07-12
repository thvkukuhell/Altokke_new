<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE viajes MODIFY COLUMN estado_viaje
        ENUM('buscando','aceptado','recogiendo','en_curso','completado','cancelado','expirado')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
