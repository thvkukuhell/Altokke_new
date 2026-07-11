<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->string('motivo_cancelacion')->nullable()->after('estado_viaje');
            $table->text('motivo_cancelacion_otro')->nullable()->after('motivo_cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->dropColumn(['motivo_cancelacion', 'motivo_cancelacion_otro']);
        });
    }
};
