<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsable_area');
            $table->string('dni_responsable', 8);
            $table->unsignedBigInteger('idarea');
            $table->timestamp('fecha_asignacion')->useCurrent();

            // Foreign Keys
            $table->foreign('dni_responsable')
                  ->references('dni_responsable')
                  ->on('responsable')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('idarea')
                  ->references('id_area')
                  ->on('area')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Índice único para evitar duplicados (mismo responsable en misma área)
            $table->unique(['dni_responsable', 'idarea'], 'unique_responsable_area');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsable_area');
    }
};
