<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bien', function (Blueprint $table) {
            $table->increments('id_bien');
            $table->string('codigo_patrimonial', 20)->unique();
            $table->string('denominacion_bien', 100);
            $table->unsignedInteger('id_tipobien');
            $table->string('modelo_bien', 20)->nullable();
            $table->string('marca_bien', 20)->nullable();
            $table->string('color_bien', 20)->nullable();
            $table->string('dimensiones_bien', 50)->nullable();
            $table->string('nserie_bien', 20)->nullable();
            $table->date('fecha_registro');
            $table->string('foto_bien', 255)->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('id_tipobien')
                  ->references('id_tipo_bien')
                  ->on('tipo_bien')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bien');
    }
};
