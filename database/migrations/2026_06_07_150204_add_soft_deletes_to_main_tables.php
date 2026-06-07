<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('conductores', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pasajeros', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('viajes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios',   function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('conductores',function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('pasajeros',  function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('viajes',     function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};
