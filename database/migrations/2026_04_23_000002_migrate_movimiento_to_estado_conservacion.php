<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ===================================================================
        // FASE 2: Migrar movimiento.id_estado_conservacion_bien
        // Antes apuntaba a: estado_bien.id_estado
        // Ahora debe apuntar a: estado_conservacion.id_estado_conservacion
        //
        // Estrategia:
        // 1. Remap los IDs existentes via JOIN por nombre
        // 2. Drop FK antigua
        // 3. Crear FK nueva
        // ===================================================================

        // PASO 1: Remap de IDs en movimiento usando un JOIN por nombre
        // (funciona independientemente de si los IDs son iguales o no)
        DB::statement("
            UPDATE movimiento AS m
            SET id_estado_conservacion_bien = ec.id_estado_conservacion
            FROM estado_conservacion AS ec
            INNER JOIN estado_bien AS eb
                ON UPPER(TRIM(ec.nombre_conservacion)) = UPPER(TRIM(eb.nombre_estado))
            WHERE m.id_estado_conservacion_bien = eb.id_estado
        ");

        // PASO 2: Drop FK antigua (movimiento → estado_bien)
        Schema::table('movimiento', function (Blueprint $table) {
            // La FK se nombra automáticamente por Laravel como:
            // movimiento_id_estado_conservacion_bien_foreign
            $table->dropForeign(['id_estado_conservacion_bien']);
        });

        // PASO 3: Agregar nueva FK (movimiento → estado_conservacion)
        Schema::table('movimiento', function (Blueprint $table) {
            $table->foreign('id_estado_conservacion_bien')
                  ->references('id_estado_conservacion')
                  ->on('estado_conservacion')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        // Revertir: volver a apuntar a estado_bien
        Schema::table('movimiento', function (Blueprint $table) {
            $table->dropForeign(['id_estado_conservacion_bien']);
        });

        Schema::table('movimiento', function (Blueprint $table) {
            $table->foreign('id_estado_conservacion_bien')
                  ->references('id_estado')
                  ->on('estado_bien')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }
};
