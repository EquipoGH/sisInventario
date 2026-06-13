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
        Schema::table('incidencias', function (Blueprint $table) {
            $table->text('resolucion')->nullable()->after('observacion');
            $table->unsignedBigInteger('id_usuario_revision')->nullable()->after('resolucion');
            $table->timestamp('fecha_revision')->nullable()->after('id_usuario_revision');

            $table->foreign('id_usuario_revision')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign(['id_usuario_revision']);
            $table->dropColumn(['resolucion', 'id_usuario_revision', 'fecha_revision']);
        });
    }
};
