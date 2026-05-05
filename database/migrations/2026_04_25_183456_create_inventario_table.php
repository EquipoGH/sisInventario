<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('responsable', 8);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('responsable')
                ->references('dni_responsable')
                ->on('responsable')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};