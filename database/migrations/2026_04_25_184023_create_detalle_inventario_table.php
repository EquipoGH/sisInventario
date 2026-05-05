<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_inventario', function (Blueprint $table) {
            $table->id('id_detalle_inv');
            $table->unsignedBigInteger('id_inventario');
            $table->unsignedBigInteger('id_movimiento');
            $table->unsignedBigInteger('estado_conservacion');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('id_inventario')
                ->references('id_inventario')
                ->on('inventario')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('id_movimiento')
                ->references('id_movimiento')
                ->on('movimiento')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('estado_conservacion')
                ->references('id_estado_conservacion')
                ->on('estado_conservacion')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_inventario');
    }
};