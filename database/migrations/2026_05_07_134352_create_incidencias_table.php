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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id('id_incidencia');
            
            // Relación con el proceso de inventario (Ancla principal)
            $table->unsignedBigInteger('id_inventario');
            
            // Relación opcional con el bien (si el bien existe en el sistema)
            // Para 'sin_codigo' este campo será NULL
            $table->unsignedBigInteger('id_bien')->nullable();
            
            // Tipo de incidencia: sobrante, faltante, sin_codigo, deteriorado
            $table->string('tipo_incidencia', 50); 
            
            // Datos de ubicación donde se reporta la incidencia
            $table->unsignedBigInteger('id_ubicacion')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
            
            $table->timestamp('fecha_registro')->useCurrent();
            $table->text('observacion')->nullable();
            $table->string('img_bien', 255)->nullable();
            
            // Estado de revisión de la incidencia
            $table->string('estado', 20)->default('no_revisado'); // 'revisado', 'no_revisado'

            $table->timestamps();

            // ==================== FOREIGN KEYS ====================

            $table->foreign('id_inventario')
                ->references('id_inventario')
                ->on('inventario')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('id_bien')
                ->references('id_bien')
                ->on('bien')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_ubicacion')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_area')
                ->references('id_area')
                ->on('area')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
