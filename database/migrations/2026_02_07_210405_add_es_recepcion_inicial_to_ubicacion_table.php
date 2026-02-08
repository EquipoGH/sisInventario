<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ubicacion', function (Blueprint $table) {
            // ⭐ Agregar columna booleana
            $table->boolean('es_recepcion_inicial')
                ->default(false)
                ->after('piso_ubicacion')
                ->comment('Indica si es la ubicación de recepción inicial para nuevos bienes');

            // ⭐ Índice para optimizar búsquedas
            $table->index('es_recepcion_inicial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ubicacion', function (Blueprint $table) {
            $table->dropIndex(['es_recepcion_inicial']);
            $table->dropColumn('es_recepcion_inicial');
        });
    }
};
