<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->unsignedBigInteger('idbien');
            $table->unsignedBigInteger('tipo_mvto');
            $table->date('fecha_mvto');
            $table->string('detalle_tecnico', 200)->nullable();
            $table->unsignedBigInteger('documento_sustentatorio')->nullable();
            $table->unsignedBigInteger('idubicacion')->nullable();
            $table->unsignedBigInteger('id_estado_conservacion_bien')->nullable();
            $table->unsignedBigInteger('idusuario');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('idbien')
                  ->references('id_bien')
                  ->on('bien')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('tipo_mvto')
                  ->references('id_tipo_mvto')
                  ->on('tipo_mvto')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('documento_sustentatorio')
                  ->references('id_documento')
                  ->on('documento_sustento')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('idubicacion')
                  ->references('id_ubicacion')
                  ->on('ubicacion')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_conservacion_bien')
                  ->references('id_estado')
                  ->on('estado_bien')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('idusuario')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento');
    }
};
