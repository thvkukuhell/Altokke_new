<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->decimal('lat_actual', 10, 7)->nullable()->after('fecha_verificacion_dni');
            $table->decimal('lng_actual', 10, 7)->nullable()->after('lat_actual');
            $table->dateTime('ubicacion_actualizada_en')->nullable()->after('lng_actual');
        });
    }

    public function down(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->dropColumn(['lat_actual', 'lng_actual', 'ubicacion_actualizada_en']);
        });
    }
};
