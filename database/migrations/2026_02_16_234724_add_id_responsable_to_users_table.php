<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar columna después de rol_usuario
            $table->string('id_responsable', 20)->nullable()->after('rol_usuario');

            // Crear foreign key con la tabla 'responsable'
            $table->foreign('id_responsable')
                  ->references('dni_responsable')
                  ->on('responsable')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_responsable']);
            $table->dropColumn('id_responsable');
        });
    }
};
