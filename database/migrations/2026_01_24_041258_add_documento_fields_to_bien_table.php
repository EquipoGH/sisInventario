<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bien', function (Blueprint $table) {
            // ⭐ AGREGAR NUEVOS CAMPOS
            $table->unsignedInteger('id_documento')->nullable()->after('fecha_registro');
            $table->string('NumDoc', 20)->nullable()->after('id_documento');

            // ⭐ AGREGAR FK
            $table->foreign('id_documento')
                  ->references('id_documento')
                  ->on('documento_sustento')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('bien', function (Blueprint $table) {
            // Eliminar FK primero
            $table->dropForeign(['id_documento']);

            // Eliminar columnas
            $table->dropColumn(['id_documento', 'NumDoc']);
        });
    }
};
