<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_inventario', function (Blueprint $table) {
            $table->string('estadoverificacion', 20)
                ->nullable()
                ->after('estado_conservacion');

            $table->unsignedBigInteger('ubicaciondetectada')
                ->nullable()
                ->after('estadoverificacion');

            $table->unsignedBigInteger('usuarioverificador')
                ->nullable()
                ->after('ubicaciondetectada');

            $table->timestamp('fechaverificacion')
                ->nullable()
                ->after('usuarioverificador');

            $table->boolean('requiereregularizacion')
                ->default(false)
                ->after('fechaverificacion');

            $table->string('evidencia', 255)
                ->nullable()
                ->after('requiereregularizacion');

            $table->foreign('ubicaciondetectada')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('usuarioverificador')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_inventario', function (Blueprint $table) {
            $table->dropForeign(['ubicaciondetectada']);
            $table->dropForeign(['usuarioverificador']);

            $table->dropColumn([
                'estadoverificacion',
                'ubicaciondetectada',
                'usuarioverificador',
                'fechaverificacion',
                'requiereregularizacion',
                'evidencia',
            ]);
        });
    }
};