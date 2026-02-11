<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            // ⭐ SOFT DELETE - No eliminar, solo marcar como anulado
            $table->boolean('anulado')->default(false)->after('idusuario');
            $table->integer('anulado_por')->nullable()->after('anulado');
            $table->timestamp('fecha_anulacion')->nullable()->after('anulado_por');
            $table->string('motivo_anulacion', 200)->nullable()->after('fecha_anulacion');

            // ⭐ ÍNDICES para optimizar consultas
            $table->index('anulado');
            $table->index('anulado_por');

            // ⭐ FOREIGN KEY (si tienes FK activadas)
            // $table->foreign('anulado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->dropColumn(['anulado', 'anulado_por', 'fecha_anulacion', 'motivo_anulacion']);
        });
    }
};
