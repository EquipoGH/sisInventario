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
        // FASE 3: Refactorizar estado_bien
        // Antes tenía valores físicos (Bueno, Regular, Malo...)
        // Ahora tendrá valores ADMINISTRATIVOS:
        //   Activo, Baja, Prestado, Mantenimiento
        //
        // Ya migramos sus datos a estado_conservacion (migración anterior)
        // Y ya eliminamos la FK de movimiento → estado_bien
        // Ahora podemos limpiarla con seguridad
        // ===================================================================

        // PASO 1: Limpiar la tabla estado_bien y poblar con valores administrativos
        // (Solo si no tiene FKs activas — ya las eliminamos en la migración anterior)
        DB::table('estado_bien')->truncate();

        $estadosAdmin = [
            ['nombre_estado' => 'Activo',       'created_at' => now(), 'updated_at' => now()],
            ['nombre_estado' => 'Baja',          'created_at' => now(), 'updated_at' => now()],
            ['nombre_estado' => 'Prestado',      'created_at' => now(), 'updated_at' => now()],
            ['nombre_estado' => 'Mantenimiento', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('estado_bien')->insert($estadosAdmin);

        // PASO 2: Agregar columnas a la tabla bien
        Schema::table('bien', function (Blueprint $table) {
            // FK al estado administrativo del bien (Activo, Baja, Prestado, Mantenimiento)
            $table->unsignedBigInteger('id_estado_bien')
                  ->nullable()
                  ->after('id_tipobien');

            // FK al estado de conservación física actual del bien (Bueno, Regular, Malo, Chatarra)
            $table->unsignedBigInteger('id_estado_conservacion')
                  ->nullable()
                  ->after('id_estado_bien');

            $table->foreign('id_estado_bien')
                  ->references('id_estado')
                  ->on('estado_bien')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_conservacion')
                  ->references('id_estado_conservacion')
                  ->on('estado_conservacion')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });

        // PASO 3: Poblar bien.id_estado_bien basado en el campo activo existente
        // activo = true  → estado "Activo"
        // activo = false → estado "Baja"
        $idActivo = DB::table('estado_bien')
            ->where('nombre_estado', 'Activo')
            ->value('id_estado');

        $idBaja = DB::table('estado_bien')
            ->where('nombre_estado', 'Baja')
            ->value('id_estado');

        if ($idActivo) {
            DB::table('bien')
                ->where('activo', true)
                ->update(['id_estado_bien' => $idActivo]);
        }

        if ($idBaja) {
            DB::table('bien')
                ->where('activo', false)
                ->update(['id_estado_bien' => $idBaja]);
        }
    }

    public function down(): void
    {
        Schema::table('bien', function (Blueprint $table) {
            $table->dropForeign(['id_estado_bien']);
            $table->dropForeign(['id_estado_conservacion']);
            $table->dropColumn(['id_estado_bien', 'id_estado_conservacion']);
        });

        // Restaurar estado_bien a valores físicos (aproximado)
        DB::table('estado_bien')->truncate();
        DB::table('estado_bien')->insert([
            ['nombre_estado' => 'Bueno',    'created_at' => now(), 'updated_at' => now()],
            ['nombre_estado' => 'Regular',  'created_at' => now(), 'updated_at' => now()],
            ['nombre_estado' => 'Malo',     'created_at' => now(), 'updated_at' => now()],
            ['nombre_estado' => 'Chatarra', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
