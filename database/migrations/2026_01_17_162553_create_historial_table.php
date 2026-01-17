<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial', function (Blueprint $table) {
            $table->id('id_historial');
            $table->dateTime('fecha_hora_cambio');
            $table->foreignId('id_usuario')->constrained('users', 'id')->onDelete('cascade');
            $table->string('entidad_afectada', 20);
            $table->integer('id_registro_afectado');
            $table->string('accion', 20);
            $table->string('campo_modificado', 20)->nullable();
            $table->string('valor_anterior', 20)->nullable();
            $table->string('valor_nuevo', 20)->nullable();
            $table->string('motivo_cambio', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial');
    }
};
