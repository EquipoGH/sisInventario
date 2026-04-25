<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ===================================================================
        // FASE 4: Crear tabla baja
        // Relación con bien: 1 bien → 0..1 baja (UNIQUE en id_bien)
        // Solo puede existir UNA baja vigente por bien
        // ===================================================================
        Schema::create('baja', function (Blueprint $table) {
            $table->id('id_baja');

            // FK a bien — UNIQUE: un bien solo puede tener una baja registrada
            $table->unsignedInteger('id_bien')->unique();

            $table->date('fecha_baja');

            // Motivo formal de la baja (robo, obsolescencia, deterioro, etc.)
            $table->string('motivo_baja', 255);

            // Número de resolución o acto administrativo que autoriza la baja
            $table->string('resolucion', 100)->nullable();

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('id_bien')
                  ->references('id_bien')
                  ->on('bien')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });

        // ===================================================================
        // FASE 4B: Crear registros en tabla baja para bienes que ya
        // tienen activo = false (bajas lógicas previas)
        // ===================================================================
        $bienesDadosDeBaja = DB::table('bien')
            ->where('activo', false)
            ->whereNotNull('eliminado_en')
            ->get();

        foreach ($bienesDadosDeBaja as $bien) {
            // Intentar obtener el movimiento tipo BAJA relacionado
            $movBaja = DB::table('movimiento as m')
                ->join('tipo_mvto as tm', 'm.tipo_mvto', '=', 'tm.id_tipo_mvto')
                ->where('m.idbien', $bien->id_bien)
                ->whereRaw("UPPER(tm.tipo_mvto) LIKE '%BAJA%'")
                ->where('m.anulado', false)
                ->orderByDesc('m.fecha_mvto')
                ->select('m.fecha_mvto', 'm.detalle_tecnico')
                ->first();

            DB::table('baja')->insertOrIgnore([
                'id_bien'    => $bien->id_bien,
                'fecha_baja' => $movBaja ? $movBaja->fecha_mvto : ($bien->eliminado_en ?? now()),
                'motivo_baja' => $movBaja?->detalle_tecnico ?? 'Bien dado de baja previo al módulo formal',
                'resolucion' => null,
                'observacion' => 'Registro migrado automáticamente desde el campo activo=false',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('baja');
    }
};
