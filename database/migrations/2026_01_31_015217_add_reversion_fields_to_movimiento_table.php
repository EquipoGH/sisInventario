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
        Schema::table('movimiento', function (Blueprint $table) {
            // ⭐⭐⭐ AGREGAR CAMPOS PARA REVERSIÓN DE BAJAS ⭐⭐⭐

            // Campo booleano para marcar si fue revertido
            $table->boolean('revertido')->default(false)->after('idusuario');

            // ID del usuario que revirtió (admin)
            $table->unsignedBigInteger('revertido_por')->nullable()->after('revertido');

            // Fecha en que se ejecutó la reversión
            $table->timestamp('fecha_reversion')->nullable()->after('revertido_por');

            // ID del movimiento de reversión creado
            $table->unsignedBigInteger('movimiento_reversion_id')->nullable()->after('fecha_reversion');

            // ⭐ FOREIGN KEYS PARA INTEGRIDAD REFERENCIAL
            $table->foreign('revertido_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->name('fk_movimiento_revertido_por');

            $table->foreign('movimiento_reversion_id')
                  ->references('id_movimiento')
                  ->on('movimiento')
                  ->onDelete('set null')
                  ->name('fk_movimiento_reversion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento', function (Blueprint $table) {
            // ⭐ ELIMINAR FOREIGN KEYS PRIMERO
            $table->dropForeign('fk_movimiento_revertido_por');
            $table->dropForeign('fk_movimiento_reversion_id');

            // ⭐ ELIMINAR COLUMNAS
            $table->dropColumn([
                'revertido',
                'revertido_por',
                'fecha_reversion',
                'movimiento_reversion_id'
            ]);
        });
    }
};
