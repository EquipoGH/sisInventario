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
        // FASE 1: Crear tabla estado_conservacion
        // Esta tabla almacena la condición FÍSICA del bien:
        //   Bueno, Regular, Malo, Chatarra
        // Antes estos valores vivían en estado_bien (incorrectamente)
        // ===================================================================
        Schema::create('estado_conservacion', function (Blueprint $table) {
            $table->id('id_estado_conservacion');
            $table->string('nombre_conservacion', 50)->unique();
            $table->timestamps();
        });

        // ===================================================================
        // FASE 1B: Copiar los datos actuales de estado_bien
        // → hacia estado_conservacion (son estados físicos mal ubicados)
        // Se hace por nombre para no depender de IDs
        // ===================================================================
        $estadosActuales = DB::table('estado_bien')->get();

        foreach ($estadosActuales as $estado) {
            DB::table('estado_conservacion')->insertOrIgnore([
                'nombre_conservacion' => $estado->nombre_estado,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        // Asegurar que existan los valores canónicos del modelo propuesto
        $valoresCanonicos = ['Bueno', 'Regular', 'Malo', 'Chatarra'];
        foreach ($valoresCanonicos as $valor) {
            DB::table('estado_conservacion')->insertOrIgnore([
                'nombre_conservacion' => $valor,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_conservacion');
    }
};
